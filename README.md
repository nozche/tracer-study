# tracer-study

Aplikasi PHP sederhana untuk autentikasi dengan pengecekan session dan role, audit log, hashing password bcrypt, serta rate limiting login. Menggunakan SQLite untuk penyimpanan data sehingga mudah dijalankan tanpa dependensi eksternal.

## Fitur
- **Auth Controller**: login, logout, dan reset password dengan hashing `password_hash` (bcrypt).
- **Middleware**: cek session dan role; redirect ke halaman login/unauthorized bila tidak memenuhi syarat.
- **Audit Log**: tabel `audit_logs` mencatat login, logout, reset password, dan percobaan akses tidak sah.
- **Rate Limiting**: batasi percobaan login per IP; kunci sementara setelah batas tercapai.
- **Demo UI**: halaman dasar (login, dashboard, admin, reset password) untuk menguji alur.

## Persiapan
Pastikan PHP 8.1+ tersedia (lingkungan ini menggunakan PHP 8.4) dan SQLite diaktifkan.

## Menjalankan
1. Jalankan server pengembangan PHP:
   ```bash
   php -S localhost:8000 -t public
   ```
2. Buka `http://localhost:8000` di browser.

Database SQLite (`data/database.sqlite`) akan dibuat otomatis saat pertama dijalankan, lengkap dengan akun default:

- **Username**: `admin`
- **Password**: `Password123!`

## Arsitektur Singkat
- `public/index.php` — front controller dan routing sederhana.
- `src/Database.php` — koneksi/ migrasi SQLite + seeding admin.
- `src/AuthController.php` — aksi login, logout, reset password.
- `src/Middleware.php` — pengecekan session dan role dengan redirect.
- `src/RateLimiter.php` — pembatasan percobaan login.
- `src/AuditLogger.php` — utilitas pencatatan ke `audit_logs`.
- `views/` — tampilan HTML sederhana.

## Audit Log
Catatan audit tersimpan di tabel `audit_logs` dengan kolom `user_id`, `action`, `metadata`, dan `created_at`. Halaman admin (`/admin`) menampilkan 20 log terbaru.

## Rate Limiting
Bawaan: maksimal 5 percobaan dalam 15 menit per IP. Setelah melewati batas, login dikunci 5 menit. Pengaturan dapat diubah di konstruktor `RateLimiter`.

## Reset Password
Reset password hanya untuk pengguna yang sudah login dan memerlukan verifikasi password saat ini. Panjang minimal password baru: 8 karakter.

## Catatan Keamanan
- Pastikan menjalankan di belakang HTTPS untuk produksi.
- Ganti kredensial default segera setelah instalasi.
- Atur konfigurasi session/cookie sesuai kebutuhan produksi.
