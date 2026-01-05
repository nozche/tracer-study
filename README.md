# Dashboard Tracer Study

Dashboard statis untuk melihat agregasi pertanyaan tracer study dengan filter dimensi dan grafik sederhana berbasis Chart.js.

## Cara menjalankan

1. Pastikan sudah berada di folder proyek ini.
2. Jalankan server statis, misalnya:
   ```bash
   python -m http.server 8000
   ```
3. Buka `http://localhost:8000` di peramban.

## Fitur

- Filter dimensi: angkatan, fakultas, dan periode.
- Agregat per pertanyaan:
  - Hitung jumlah per opsi untuk pertanyaan pilihan.
  - Rata-rata dan distribusi skala untuk pertanyaan skala (Likert).
- Visualisasi: grafik batang dan pai menggunakan Chart.js di tampilan staf.
