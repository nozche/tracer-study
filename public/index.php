<?php
session_start();

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/AuditLogger.php';
require_once __DIR__ . '/../src/RateLimiter.php';
require_once __DIR__ . '/../src/Middleware.php';
require_once __DIR__ . '/../src/AuthController.php';

date_default_timezone_set('Asia/Jakarta');

$db = Database::connection();
$controller = new AuthController();

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($uri) {
    case '/':
        include __DIR__ . '/../views/home.php';
        break;
    case '/login':
        if ($method === 'POST') {
            $controller->login();
        }
        $controller->showLoginForm();
        break;
    case '/logout':
        Middleware::requireSession();
        if ($method === 'POST') {
            $controller->logout();
            break;
        }
        header('Location: /dashboard');
        break;
    case '/dashboard':
        Middleware::requireSession();
        include __DIR__ . '/../views/dashboard.php';
        break;
    case '/admin':
        Middleware::requireRole('admin');
        $stmt = $db->query('SELECT audit_logs.*, users.username FROM audit_logs LEFT JOIN users ON users.id = audit_logs.user_id ORDER BY audit_logs.created_at DESC LIMIT 20');
        $logs = $stmt->fetchAll();
        include __DIR__ . '/../views/admin.php';
        break;
    case '/reset-password':
        Middleware::requireSession();
        if ($method === 'POST') {
            $controller->resetPassword();
            break;
        }
        $controller->showResetForm();
        break;
    case '/unauthorized':
        include __DIR__ . '/../views/unauthorized.php';
        break;
    default:
        http_response_code(404);
        echo 'Halaman tidak ditemukan';
        break;
}
