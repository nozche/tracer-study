<?php

class Middleware
{
    public static function requireSession(): void
    {
        if (!isset($_SESSION['user'])) {
            AuditLogger::log(null, 'unauthenticated-access', [
                'path' => $_SERVER['REQUEST_URI'] ?? '',
            ]);
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireSession();

        if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== $role) {
            AuditLogger::log(self::userId(), 'unauthorized-access', [
                'role' => $_SESSION['user']['role'] ?? null,
                'required_role' => $role,
                'path' => $_SERVER['REQUEST_URI'] ?? '',
            ]);

            header('Location: /unauthorized');
            exit;
        }
    }

    private static function userId(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }
}
