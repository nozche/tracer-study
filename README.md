# tracer-study

Layanan kecil untuk mengelola periode tracer study, versi kuisioner per periode, serta builder pertanyaan berbasis JSON.

## Menjalankan aplikasi

1. Pasang dependensi (disarankan dalam virtualenv):
   ```bash
   pip install -r requirements.txt
   ```
2. Jalankan server:
   ```bash
   uvicorn app.main:app --reload
   ```

Aplikasi akan membuat file SQLite `tracer.db` otomatis. Buka dokumentasi API di `/docs` atau UI builder statis di `/static/index.html`.

### Fitur utama
- CRUD periode dengan status `draft/active/closed` dan hanya satu periode aktif sekaligus.
- Versi kuisioner per periode; membuat versi baru selalu meningkatkan nomor versi dan tidak mengubah versi lama.
- Endpoint khusus untuk menduplikasi versi terbaru sebagai titik awal edit.
- Pertanyaan mendukung tipe `single`, `multiple`, `text`, `scale`, `matrix` dengan metadata tersimpan sebagai JSON (opsi, skala, dll.).
- Builder UI sederhana untuk menyusun pertanyaan (opsi, urutan, required) dan menyimpan sebagai versi baru.
