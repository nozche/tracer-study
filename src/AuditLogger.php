<?php

class AuditLogger
{
    public static function log(?int $userId, string $action, array $metadata = []): void
    {
        $db = Database::connection();

        $stmt = $db->prepare('INSERT INTO audit_logs (user_id, action, metadata) VALUES (:user_id, :action, :metadata)');
        $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':metadata' => empty($metadata) ? null : json_encode($metadata, JSON_THROW_ON_ERROR),
        ]);
    }
}
