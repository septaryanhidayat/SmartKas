# Sistem Keuangan Terpusat Yayasan Generasi Robbani Sumatera Selatan

Aplikasi web manajemen keuangan terpusat yang dirancang khusus untuk **Bendahara Yayasan** dan **Admin Keuangan Unit** guna mengelola arus kas, tagihan SPP, penerimaan kasir, pengajuan anggaran, dan pelaporan keuangan secara transparan, akurat, dan terisolasi untuk **4 unit sekolah Islam terpadu**:
1. **KB/TKIT Robbani**
2. **SDIT Robbani**
3. **SMPIT Robbani**
4. **SMAIT Robbani**

---

## 🛠️ Tech Stack & Spesifikasi

* **Framework:** Laravel 13.x
* **Bahasa:** PHP 8.4 (menggunakan *Strict Types*, *Backed Enums*, *Typed Properties*)
* **Database:** MySQL 8.x (*InnoDB, utf8mb4_unicode_ci, BigInteger Rupiah*)
* **Frontend:** Blade + Tailwind CSS + Alpine.js
* **PDF & Excel:** `barryvdh/laravel-dompdf` & `maatwebsite/excel`
* **RBAC:** `spatie/laravel-permission`
* **Audit Trail:** `spatie/laravel-activitylog`
* **Testing:** Pest PHP 5
* **Code Styling:** Laravel Pint

---

## 👥 Pengelola & Pejabat Keuangan Terdaftar

Sistem telah dikonfigurasi dengan struktur organisasi resmi **Yayasan Generasi Robbani Sumatera Selatan**:

| Peran / Jabatan | Nama Pejabat & Pengelola | Email Login | Cakupan Otoritas |
| :--- | :--- | :--- | :--- |
| **Ketua Yayasan** | **Sughesti Wulandari, S.Pd** | `superadmin@sit.sch.id` | Superadmin (Seluruh Unit & Kebijakan) |
| **Bendahara Yayasan** | **Dessy Aprianty, S.E.** | `bendahara@sit.sch.id` | Otorisator Kas, Pencairan & Tutup Buku |
| **Admin Yayasan** | **Anis Monica, S.H.** | `admin.yayasan@sit.sch.id` | Pusat Pembayaran Siswa Seluruh Unit |
| **Admin TKIT** | **Rojanah, S.E.** | `admin.tkit@sit.sch.id` | Kasir & Tagihan KB/TKIT Robbani |
| **Admin SDIT** | **Risma, S.Pd** | `admin.sdit@sit.sch.id` | Kasir & Tagihan SDIT Robbani |
| **Admin SMPIT** | **Adelia Jesika, S.Pd** | `admin.smpit@sit.sch.id` | Kasir & Tagihan SMPIT Robbani |
| **Admin SMAIT** | **Admin Keuangan SMAIT Robbani** | `admin.smait@sit.sch.id` | Kasir & Tagihan SMAIT Robbani |

*Kata Sandi Default Pengujian:* `password123` (Tersedia juga tombol 1-Click Quick Login di `/login`).

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
