# tracer-study

Backend ringan untuk tracer study alumni menggunakan FastAPI + SQLite.

## Menjalankan secara lokal

```bash
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
python -m app.seed  # seed data contoh + token publik sample-token-123
uvicorn app.main:app --reload
```

## Endpoint utama

- `GET /forms/{token}`: ambil detail form + pertanyaan berdasarkan token unik alumni.
- `POST /forms/{token}/responses`: simpan jawaban sebagai draft (`submit=false`) atau kirim akhir (`submit=true` dengan validasi required).
- `POST /forms/{token}/reopen`: buka kembali jawaban yang sudah submitted jika kebijakan mengizinkan dan periode aktif masih berlaku.

Model data menggunakan tabel `forms`, `invitations`, `questions`, `responses`, dan `answers` untuk melacak status draft/submitted per token.
