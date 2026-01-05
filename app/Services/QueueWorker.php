<?php

require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/NotificationSender.php';

class QueueWorker
{
    private PDO $db;
    private NotificationSender $sender;
    private int $maxAttempts;

    public function __construct(int $maxAttempts = 3)
    {
        $this->db = Database::connection();
        $this->sender = new NotificationSender();
        $this->maxAttempts = $maxAttempts;
    }

    public function processNext(): ?array
    {
        $job = $this->reserveJob();
        if (!$job) {
            return null;
        }

        $payload = json_decode($job['payload'], true) ?: [];
        $result = $this->send($job['channel'], $payload);

        $this->db->beginTransaction();
        try {
            $this->logDelivery($job['invitation_id'], $job['channel'], $result);
            $this->markInvitationStatus($job['invitation_id'], $result['success']);
            $this->finalizeJob($job, $result);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $result;
    }

    private function reserveJob(): ?array
    {
        $this->db->beginTransaction();
        $stmt = $this->db->prepare('SELECT * FROM queue_jobs WHERE reserved_at IS NULL AND completed_at IS NULL AND failed_at IS NULL ORDER BY available_at LIMIT 1');
        $stmt->execute();
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($job) {
            $update = $this->db->prepare('UPDATE queue_jobs SET reserved_at = CURRENT_TIMESTAMP WHERE id = :id');
            $update->execute([':id' => $job['id']]);
            $this->db->commit();
            return $job;
        }

        $this->db->commit();
        return null;
    }

    private function send(string $channel, array $payload): array
    {
        $alumni = $payload['alumni'] ?? [];
        $tracerUrl = $payload['tracer_url'] ?? '';
        $token = $payload['token'] ?? '';

        $message = "Halo {$alumni['name']}, mohon lengkapi tracer study: {$tracerUrl}";

        return match ($channel) {
            'email' => $this->sender->sendEmail($alumni['email'] ?? '', 'Undangan Tracer Study', $message),
            'whatsapp' => $this->sender->sendWhatsApp($alumni['whatsapp_number'] ?? '', $message),
            default => ['success' => false, 'metadata' => ['error' => 'Unsupported channel', 'channel' => $channel]],
        } + ['token' => $token];
    }

    private function logDelivery(int $invitationId, string $channel, array $result): void
    {
        $stmt = $this->db->prepare('INSERT INTO delivery_logs (invitation_id, channel, status, metadata) VALUES (:invitation_id, :channel, :status, :metadata)');
        $stmt->execute([
            ':invitation_id' => $invitationId,
            ':channel' => $channel,
            ':status' => $result['success'] ? 'sent' : 'failed',
            ':metadata' => json_encode($result['metadata'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    private function finalizeJob(array $job, array $result): void
    {
        $attempts = $job['attempts'] + 1;

        if ($result['success']) {
            $stmt = $this->db->prepare('UPDATE queue_jobs SET attempts = :attempts, completed_at = CURRENT_TIMESTAMP WHERE id = :id');
            $stmt->execute([
                ':attempts' => $attempts,
                ':id' => $job['id'],
            ]);
        } else {
            $fields = [
                ':attempts' => $attempts,
                ':error' => $result['metadata']['error'] ?? 'unknown',
                ':id' => $job['id'],
            ];

            if ($attempts >= $this->maxAttempts) {
                $stmt = $this->db->prepare('UPDATE queue_jobs SET attempts = :attempts, failed_at = CURRENT_TIMESTAMP, last_error = :error WHERE id = :id');
            } else {
                $stmt = $this->db->prepare('UPDATE queue_jobs SET attempts = :attempts, reserved_at = NULL, available_at = datetime(CURRENT_TIMESTAMP, "+5 minutes"), last_error = :error WHERE id = :id');
            }

            $stmt->execute($fields);
        }
    }

    private function markInvitationStatus(int $invitationId, bool $success): void
    {
        $stmt = $this->db->prepare('UPDATE invitations SET status = :status, sent_at = CASE WHEN :success = 1 THEN CURRENT_TIMESTAMP ELSE sent_at END WHERE id = :id');
        $stmt->execute([
            ':status' => $success ? 'sent' : 'pending_retry',
            ':success' => $success ? 1 : 0,
            ':id' => $invitationId,
        ]);
    }
}
