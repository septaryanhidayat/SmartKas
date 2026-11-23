---
trigger: model_decision
description: "Aturan perancangan tabel, migrasi MySQL, indexing, foreign key, dan seeder untuk sistem keuangan."
---

# Aturan Database & Migrasi MySQL

1. **Engine & Charset:** Gunakan InnoDB dengan `utf8mb4` dan `utf8mb4_unicode_ci`.
2. **Foreign Keys:** Semua relasi wajib memiliki `foreignId()->constrained()` dengan kebijakan `restrictOnDelete()` untuk data transaksi agar tidak ada data keuangan yang hilang akibat *cascade delete*.
3. **Soft Deletes & Audit Columns:**
   * Semua tabel utama dan transaksi wajib memiliki `softDeletes()`.
   * Tabel transaksi wajib mencatat `created_by` dan `updated_by` (relasi ke `users.id`).
4. **Indexing Wajib:**
   * Tambahkan *composite index* pada kolom yang sering difilter untuk laporan dan tunggakan, seperti `['unit_id', 'status']`, `['unit_id', 'transaction_date']`, dan `['student_id', 'billing_month', 'billing_year']`.
5. **Integritas Migrasi:**
   * Jangan mengedit file migrasi yang sudah dijalankan di *staging/production*; buat migrasi baru untuk perubahan skema.
   * Sediakan `DatabaseSeeder` yang mengisi 4 unit default (KB/TKIT, SDIT, SMPIT, SMAIT), Role & Permission dasar, serta akun uji coba untuk masing-masing role.