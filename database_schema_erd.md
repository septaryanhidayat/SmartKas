# Skema Database MySQL (Single Database + `unit_id` Scoping)

Seluruh tabel menggunakan *engine* `InnoDB`, `utf8mb4_unicode_ci`, tipe nominal `BIGINT UNSIGNED` (dalam satuan Rupiah penuh), serta `SoftDeletes` untuk tabel master & transaksi.

## 1. Master Data & Organisasi

### `units`
Menyimpan 4 unit sekolah + 1 entitas pusat (Yayasan) jika diperlukan untuk transaksi level pusat.
* `id` (PK)
* `code` (VARCHAR 20, UNIQUE) — contoh: `YAYASAN`, `TKIT`, `SDIT`, `SMPIT`, `SMAIT`
* `name` (VARCHAR 100)
* `level` (ENUM: `foundation`, `tk`, `sd`, `smp`, `sma`)
* `address` (TEXT, nullable)
* `phone` (VARCHAR 30, nullable)
* `principal_name` (VARCHAR 100, nullable)
* `logo_path` (VARCHAR 255, nullable)
* `is_active` (BOOLEAN, default true)
* `timestamps`, `softDeletes`

### `users`
* `id` (PK)
* `unit_id` (FK -> `units.id`, nullable untuk Superadmin/Bendahara Yayasan)
* `name` (VARCHAR 100)
* `email` (VARCHAR 100, UNIQUE)
* `password` (VARCHAR 255)
* `phone` (VARCHAR 30, nullable)
* `is_active` (BOOLEAN, default true)
* `timestamps`, `softDeletes`
*(Role & Permission dikelola oleh tabel standar `spatie/laravel-permission`)*

### `academic_years`
* `id` (PK)
* `name` (VARCHAR 20, UNIQUE) — contoh: `2026/2027`
* `start_date` (DATE)
* `end_date` (DATE)
* `is_active` (BOOLEAN, default false)
* `timestamps`, `softDeletes`

### `classrooms`
* `id` (PK)
* `unit_id` (FK -> `units.id`)
* `academic_year_id` (FK -> `academic_years.id`)
* `name` (VARCHAR 50) — contoh: `1A Al-Fatih`, `TK B`
* `grade_level` (TINYINT UNSIGNED) — tingkat kelas
* `timestamps`, `softDeletes`

### `students`
* `id` (PK)
* `unit_id` (FK -> `units.id`)
* `classroom_id` (FK -> `classrooms.id`, nullable)
* `nis` (VARCHAR 30, UNIQUE)
* `nisn` (VARCHAR 30, nullable)
* `name` (VARCHAR 150)
* `parent_name` (VARCHAR 150)
* `parent_whatsapp` (VARCHAR 30) — format internasional `628xxx`
* `status` (ENUM: `active`, `graduated`, `transferred`, `on_leave`)
* `enrolled_at` (DATE)
* `timestamps`, `softDeletes`

### `fee_components`
Master komponen biaya (SPP, Uang Pangkal, Seragam, Katering, dll.).
* `id` (PK)
* `unit_id` (FK -> `units.id`)
* `academic_year_id` (FK -> `academic_years.id`)
* `code` (VARCHAR 30)
* `name` (VARCHAR 100)
* `billing_type` (ENUM: `monthly`, `annual`, `incidental`)
* `default_amount` (BIGINT UNSIGNED)
* `is_active` (BOOLEAN, default true)
* `timestamps`, `softDeletes`

### `discounts` & `student_discounts`
* **`discounts`:** `id`, `unit_id` (nullable = berlaku semua unit), `name` (misal: `Beasiswa Yatim 50%`), `type` (ENUM: `percentage`, `fixed`), `value` (BIGINT UNSIGNED), `is_active`, `timestamps`, `softDeletes`.
* **`student_discounts`:** `id`, `student_id` (FK), `discount_id` (FK), `fee_component_id` (FK, nullable = berlaku semua komponen), `valid_from` (DATE), `valid_until` (DATE, nullable), `notes` (TEXT, nullable), `timestamps`, `softDeletes`.

### `cash_accounts`
Daftar akun Kas Tunai, Petty Cash Unit, dan Rekening Bank Yayasan.
* `id` (PK)
* `unit_id` (FK -> `units.id`, nullable = akun milik pusat/yayasan)
* `name` (VARCHAR 100) — contoh: `BSI Operasional Yayasan`, `Petty Cash SDIT`
* `type` (ENUM: `bank`, `cash`, `petty_cash`)
* `account_number` (VARCHAR 50, nullable)
* `bank_name` (VARCHAR 50, nullable)
* `current_balance` (BIGINT, default 0)
* `is_active` (BOOLEAN, default true)
* `timestamps`, `softDeletes`

---

## 2. Modul Tagihan & Pembayaran (Account Receivable)

### `invoices` (Tagihan Siswa)
* `id` (PK)
* `unit_id` (FK -> `units.id`)
* `student_id` (FK -> `students.id`)
* `fee_component_id` (FK -> `fee_components.id`)
* `academic_year_id` (FK -> `academic_years.id`)
* `invoice_number` (VARCHAR 50, UNIQUE)
* `billing_month` (TINYINT UNSIGNED, nullable) — `1` s/d `12` untuk tagihan bulanan
* `billing_year` (SMALLINT UNSIGNED, nullable)
* `due_date` (DATE)
* `gross_amount` (BIGINT UNSIGNED) — nominal awal
* `discount_amount` (BIGINT UNSIGNED, default 0) — total potongan beasiswa
* `net_amount` (BIGINT UNSIGNED) — `gross_amount - discount_amount`
* `paid_amount` (BIGINT UNSIGNED, default 0) — total yang sudah dibayar (mendukung cicilan)
* `status` (ENUM: `unpaid`, `partial`, `paid`, `void`)
* `notes` (TEXT, nullable)
* `created_by` (FK -> `users.id`)
* `timestamps`, `softDeletes`
* **Indexes:** `INDEX (unit_id, status)`, `INDEX (student_id, billing_year, billing_month)`

### `payments` (Header Transaksi Pembayaran / Kwitansi)
Satu kwitansi dapat melunasi/mencicil satu atau beberapa tagihan sekaligus.
* `id` (PK)
* `unit_id` (FK -> `units.id`)
* `student_id` (FK -> `students.id`)
* `cash_account_id` (FK -> `cash_accounts.id`)
* `receipt_number` (VARCHAR 50, UNIQUE)
* `payment_date` (DATETIME)
* `payment_method` (ENUM: `cash`, `transfer`, `qris`, `virtual_account`)
* `total_amount` (BIGINT UNSIGNED)
* `reference_number` (VARCHAR 100, nullable) — no referensi transfer
* `proof_path` (VARCHAR 255, nullable) — bukti transfer
* `status` (ENUM: `completed`, `void`)
* `void_reason` (TEXT, nullable)
* `voided_by` (FK -> `users.id`, nullable)
* `voided_at` (DATETIME, nullable)
* `whatsapp_sent_at` (DATETIME, nullable)
* `created_by` (FK -> `users.id`)
* `timestamps`, `softDeletes`

### `payment_items` (Detail Alokasi Pembayaran ke Tagihan)
* `id` (PK)
* `payment_id` (FK -> `payments.id`)
* `invoice_id` (FK -> `invoices.id`)
* `amount` (BIGINT UNSIGNED) — nominal yang dialokasikan ke invoice tersebut
* `timestamps`

---

## 3. Modul Pengeluaran, Approval & Petty Cash (Account Payable)

### `expense_categories`
* `id` (PK)
* `code` (VARCHAR 30, UNIQUE)
* `name` (VARCHAR 100) — contoh: `Listrik & Air`, `Gaji Guru`, `ATK`, `Kegiatan Siswa`
* `is_active` (BOOLEAN, default true)
* `timestamps`, `softDeletes`

### `expense_requests` (Pengajuan Dana & Pengeluaran Operasional)
* `id` (PK)
* `unit_id` (FK -> `units.id`)
* `expense_category_id` (FK -> `expense_categories.id`)
* `cash_account_id` (FK -> `cash_accounts.id`, nullable saat draft, wajib saat cair)
* `request_number` (VARCHAR 50, UNIQUE)
* `title` (VARCHAR 150)
* `description` (TEXT, nullable)
* `expense_type` (ENUM: `cash_advance`, `operational`, `petty_cash`)
* `requested_amount` (BIGINT UNSIGNED)
* `approved_amount` (BIGINT UNSIGNED, nullable)
* `transaction_date` (DATE)
* `status` (ENUM: `draft`, `submitted`, `approved_principal`, `approved_treasurer`, `disbursed`, `rejected`, `void`)
* `rejection_reason` (TEXT, nullable)
* `attachment_path` (VARCHAR 255, nullable) — bukti nota/proposal
* `requested_by` (FK -> `users.id`)
* `principal_approved_by` (FK -> `users.id`, nullable)
* `principal_approved_at` (DATETIME, nullable)
* `treasurer_approved_by` (FK -> `users.id`, nullable)
* `treasurer_approved_at` (DATETIME, nullable)
* `disbursed_by` (FK -> `users.id`, nullable)
* `disbursed_at` (DATETIME, nullable)
* `timestamps`, `softDeletes`

---

## 4. Modul Buku Kas & Tutup Buku

### `cash_ledgers` (Buku Kas Umum / Mutasi)
Mencatat setiap aliran uang masuk/keluar secara kronologis untuk kebutuhan Buku Kas Umum dan Laporan Laba/Rugi.
* `id` (PK)
* `unit_id` (FK -> `units.id`)
* `cash_account_id` (FK -> `cash_accounts.id`)
* `transaction_date` (DATETIME)
* `type` (ENUM: `in`, `out`)
* `amount` (BIGINT UNSIGNED)
* `balance_before` (BIGINT)
* `balance_after` (BIGINT)
* `reference_type` (VARCHAR 100) — polymorphic (`App\Models\Payment`, `App\Models\ExpenseRequest`, dll.)
* `reference_id` (BIGINT UNSIGNED)
* `description` (VARCHAR 255)
* `created_by` (FK -> `users.id`)
* `timestamps`, `softDeletes`
* **Indexes:** `INDEX (unit_id, transaction_date)`, `INDEX (cash_account_id, transaction_date)`

### `closing_periods` (Tutup Buku)
* `id` (PK)
* `unit_id` (FK -> `units.id`, nullable = penutupan seluruh yayasan)
* `year` (SMALLINT UNSIGNED)
* `month` (TINYINT UNSIGNED) — `1` s/d `12`
* `status` (ENUM: `open`, `closed`)
* `closed_by` (FK -> `users.id`, nullable)
* `closed_at` (DATETIME, nullable)
* `notes` (TEXT, nullable)
* `timestamps`
* **Unique Constraint:** `UNIQUE (unit_id, year, month)`