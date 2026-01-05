# tracer-study

## Dashboard requirements

This repository collects requirements for a tracer study dashboard that supports staff oversight and data operations.

### Ringkasan kebutuhan
- Tampilkan metrik utama: total undangan dikirim, jumlah respons masuk, serta progres per fakultas dan per angkatan.
- Sediakan filter, pencarian, dan ekspor CSV/Excel untuk data respons/jawaban (gunakan query join atau view agar efisien).
- Ringkas status partisipasi: belum buka, draft, dan submit.

### Detail fungsional

#### 1) Metrik dan visualisasi
- **Total undangan**: jumlah email/kontak undangan yang sudah dikirim.
- **Respons masuk**: jumlah responden yang sudah mulai atau menyelesaikan survei.
- **Progres per fakultas/angkatan**: tabel atau chart yang menampilkan total undangan, respons yang sudah masuk, serta persentase progres per fakultas dan per tahun angkatan.

#### 2) Filter & pencarian
- Filter fakultas, program studi, angkatan, status, dan rentang tanggal undangan/submit.
- Pencarian berdasarkan nama, email, atau nomor identitas.
- Kombinasi filter harus bekerja sekaligus (AND), bukan saling menimpa.

#### 3) Ekspor CSV/Excel
- Ekspor daftar respons dengan filter/pencarian yang aktif.
- Gunakan query berbasis join atau view agar performa ekspor tetap terjaga dan data yang diekspor konsisten dengan tampilan dashboard.
- Sertakan kolom-kolom kunci: identitas responden, fakultas, program studi, angkatan, status, timestamp terakhir, serta ringkasan jawaban jika diperlukan.

#### 4) Status partisipasi
- **Belum buka**: undangan dikirim, tetapi responden belum membuka tautan survei (tidak ada aktivitas).
- **Draft**: responden sudah membuka dan menyimpan sebagian jawaban, tetapi belum submit final.
- **Submit**: responden sudah menyelesaikan dan mengirim survei.

### Contoh struktur data & query
Asumsikan tabel:
- `invites(invite_id, respondent_id, faculty, program, cohort_year, sent_at)`
- `responses(response_id, invite_id, status, updated_at)`
- `answers(response_id, question_id, answer_value)`

Contoh view untuk ringkasan status per fakultas/angkatan:
```sql
CREATE OR REPLACE VIEW v_response_summary AS
SELECT
  i.faculty,
  i.cohort_year,
  COUNT(DISTINCT i.invite_id) AS total_invites,
  COUNT(DISTINCT CASE WHEN r.response_id IS NOT NULL THEN i.invite_id END) AS respondents_started,
  COUNT(DISTINCT CASE WHEN r.status = 'submit' THEN i.invite_id END) AS respondents_submitted,
  COUNT(DISTINCT CASE WHEN r.status = 'draft' THEN i.invite_id END) AS respondents_draft,
  COUNT(DISTINCT CASE WHEN r.status IS NULL THEN i.invite_id END) AS respondents_not_opened
FROM invites i
LEFT JOIN responses r ON r.invite_id = i.invite_id
GROUP BY i.faculty, i.cohort_year;
```

Contoh ekspor dengan filter (pseudocode SQL):
```sql
SELECT
  i.invite_id,
  p.full_name,
  p.email,
  i.faculty,
  i.program,
  i.cohort_year,
  COALESCE(r.status, 'belum_buka') AS status,
  r.updated_at
FROM invites i
JOIN people p ON p.person_id = i.respondent_id
LEFT JOIN responses r ON r.invite_id = i.invite_id
WHERE (:faculty IS NULL OR i.faculty = :faculty)
  AND (:program IS NULL OR i.program = :program)
  AND (:cohort_year IS NULL OR i.cohort_year = :cohort_year)
  AND (:status IS NULL OR COALESCE(r.status, 'belum_buka') = :status)
  AND (:date_from IS NULL OR r.updated_at >= :date_from)
  AND (:date_to IS NULL OR r.updated_at <= :date_to)
ORDER BY r.updated_at DESC NULLS LAST;
```

### Catatan implementasi
- Gunakan indeks pada kolom `invite_id`, `faculty`, `cohort_year`, `status`, dan `updated_at` untuk mempercepat filter dan ekspor.
- Simpan ekspor dalam job async jika dataset besar; beri notifikasi setelah file siap.
- Validasi bahwa ekspor mengikuti filter/pencarian yang sama dengan dashboard agar konsisten.
