<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Kelola Pengguna</h1>
        <a href="<?= base_url('admin/users/create'); ?>" class="btn btn-primary btn-sm">Tambah Pengguna</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th>Fakultas</th>
                        <th>Program Studi</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">Belum ada data pengguna.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($user['name']); ?></strong><br>
                                    <small class="text-muted"><?= esc($user['phone'] ?? '-'); ?></small>
                                </td>
                                <td><?= esc($user['email']); ?></td>
                                <td><?= esc($user['role_name'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?= esc(ucfirst($user['status'])); ?>
                                    </span>
                                </td>
                                <td><?= esc($user['fakultas_nama'] ?? '-'); ?></td>
                                <td><?= esc($user['program_studi_nama'] ?? '-'); ?></td>
                                <td class="d-flex gap-1">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= base_url('admin/users/' . $user['id'] . '/edit'); ?>">Edit</a>
                                    <form method="post" action="<?= base_url('admin/users/' . $user['id'] . '/delete'); ?>" onsubmit="return confirm('Hapus pengguna ini?');">
                                        <?= csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?= $this->endSection(); ?>
