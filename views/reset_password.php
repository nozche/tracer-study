<?php include __DIR__ . '/partials/header.php'; ?>
<h2>Reset Password</h2>
<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p class="success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
<form method="POST" action="/reset-password">
    <label>Password Saat Ini</label>
    <input type="password" name="current_password" required>

    <label>Password Baru</label>
    <input type="password" name="new_password" required>

    <button type="submit">Ubah Password</button>
</form>
<?php include __DIR__ . '/partials/footer.php'; ?>
