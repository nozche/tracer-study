# Tracer Study Dashboard

Layanan sederhana untuk menampilkan dashboard staf tracer study dan mengekspor respons undangan.

## Menjalankan secara lokal

1. Buat virtualenv dan pasang dependensi:

   ```bash
   python -m venv .venv
   source .venv/bin/activate
   pip install -r requirements.txt
   ```

2. Jalankan server:

   ```bash
   uvicorn app.main:app --reload
   ```

3. Akses dokumentasi interaktif di `http://127.0.0.1:8000/docs`.

## Fitur utama

- **Dashboard staf**: total undangan, respons masuk, ringkasan status (belum buka, draft, submitted), serta progres per fakultas dan angkatan.
- **Filter & pencarian**: parameter query `faculty`, `cohort_year`, `status`, dan `search` (nama/email/fakultas) untuk daftar respons.
- **Ekspor**: endpoint `/responses/export` mendukung format CSV atau Excel (`format=csv|xlsx`) dengan hasil query join/view `responses_view`.

Basis data SQLite otomatis dibuat beserta seed contoh saat aplikasi dijalankan pertama kali.
