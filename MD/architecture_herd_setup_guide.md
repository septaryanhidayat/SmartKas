# Arsitektur Aplikasi & Panduan Setup Laravel Herd

## 1. Setup Lingkungan Lokal (Laravel Herd)
Pastikan **Laravel Herd** sudah terinstal dengan PHP 8.4 aktif dan layanan MySQL 8 berjalan (melalui Herd Services / DBngin / MySQL lokal).

### Langkah Inisialisasi Proyek
```bash
# Masuk ke direktori parked Laravel Herd (misal: ~/Herd)
cd ~/Herd

# Buat proyek Laravel 13 baru
laravel new keuangan-yayasan

# Masuk ke folder proyek
cd keuangan-yayasan

# Pastikan versi PHP menggunakan 8.4 di Herd
herd use php@8.4

# Aktifkan HTTPS lokal (opsional tapi disarankan)
herd secure keuangan-yayasan
```

### Instalasi Paket Pendukung Utama
```bash
composer require spatie/laravel-permission spatie/laravel-activitylog barryvdh/laravel-dompdf maatwebsite/excel
```

### Konfigurasi `.env` Lokal
```env
APP_NAME="Keuangan Yayasan SIT"
APP_URL=https://keuangan-yayasan.test
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=keuangan_yayasan
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
```

---

## 2. Struktur Direktori Aplikasi (`app/`)
Aplikasi menggunakan pola **Action-Service-Policy** agar logika keuangan tetap modular, mudah diuji (*unit & feature testable*), dan terpisah dari HTTP Controller.

```text
app/
├── Actions/
│   ├── Billing/
│   │   ├── GenerateMonthlyInvoicesAction.php
│   │   └── GenerateIncidentalInvoicesAction.php
│   ├── Payment/
│   │   ├── RecordStudentPaymentAction.php
│   │   └── VoidStudentPaymentAction.php
│   ├── Expense/
│   │   ├── SubmitExpenseRequestAction.php
│   │   ├── ApproveExpenseRequestAction.php
│   │   └── DisburseExpenseAction.php
│   └── Accounting/
│       └── CloseMonthlyPeriodAction.php
├── Console/Commands/
│   └── GenerateMonthlyBillingCommand.php
├── Enums/
│   ├── BillingType.php
│   ├── ExpenseStatus.php
│   ├── InvoiceStatus.php
│   ├── PaymentMethod.php
│   ├── StudentStatus.php
│   └── UserRole.php
├── Jobs/
│   └── SendWhatsAppReceiptJob.php
├── Models/
│   ├── Concerns/
│   │   └── BelongsToUnit.php
│   └── ... (Unit, Student, Invoice, Payment, ExpenseRequest, CashLedger, dll.)
├── Policies/
│   ├── InvoicePolicy.php
│   ├── PaymentPolicy.php
│   └── ExpenseRequestPolicy.php
└── Services/
    ├── ClosingPeriodService.php
    ├── FinancialReportService.php
    ├── ReceiptPdfService.php
    └── WhatsAppGatewayService.php
```

---

## 3. Alur Logika Kritis (Critical Business Flows)

### A. Alur Penerimaan Pembayaran (`RecordStudentPaymentAction`)
1. Validasi periode *Tutup Buku* melalui `ClosingPeriodService::ensurePeriodIsOpen()`.
2. Buka `DB::transaction()`.
3. Kunci baris `cash_accounts` dan semua `invoices` terkait dengan `lockForUpdate()`.
4. Validasi bahwa nominal bayar per invoice tidak melebihi sisa tagihan (`net_amount - paid_amount`).
5. Buat record `payments` dan `payment_items`.
6. Update `paid_amount` pada masing-masing `invoices`:
   * Jika `paid_amount == net_amount` $\rightarrow$ status `paid`.
   * Jika `paid_amount > 0 && paid_amount < net_amount` $\rightarrow$ status `partial`.
7. Catat mutasi masuk di `cash_ledgers` dan tambah `current_balance` pada `cash_accounts`.
8. Commit transaksi.
9. *Dispatch* `SendWhatsAppReceiptJob` ke Queue secara *asynchronous* (agar kasir tidak menunggu respons API WhatsApp).

### B. Alur Pembatalan / Void Pembayaran (`VoidStudentPaymentAction`)
1. Cek periode *Tutup Buku* (tidak bisa void jika bulan sudah ditutup).
2. Di dalam `DB::transaction()`:
   * Ubah status `payments` menjadi `void` dan simpan `void_reason`.
   * Kurangi `paid_amount` pada setiap `invoices` terkait dan kembalikan statusnya ke `partial` atau `unpaid`.
   * Buat entri pembalik (*reversal*) bertipe `out` di `cash_ledgers` dan kurangi `current_balance` di `cash_accounts`.
   * Catat di `activity_log`.

### C. Isolasi Data Unit (`BelongsToUnit` Trait)
* Jika user yang sedang login memiliki `unit_id` (Admin Unit / Kepala Sekolah), Eloquent Global Scope otomatis menambahkan `WHERE unit_id = auth()->user()->unit_id`.
* Jika user adalah Superadmin atau Bendahara Yayasan (`unit_id === null`), Global Scope tidak membatasi query dan menyediakan *dropdown* filter Unit untuk tampilan per unit maupun konsolidasi total.