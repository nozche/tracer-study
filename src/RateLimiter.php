<?php

class RateLimiter
{
    private int $maxAttempts;
    private int $decaySeconds;
    private int $lockSeconds;

    public function __construct(int $maxAttempts = 5, int $decaySeconds = 900, int $lockSeconds = 300)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
        $this->lockSeconds = $lockSeconds;
    }

    public function canAttempt(string $ip): array
    {
        $db = Database::connection();

        $stmt = $db->prepare('SELECT ip, attempts, last_attempt, locked_until FROM login_attempts WHERE ip = :ip LIMIT 1');
        $stmt->execute([':ip' => $ip]);
        $record = $stmt->fetch();

        if (!$record) {
            return ['allowed' => true, 'remaining' => $this->maxAttempts, 'retry_after' => 0];
        }

        $now = time();

        if (!empty($record['locked_until']) && (int)$record['locked_until'] > $now) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'retry_after' => (int)$record['locked_until'] - $now,
            ];
        }

        if (!empty($record['last_attempt']) && ($now - (int)$record['last_attempt']) > $this->decaySeconds) {
            $this->reset($ip);
            return ['allowed' => true, 'remaining' => $this->maxAttempts, 'retry_after' => 0];
        }

        $remaining = max(0, $this->maxAttempts - (int)$record['attempts']);

        return ['allowed' => $remaining > 0, 'remaining' => $remaining, 'retry_after' => 0];
    }

    public function hit(string $ip, bool $success): void
    {
        $db = Database::connection();
        $now = time();

        $stmt = $db->prepare('SELECT attempts FROM login_attempts WHERE ip = :ip LIMIT 1');
        $stmt->execute([':ip' => $ip]);
        $record = $stmt->fetch();

        if ($success) {
            $db->prepare('DELETE FROM login_attempts WHERE ip = :ip')->execute([':ip' => $ip]);
            return;
        }

        if ($record) {
            $attempts = (int)$record['attempts'] + 1;
            $lockedUntil = $attempts >= $this->maxAttempts ? $now + $this->lockSeconds : null;

            $db->prepare('UPDATE login_attempts SET attempts = :attempts, last_attempt = :last_attempt, locked_until = :locked_until WHERE ip = :ip')
                ->execute([
                    ':attempts' => $attempts,
                    ':last_attempt' => $now,
                    ':locked_until' => $lockedUntil,
                    ':ip' => $ip,
                ]);
        } else {
            $lockedUntil = 1 >= $this->maxAttempts ? $now + $this->lockSeconds : null;
            $db->prepare('INSERT INTO login_attempts (ip, attempts, last_attempt, locked_until) VALUES (:ip, :attempts, :last_attempt, :locked_until)')
                ->execute([
                    ':ip' => $ip,
                    ':attempts' => 1,
                    ':last_attempt' => $now,
                    ':locked_until' => $lockedUntil,
                ]);
        }
    }

    public function reset(string $ip): void
    {
        $db = Database::connection();
        $db->prepare('DELETE FROM login_attempts WHERE ip = :ip')->execute([':ip' => $ip]);
    }
}
