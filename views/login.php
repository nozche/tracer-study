<?php include __DIR__ . '/partials/header.php'; ?>
<h2>Login</h2>
<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
<?php if (!empty($remaining)): ?>
    <p class="info">Percobaan tersisa: <?= (int)$remaining ?></p>
<?php endif; ?>
<form method="POST" action="/login">
    <label>Username</label>
    <input type="text" name="username" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Masuk</button>
</form>
<p>Default admin: <code>admin / Password123!</code></p>
<?php include __DIR__ . '/partials/footer.php'; ?>
