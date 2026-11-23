---
trigger: glob
globs: "**/*.php"
description: "Standar penulisan kode PHP 8.4, Laravel 13, dan aturan keamanan transaksi keuangan."
---

# Standar Kode PHP 8.4 & Laravel 13 (Keuangan)

## 1. Standar PHP 8.4 & Laravel
* Selalu sertakan `declare(strict_types=1);` di bagian paling atas setiap file PHP baru.
* Gunakan *typed properties*, *return types*, dan *Backed Enums* untuk semua status (misal: `InvoiceStatus`, `PaymentMethod`, `ExpenseStatus`, `UnitCode`, `UserRole`).
* Hindari *Fat Controllers*. Gunakan pola **Action Classes** (`app/Actions/...`) untuk setiap logika bisnis (misal: `RecordStudentPaymentAction`, `GenerateMonthlyBillingAction`, `ApproveExpenseRequestAction`).
* Selalu gunakan **Form Request** (`app/Http/Requests/...`) untuk validasi input dan **Policy** (`app/Policies/...`) untuk otorisasi.

## 2. Aturan Penanganan Uang & Transaksi
* **Tipe Data Nominal:** Simpan nominal Rupiah di MySQL sebagai `BIGINT UNSIGNED` (atau `DECIMAL(15,2)` jika butuh desimal). Jangan pernah menggunakan `FLOAT` atau `DOUBLE` untuk uang.
* **Atomisitas:** Setiap proses yang mengubah lebih dari satu tabel (contoh: membuat record `payments`, memperbarui `paid_amount` dan `status` di `invoices`, serta mencatat di `cash_ledgers`) wajib menggunakan `DB::transaction(fn () => ...)`.
* **Concurrency Lock:** Gunakan `lockForUpdate()` saat membaca saldo akun kas/bank atau sisa tagihan invoice di dalam transaksi untuk mencegah *race condition* (pembayaran ganda secara bersamaan).
* **Idempotency:** Generate nomor kwitansi (`receipt_number`) dan nomor tagihan (`invoice_number`) secara unik dengan format terstandar (contoh: `INV/SDIT/202607/0001`, `KW/SDIT/202607/0001`).

## 3. Scoping Unit & Keamanan
* Terapkan Global Scope atau Trait `BelongsToUnit` pada model yang terikat dengan unit sekolah.
* Validasi periode *Tutup Buku* (`ClosingPeriodService::ensurePeriodIsOpen($unitId, $transactionDate)`) di dalam setiap Action yang memodifikasi transaksi keuangan.
* Catat seluruh perubahan penting (pembuatan, pengeditan nominal, pembatalan/void, persetujuan) menggunakan `spatie/laravel-activitylog`.