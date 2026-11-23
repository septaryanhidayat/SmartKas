# Roadmap Pengembangan Bertahap (Task Plan untuk Antigravity IDE)

Gunakan daftar fase di bawah ini sebagai panduan eksekusi bertahap di Antigravity IDE. Selesaikan dan verifikasi (*test passing*) setiap fase sebelum melanjutkan ke fase berikutnya.

## Fase 0: Inisialisasi Proyek & Fondasi
- [ ] Inisialisasi proyek Laravel 13 di Laravel Herd (PHP 8.4) & konfigurasi MySQL.
- [ ] Instalasi paket: `spatie/laravel-permission`, `spatie/laravel-activitylog`, `barryvdh/laravel-dompdf`, `maatwebsite/excel`.
- [ ] Konfigurasi timezone `Asia/Jakarta`, locale `id`, dan helper format Rupiah.
- [ ] Buat seluruh PHP 8.4 Backed Enums di `app/Enums/`.
- [ ] Setup layout dasar dashboard (Sidebar navigasi berbasis Role & indikator Unit aktif).

## Fase 1: Autentikasi, RBAC & Master Data
- [ ] Buat migrasi & model: `units`, `users`, `academic_years`, `classrooms`, `students`, `fee_components`, `discounts`, `student_discounts`, `cash_accounts`.
- [ ] Implementasi Trait `BelongsToUnit` untuk isolasi data otomatis per unit.
- [ ] Buat `DatabaseSeeder` (4 unit SIT + Yayasan, 4 Role & Permission, akun demo per role).
- [ ] CRUD Master Data (Unit, Tahun Ajaran, Kelas, Siswa, Komponen Biaya, Diskon/Beasiswa, Akun Kas/Bank).
- [ ] Feature Test: Pastikan Admin SDIT tidak bisa mengakses atau mengubah data siswa SMPIT.

## Fase 2: Modul Tagihan & Pembayaran (Account Receivable)
- [ ] Buat migrasi & model: `invoices`, `payments`, `payment_items`, `cash_ledgers`.
- [ ] Implementasi `GenerateMonthlyInvoicesAction` & Scheduled Command (otomatis tanggal 1 + kalkulasi diskon siswa).
- [ ] Implementasi `GenerateIncidentalInvoicesAction` (tagihan Uang Pangkal/Seragam massal & individu).
- [ ] Implementasi `RecordStudentPaymentAction` (pembayaran penuh, cicilan/parsial, multi-tagihan dalam 1 kwitansi).
- [ ] Implementasi `VoidStudentPaymentAction` (pembatalan dengan jurnal pembalik).
- [ ] Generate e-Kwitansi PDF (`ReceiptPdfService`) dengan kop surat masing-masing unit.
- [ ] Integrasi `WhatsAppGatewayService` + `SendWhatsAppReceiptJob` (Queue).
- [ ] Dashboard Tunggakan (Arrears) dengan filter Unit, Kelas, & Komponen Biaya.
- [ ] Feature Test: Skenario pembayaran cicilan 2x hingga lunas, cegah *overpayment*, dan skenario *void*.

## Fase 3: Modul Pengeluaran, Approval & Petty Cash
- [ ] Buat migrasi & model: `expense_categories`, `expense_requests`.
- [ ] Implementasi alur pengajuan dana (`SubmitExpenseRequestAction`).
- [ ] Implementasi persetujuan berjenjang (`ApproveExpenseRequestAction`: Kepsek $\rightarrow$ Bendahara Yayasan) & fitur tolak dengan alasan.
- [ ] Implementasi pencairan dana (`DisburseExpenseAction`) yang otomatis memotong saldo akun kas/bank dan mencatat ke `cash_ledgers`.
- [ ] Modul pencatatan Biaya Operasional rutin & Manajemen *Petty Cash* (Kas Kecil) per unit + upload nota.
- [ ] Feature Test: Verifikasi alur *approval* berjenjang dan pencatatan mutasi keluar di Buku Kas.

## Fase 4: Pelaporan, Audit Trail & Tutup Buku
- [ ] Buat migrasi & model `closing_periods` serta `ClosingPeriodService`.
- [ ] Implementasi fitur **Tutup Buku Bulanan** oleh Bendahara Yayasan dan validasi penguncian di seluruh Action transaksi.
- [ ] Implementasi `FinancialReportService`:
  - [ ] Buku Kas Umum (Mutasi harian & saldo berjalan per akun).
  - [ ] Laporan Surplus/Defisit (Laba Rugi) filter Per Unit & Konsolidasi Total Yayasan.
  - [ ] Rekap Penerimaan per Komponen Biaya.
- [ ] Export Excel (`.xlsx`) dan PDF untuk semua laporan.
- [ ] Halaman **Audit Trail** (Log Aktivitas) untuk Superadmin & Bendahara Yayasan.

## Fase 5: Uji Akhir, Optimasi & Persiapan Produksi
- [ ] Review seluruh *N+1 query* menggunakan eager loading & audit *database indexing*.
- [ ] Jalankan seluruh test suite (`php artisan test`) & code formatting (`./vendor/bin/pint`).
- [ ] Siapkan panduan *deployment* (Cron Job untuk Scheduler Laravel, Supervisor untuk Queue Worker WhatsApp, dan Backup Database MySQL otomatis harian).