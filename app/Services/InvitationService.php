<?php

require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/NotificationSender.php';

class InvitationService
{
    private PDO $db;
    private NotificationSender $sender;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->sender = new NotificationSender();
    }

    public function createInvitation(array $alumni, string $channel = 'email'): array
    {
        $token = bin2hex(random_bytes(16));
        $tracerUrl = $this->buildTracerLink($token);

        $this->db->beginTransaction();
        try {
            $alumniId = $this->storeAlumni($alumni, $tracerUrl);
            $invitationId = $this->storeInvitation($alumniId, $token, $tracerUrl, $channel);
            $this->queueDelivery($invitationId, $channel, $alumni, $tracerUrl, $token);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return [
            'token' => $token,
            'tracer_url' => $tracerUrl,
            'channel' => $channel,
            'alumni_id' => $alumniId,
            'invitation_id' => $invitationId,
        ];
    }

    private function buildTracerLink(string $token): string
    {
        $base = getenv('TRACER_BASE_URL') ?: 'https://tracer.example.com/form';
        return $base . '?token=' . $token;
    }

    private function storeAlumni(array $alumni, string $tracerUrl): int
    {
        $stmt = $this->db->prepare('INSERT INTO alumni (name, email, whatsapp_number, tracer_link) VALUES (:name, :email, :wa, :link)');
        $stmt->execute([
            ':name' => $alumni['name'],
            ':email' => $alumni['email'] ?? null,
            ':wa' => $alumni['whatsapp_number'] ?? null,
            ':link' => $tracerUrl,
        ]);

        return (int) $this->db->lastInsertId();
    }

    private function storeInvitation(int $alumniId, string $token, string $tracerUrl, string $channel): int
    {
        $stmt = $this->db->prepare('INSERT INTO invitations (alumni_id, token, tracer_url, delivery_channel) VALUES (:alumni_id, :token, :url, :channel)');
        $stmt->execute([
            ':alumni_id' => $alumniId,
            ':token' => $token,
            ':url' => $tracerUrl,
            ':channel' => $channel,
        ]);

        return (int) $this->db->lastInsertId();
    }

    private function queueDelivery(int $invitationId, string $channel, array $alumni, string $tracerUrl, string $token): void
    {
        $payload = [
            'alumni' => $alumni,
            'tracer_url' => $tracerUrl,
            'token' => $token,
        ];

        $stmt = $this->db->prepare('INSERT INTO queue_jobs (invitation_id, channel, payload) VALUES (:invitation_id, :channel, :payload)');
        $stmt->execute([
            ':invitation_id' => $invitationId,
            ':channel' => $channel,
            ':payload' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
    }
}
