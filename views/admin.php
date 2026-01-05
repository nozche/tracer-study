<?php include __DIR__ . '/partials/header.php'; ?>
<h2>Admin Area</h2>
<p>Halaman ini membutuhkan role <strong>admin</strong>.</p>
<h3>Audit Log Terbaru</h3>
<table>
    <thead>
    <tr>
        <th>Waktu</th>
        <th>Aksi</th>
        <th>Pengguna</th>
        <th>Detail</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($log['username'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
            <td><pre><?= htmlspecialchars($log['metadata'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></pre></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<a href="/dashboard">Kembali</a>
<?php include __DIR__ . '/partials/footer.php'; ?>
