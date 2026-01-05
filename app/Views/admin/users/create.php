<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h5 mb-3">Tambah Pengguna</h1>
            <form method="post" action="<?= base_url('admin/users'); ?>">
                <?= csrf_field(); ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= old('name'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= old('email'); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="active" <?= old('status') === 'inactive' ? '' : 'selected'; ?>>Aktif</option>
                                <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Peran</label>
                            <select name="role_id" id="role_id" class="form-select" required>
                                <option value="">-- Pilih Peran --</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id']; ?>" <?= old('role_id') == $role['id'] ? 'selected' : ''; ?>>
                                        <?= esc($role['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="fakultas_id" class="form-label">Fakultas</label>
                            <select name="fakultas_id" id="fakultas_id" class="form-select">
                                <option value="">-- Pilih Fakultas --</option>
                                <?php foreach ($fakultas as $row): ?>
                                    <option value="<?= $row['id']; ?>" <?= old('fakultas_id') == $row['id'] ? 'selected' : ''; ?>>
                                        <?= esc($row['nama']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="program_studi_id" class="form-label">Program Studi</label>
                            <select name="program_studi_id" id="program_studi_id" class="form-select">
                                <option value="">-- Pilih Program Studi --</option>
                                <?php foreach ($programStudi as $prodi): ?>
                                    <option value="<?= $prodi['id']; ?>" <?= old('program_studi_id') == $prodi['id'] ? 'selected' : ''; ?>>
                                        <?= esc($prodi['nama']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Telepon</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?= old('phone'); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?= old('address'); ?>">
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('admin/users'); ?>" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
<?= $this->endSection(); ?>
