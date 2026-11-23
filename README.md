# Sistem Keuangan Terpusat Yayasan Pendidikan Islam Terpadu (SIT)

Aplikasi web manajemen keuangan terpusat yang dirancang khusus untuk **Bendahara Yayasan** dan **Admin Keuangan Unit** guna mengelola arus kas, tagihan SPP, penerimaan kasir, pengajuan anggaran, dan pelaporan keuangan secara transparan, akurat, dan terisolasi untuk **4 unit sekolah**:
1. **KB/TKIT Nurul Fikri**
2. **SDIT Nurul Fikri**
3. **SMPIT Nurul Fikri**
4. **SMAIT Nurul Fikri**

---

## 🛠️ Tech Stack & Spesifikasi

* **Framework:** Laravel 13.x
* **Bahasa:** PHP 8.4 (menggunakan *Strict Types*, *Backed Enums*, *Typed Properties*)
* **Database:** MySQL 8.x (*InnoDB, utf8mb4_unicode_ci, BigInteger Rupiah*)
* **Frontend:** Blade + Tailwind CSS + Alpine.js
* **PDF & Excel:** `barryvdh/laravel-dompdf` & `maatwebsite/excel`
* **RBAC:** `spatie/laravel-permission`
* **Audit Trail:** `spatie/laravel-activitylog`
* **Testing:** Pest PHP 5 (13 tests, 47 assertions passing)
* **Code Styling:** Laravel Pint

---

## 🌟 Fitur Utama

### 1. Manajemen Master Data & Isolasi Unit (Data Scoping)
* Master 4 Unit SIT + Yayasan Pusat lengkap dengan kop surat dan data kepala sekolah.
* Master Tahun Ajaran aktif (`2026/2027`).
* Master Kelas dan Siswa dengan status (`active`, `graduated`, `transferred`, `on_leave`).
* Master Komponen Biaya: Rutin bulanan (SPP, Katering), Insidentil (Uang Pangkal/Gedung), Tahunan (Seragam, Daftar Ulang).
* Master Beasiswa & Diskon (Anak Yatim 100%, Anak Guru 50%, Diskon Saudara Kandung).
* Master Akun Kas & Bank Yayasan serta *Petty Cash* per unit.
* Isolasi data otomatis via Trait `BelongsToUnit` (Admin SDIT tidak dapat melihat/mengubah data siswa SMPIT).

### 2. Modul Tagihan & Kasir SPP (Account Receivable)
* **Auto-Billing Generator:** Scheduled Artisan command (`php artisan billing:generate-monthly`) yang otomatis menghitung tarif SPP, memotong diskon beasiswa aktif, dan mencegah duplikasi tagihan.
* **Tagihan Insidentil:** Pembuatan tagihan massal per unit/kelas atau siswa perorangan.
* **Point-of-Sale (POS) Kasir SPP:** Antarmuka kasir cepat untuk memilih siswa, melihat tagihan belum lunas, mencentang pembayaran penuh maupun cicilan (parsial), multi-tagihan dalam 1 kwitansi, dan memilih metode pembayaran (Tunai, Transfer Bank, QRIS, Virtual Account).
* **e-Kwitansi PDF Resmi:** Cetak kwitansi format A5 landscape berkop resmi unit sekolah, rincian komponen, terbilang otomatis, dan tanda tangan kasir.
* **Integrasi WhatsApp Gateway:** Dispatch *queued job* notifikasi pesan konfirmasi pembayaran dan tautan e-Kwitansi ke nomor WhatsApp orang tua siswa.
* **Pembatalan Transaksi (Void):** Mekanisme void transaksi salah input yang secara otomatis memulihkan sisa tagihan siswa dan mencatat jurnal pembalik di Buku Kas.

### 3. Modul Belanja, Approval & Kas Kecil (Account Payable)
* **Pengajuan Dana (Cash Advance / RAB):** Pengajuan proposal biaya kegiatan oleh admin unit.
* **Approval Berjenjang:**
  * Alur: `Draft` &rarr; `Diajukan` &rarr; `Disetujui Kepala Sekolah (Tahap 1)` &rarr; `Disetujui Bendahara Yayasan (Tahap 2)` &rarr; `Dicairkan (Disbursed)`.
  * Fitur penyesuaian nominal disetujui dan penolakan disertai alasan wajib.
* **Pencatatan Belanja Langsung Operasional:** Input langsung biaya listrik, air, internet, ATK, pemeliharaan gedung.
* **Manajemen Petty Cash (Kas Kecil):** Top-up saldo imprest dan pengeluaran harian kas kecil.

### 4. Pelaporan Keuangan & Akuntansi
* **Buku Kas Umum (Mutasi Kas):** Aliran dana masuk (Debet), keluar (Kredit), saldo awal, dan saldo akhir berjalan per akun kas/bank.
* **Laporan Surplus / Defisit (Laba Rugi):** Komparasi total pendapatan operasional vs beban pengeluaran, dengan filter **Per Unit Sekolah** dan **Konsolidasi Total Yayasan**.
* **Dashboard Tunggakan Siswa (Arrears):** Rekap tunggakan siswa dengan tombol pintas pengingat tagihan via WhatsApp.
* **Export Data:** Seluruh laporan dapat diekspor langsung ke **Excel (`.xlsx`)** dan **PDF**.

### 5. Tutup Buku & Audit Trail
* **Tutup Buku Bulanan (Closing Lock):** Fitur penguncian periode keuangan bulanan oleh Bendahara Yayasan sehingga transaksi pada periode tertutup bersifat *immutable* (tidak dapat ditambah/diubah/dibatalkan).
* **Buka Kunci (Reopen):** Membuka kembali periode terkunci dengan catatan justifikasi wajib.
* **Audit Trail:** Mencatat setiap perubahan data, aktor (`user_id`), IP, dan nilai lama vs baru.

---

## 👥 Akun Uji Coba (Demo Credentials)

Tersedia fitur **Quick Login (⚡ Mode Cepat)** di halaman `/login` untuk langsung login tanpa perlu mengetik kata sandi:

| Role | Nama Pengguna | Email | Password |
| :--- | :--- | :--- | :--- |
| **Superadmin** | Ketua Yayasan | `superadmin@sit.sch.id` | `password123` |
| **Bendahara** | Hj. Maryam (Bendahara Yayasan) | `bendahara@sit.sch.id` | `password123` |
| **Kepsek TKIT** | Kepala Sekolah TKIT | `kepsek.tkit@sit.sch.id` | `password123` |
| **Admin TKIT** | Admin Keuangan TKIT | `admin.tkit@sit.sch.id` | `password123` |
| **Kepsek SDIT** | Kepala Sekolah SDIT | `kepsek.sdit@sit.sch.id` | `password123` |
| **Admin SDIT** | Admin Keuangan SDIT | `admin.sdit@sit.sch.id` | `password123` |
| **Kepsek SMPIT**| Kepala Sekolah SMPIT | `kepsek.smpit@sit.sch.id` | `password123` |
| **Admin SMPIT** | Admin Keuangan SMPIT | `admin.smpit@sit.sch.id` | `password123` |
| **Kepsek SMAIT**| Kepala Sekolah SMAIT | `kepsek.smait@sit.sch.id` | `password123` |
| **Admin SMAIT** | Admin Keuangan SMAIT | `admin.smait@sit.sch.id` | `password123` |

---

## 🚀 Panduan Instalasi Lokal (Laravel Herd / CLI)

### 1. Clone & Setup Environment
```bash
git clone https://github.com/septaryanhidayat/keuangan.git
cd keuangan

composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 2. Konfigurasi Database MySQL
Pastikan layanan MySQL berjalan (port 3306) dan database `keuangan_yayasan` tersedia. Sesuaikan `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=keuangan_yayasan
DB_USERNAME=root
DB_PASSWORD=bismillah123
```

### 3. Migrasi & Seeding Data
Jalankan migrasi tabel dan pengisian master data awal:
```bash
php artisan migrate:fresh --seed
```

### 4. Build Frontend Assets
```bash
npm run build
```

### 5. Jalankan Server Lokal
Jika menggunakan Laravel Herd: akses langsung di browser `http://keuangan.test`
Atau menggunakan built-in server:
```bash
php artisan serve
```
Buka browser di `http://localhost:8000` dan login menggunakan akun demo.

---

## 🧪 Menjalankan Automated Tests & Linter

```bash
# Menjalankan seluruh test suite (Pest)
php artisan test

# Menjalankan code formatter (Pint)
./vendor/bin/pint --test
```

---

## ⚙️ Perintah Artisan Khusus

```bash
# Generate tagihan bulanan SPP rutin
php artisan billing:generate-monthly

# Generate tagihan untuk unit tertentu pada bulan/tahun spesifik
php artisan billing:generate-monthly --unit=2 --month=7 --year=2026
```

---
&copy; {{ date('Y') }} Yayasan Pendidikan Islam Terpadu. All rights reserved.
