<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-3">Welcome to Tracer Study</h1>
            <p class="mb-2">CodeIgniter 4 starter application.</p>
            <p class="mb-0">
                Gunakan akun <strong>superadmin@example.com</strong> dengan kata sandi
                <strong>SuperAdmin@123</strong> untuk mengakses area administrasi pengguna.
            </p>
        </div>
    </div>
<?= $this->endSection(); ?>
