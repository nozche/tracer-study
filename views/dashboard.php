<?php include __DIR__ . '/partials/header.php'; ?>
<h2>Dashboard</h2>
<p>Halo, <?= htmlspecialchars($_SESSION['user']['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>!</p>
<p>Role: <strong><?= htmlspecialchars($_SESSION['user']['role'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong></p>
<nav>
    <a href="/">Beranda</a>
    <a href="/reset-password">Reset Password</a>
    <a href="/admin">Halaman Admin</a>
</nav>
<form method="POST" action="/logout" style="margin-top:1rem;">
    <button type="submit">Logout</button>
</form>
<?php include __DIR__ . '/partials/footer.php'; ?>
