<?php

class AuthController
{
    private RateLimiter $rateLimiter;

    public function __construct()
    {
        $this->rateLimiter = new RateLimiter();
    }

    public function showLoginForm(): void
    {
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);
        $remaining = $_SESSION['rate_remaining'] ?? null;
        unset($_SESSION['rate_remaining']);

        include __DIR__ . '/../views/login.php';
    }

    public function login(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $check = $this->rateLimiter->canAttempt($ip);

        if (!$check['allowed']) {
            $_SESSION['flash_error'] = 'Terlalu banyak percobaan. Coba lagi dalam ' . $check['retry_after'] . ' detik.';
            header('Location: /login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $db = Database::connection();
        $stmt = $db->prepare('SELECT id, username, password_hash, role FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->rateLimiter->hit($ip, false);
            $_SESSION['flash_error'] = 'Username atau password salah.';
            $_SESSION['rate_remaining'] = $check['remaining'] - 1;

            AuditLogger::log(null, 'login-failed', [
                'username' => $username,
                'ip' => $ip,
            ]);

            header('Location: /login');
            exit;
        }

        $this->rateLimiter->hit($ip, true);

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];

        AuditLogger::log((int)$user['id'], 'login', ['ip' => $ip]);

        header('Location: /dashboard');
        exit;
    }

    public function logout(): void
    {
        $userId = $_SESSION['user']['id'] ?? null;
        $username = $_SESSION['user']['username'] ?? null;
        AuditLogger::log($userId, 'logout', ['username' => $username]);

        session_destroy();
        header('Location: /login');
        exit;
    }

    public function showResetForm(): void
    {
        $error = $_SESSION['flash_error'] ?? null;
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        include __DIR__ . '/../views/reset_password.php';
    }

    public function resetPassword(): void
    {
        Middleware::requireSession();

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        if (strlen($newPassword) < 8) {
            $_SESSION['flash_error'] = 'Password baru minimal 8 karakter.';
            header('Location: /reset-password');
            exit;
        }

        $db = Database::connection();
        $stmt = $db->prepare('SELECT id, password_hash FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $_SESSION['user']['id']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Password saat ini salah.';
            header('Location: /reset-password');
            exit;
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $update = $db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $update->execute([
            ':hash' => $newHash,
            ':id' => $user['id'],
        ]);

        AuditLogger::log((int)$user['id'], 'password-reset', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        $_SESSION['flash_success'] = 'Password berhasil diubah.';
        header('Location: /reset-password');
        exit;
    }
}
