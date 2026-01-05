<?php include __DIR__ . '/partials/header.php'; ?>
<h2>Tracer Study Demo</h2>
<p>Aplikasi sederhana dengan autentikasi, pengecekan role, dan audit log.</p>
<?php if (empty($_SESSION['user'])): ?>
    <a href="/login">Login</a>
<?php else: ?>
    <a href="/dashboard">Buka Dashboard</a>
<?php endif; ?>
<?php include __DIR__ . '/partials/footer.php'; ?>
