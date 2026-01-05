# Tracer Study API

API sederhana untuk mengelola tracer study dengan kebutuhan:

- CRUD user pada area **Super Admin** dengan dukungan multi-role.
- Profil alumni (`alumni_profiles`) terhubung ke user dengan informasi angkatan, program, pekerjaan, lokasi, dan range gaji.
- Profil mahasiswa/dosen opsional untuk membantu validasi tracer.

## Menjalankan aplikasi

1. Install dependency:

```bash
pip install -r requirements.txt
```

2. Jalankan server pengembangan:

```bash
uvicorn app.main:app --reload
```

Server akan otomatis membuat database SQLite (`app.db`) ketika startup.

## Pengujian

```bash
pytest
```

## Endpoint utama

- `POST /super-admin/users` – Membuat user baru dan menetapkan beberapa role sekaligus.
- `GET /super-admin/users` – Daftar user beserta role.
- `PATCH /super-admin/users/{user_id}` – Perbarui data user termasuk role.
- `DELETE /super-admin/users/{user_id}` – Hapus user.
- `PUT /alumni/{user_id}/profile` – Buat atau perbarui profil alumni.
- `PUT /students/{user_id}/profile` – Buat atau perbarui profil mahasiswa (opsional).
- `PUT /lecturers/{user_id}/profile` – Buat atau perbarui profil dosen (opsional).
