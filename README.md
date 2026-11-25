# SmartKas — Sistem Keuangan Terpusat Yayasan Generasi Robbani Sumatera Selatan

Aplikasi web manajemen keuangan terpusat (**SmartKas**) untuk **Yayasan Generasi Robbani Sumatera Selatan** (*Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir*) yang dirancang khusus bagi **Ketua Yayasan**, **Bendahara Yayasan**, **Kepala Sekolah**, dan **Admin Keuangan Unit** untuk mengelola arus kas, tagihan SPP, kasir pembayaran, persetujuan pengeluaran berjenjang, tutup buku, hingga pengaturan sistem secara terisolasi dan akuntabel untuk **4 unit sekolah Islam terpadu**:
1. **KB/TKIT Robbani**
2. **SDIT Robbani**
3. **SMPIT Robbani**
4. **SMAIT Robbani**

---

## 🛠️ Tech Stack & Persyaratan Sistem

* **Framework:** Laravel 13.x (`^13.17`)
* **Bahasa:** PHP >= **8.4.1** (*Strict Types*, *Backed Enums*, *Symfony 8.4*, *Spatie Activitylog v5.1*)
* **Database:** MySQL 8.x / MariaDB 10.x (*InnoDB, `utf8mb4_unicode_ci`, BigInteger Rupiah*)
* **Frontend:** Blade + Tailwind CSS v4 (Vite + Bunny Fonts) + Alpine.js
* **PDF & Excel:** `barryvdh/laravel-dompdf` (Kop Surat Logo Yayasan) & `maatwebsite/excel`
* **RBAC & Audit:** `spatie/laravel-permission` & `spatie/laravel-activitylog`
* **Testing & Linter:** Pest PHP 5 & Laravel Pint

---

## ✨ Daftar Lengkap Fitur Aplikasi

### 1. Keamanan Login, RBAC & Isolasi Data Unit
* Login terproteksi menggunakan email resmi `@sitrobbani.sch.id`, validasi akun aktif (`is_active`), sesi database, dan opsi *"Ingat saya di perangkat ini"*.
* Pembagian peran (*Role-Based Access Control*): Ketua Yayasan (*Superadmin*), Bendahara Yayasan (*Treasurer*), Admin Yayasan Pusat Pembayaran, Admin Keuangan Unit, dan Kepala Sekolah Unit.
* **Isolasi Ketat per Unit (`BelongsToUnit`):** Admin Keuangan Unit dan Kepala Sekolah hanya dapat mengakses data siswa, tagihan, kwitansi, kas, dan laporan unit masing-masing.

### 2. Dashboard Ringkasan Keuangan Real-Time
* Kartu KPI utama: Total Saldo Kas & Bank aktif, Penerimaan Bulan Ini (+ hari ini), Pengeluaran Bulan Ini (+ hari ini), dan Total Tunggakan SPP beserta jumlah siswa menunggak.
* Indikator status Tutup Buku bulan berjalan (*Aktif* / *Terkunci*) dan filter **Konsolidasi Yayasan vs Per Unit** untuk level pusat.
* Ringkasan 4 Unit Sekolah (Kepsek, Admin unit, jumlah siswa aktif, persentase pelunasan, penerimaan, dan tunggakan).
* Daftar **8 Mutasi Terakhir BKU**, **Saldo Rekening Kas & Bank Real-Time** (termasuk Bank Syariah Indonesia `7206858502`), serta **Antrean Persetujuan Dana**.

### 3. Direktori Siswa, Kelas & Beasiswa
* Pencarian siswa berdasarkan Nama, NIS, Nama Orang Tua, atau Nomor WhatsApp, lengkap dengan filter Unit dan Kelas.
* Halaman profil siswa menampilkan informasi wali, daftar diskon/beasiswa aktif beserta masa berlaku, riwayat tagihan semua status (dengan unduh PDF Invoice), serta tombol langsung ke Kasir Pembayaran siswa terkait.

### 4. Tagihan & Billing Siswa (*Invoicing*)
* Daftar seluruh tagihan (`Belum Dibayar`, `Cicilan/Partial`, `Lunas`, `Batal/Void`) dengan filter Unit, Status, dan pencarian cepat.
* Tampilan kode tagihan panjang dalam format ringkas (*compact badge*) dengan tooltip kode penuh dan tombol salin (*copy-to-clipboard*).
* **Generate Tagihan Bulanan Massal:** Membuat tagihan rutin per Unit & Tahun Ajaran untuk siswa aktif, otomatis memotong beasiswa/diskon, memakai tanggal jatuh tempo dari Pengaturan Aplikasi, dan anti-duplikat.
* **Generate Tagihan Insidentil / Tahunan:** Membuat tagihan khusus (uang pangkal, seragam, daftar ulang, dll.) ke siswa terpilih menggunakan pencarian checkbox, mendukung nominal kustom dan catatan.
* **Unduh Surat Tagihan Invoice Resmi (PDF):** Ber-kop resmi Yayasan Generasi Robbani Sumatera Selatan dengan **Logo Yayasan**, alamat *Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir*, rincian biaya awal, potongan, terbayar, sisa kewajiban, serta info rekening transfer resmi.

### 5. Kasir Pembayaran SPP (POS) & e-Kwitansi
* Kasir pembayaran cepat dengan dukungan multi-tagihan sekaligus dan pembayaran parsial/cicilan per item tagihan dalam transaksi DB atomik (`DB::transaction()`).
* Mendukung metode **Tunai**, **Transfer Bank**, **QRIS**, dan **Virtual Account** yang langsung memperbarui status tagihan, saldo kas/bank, serta mencatat mutasi ke **Buku Kas Umum**.
* Menyiapkan antrean pesan bukti pembayaran **WhatsApp** ke wali murid.
* **Cetak e-Kwitansi PDF Resmi:** Dilengkapi Kop Yayasan, Logo Yayasan, rincian alokasi, nominal angka & **Terbilang**, penandatangan, serta tanda status apabila *Void*.
* **Pembatalan Kwitansi (*Void*):** Khusus Ketua & Bendahara Yayasan dengan alasan wajib; otomatis memulihkan sisa tagihan dan mencatat jurnal pembalik di BKU (terblokir bila periode sudah ditutup buku).

### 6. Pengajuan Belanja & Persetujuan Berjenjang
* Mendukung **Pengajuan Dana Berjenjang** (*Submitted* &rarr; Approval Kepsek Level 1 &rarr; Approval Bendahara Level 2 dengan penyesuaian nominal &rarr; Pencairan Kas yang memotong saldo & mencatat BKU) maupun **Pengeluaran Langsung**.
* Dilengkapi fitur penolakan (*Reject*) beserta alasan dan *Approval Trail* di setiap tahap.

### 7. Laporan Keuangan & Konsolidasi
* **Buku Kas Umum (BKU):** Catatan kronologis Debet & Kredit, Saldo Awal, Total Masuk/Keluar, Saldo Akhir, dan saldo berjalan; filter per Unit, Akun Kas/Bank, dan tanggal; Export **Excel (`.xlsx`)** dan **PDF** berkop Yayasan.
* **Surplus / Defisit (Laba Rugi):** Rekap pendapatan per komponen biaya vs biaya operasional dalam periode tertentu (Konsolidasi maupun per Unit); Export **Excel** dan **PDF** berkop Yayasan.
* **Dashboard & Rekap Tunggakan SPP (*Arrears*):** Ringkasan total piutang, jumlah tagihan & siswa menunggak berpaginasi, filter Unit/Kelas/pencarian, tombol **Kirim Pengingat WhatsApp** otomatis ke wali murid, pintasan bayar, serta Export **Excel (`.xlsx`)**.

### 8. Tutup Buku Bulanan & Audit Trail
* **Tutup Buku (*Closing Lock*):** Mengunci periode bulan/tahun per Unit atau Konsolidasi sehingga transaksi di periode *Closed* tidak bisa ditambah, diubah, maupun di-*void*. Hanya Bendahara/Ketua Yayasan yang dapat melakukan *Re-open* dengan justifikasi wajib.
* **Audit Trail:** Merekam perubahan penting pada tagihan, pembayaran, belanja, tutup buku, pengguna, dan pengaturan aplikasi (*SoftDeletes* / *Zero Hard Delete* di seluruh tabel keuangan).

### 9. Pengaturan Aplikasi (`/settings`)
*(Khusus Ketua Yayasan & Bendahara Yayasan)*
* **Identitas, Alamat & Logo:** Ubah nama aplikasi, tagline, tahun ajaran, nama Yayasan, alamat kop & invoice, telepon/email, unggah **Logo Aplikasi**, **Favicon**, dan **Logo Kop Yayasan** (termasuk sinkronisasi alamat ke unit sekolah).
* **Invoice, Kwitansi & Kode:** Atur prefix penomoran (`INV`/`KW`), tanggal jatuh tempo default, jabatan penandatangan, catatan kaki PDF, toggle logo pada PDF, serta mode kode transaksi ringkas vs lengkap beserta pratinjau kop surat langsung.
* **Tipografi & Tema:** Pilih jenis huruf (*Plus Jakarta Sans, Inter, Poppins, Nunito, Roboto,* atau *System UI*), ukuran huruf (*13px–16px*), kerapatan tabel (*Nyaman/Padat*), dan tema warna sidebar/banner.
* **Unit Sekolah & Akun Pengguna:** Kelola data unit/Kepsek serta tambah, edit, reset password, atau nonaktifkan akun admin dengan proteksi terhadap akun aktif sendiri dan Superadmin terakhir.

### 10. Migrasi Database Historis
Command `php artisan smartkas:import-legacy` mengimpor data historis `pesonaas_db_arsi` secara utuh (5 tahun ajaran, kelas, 533 siswa beserta wali & status arsip, 102 komponen biaya, rekening BSI `7206858502`, 13.324 tagihan, dan 5.193 pembayaran lunas dengan rekonsiliasi saldo 0 selisih).

---

## 👥 Pengelola & Pejabat Keuangan Terdaftar

| Peran / Jabatan | Nama Pejabat & Pengelola | Email Login | Cakupan Otoritas |
| :--- | :--- | :--- | :--- |
| **Ketua Yayasan** | **Sughesti Wulandari, S.Pd** | `sughesti@sitrobbani.sch.id` | Superadmin (Seluruh Unit & Pengaturan) |
| **Bendahara Yayasan** | **Dessy Aprianty, S.E.** | `dessy@sitrobbani.sch.id` | Otorisator Kas, Pencairan, Tutup Buku & Pengaturan |
| **Admin Yayasan** | **Anis Monica, S.H.** | `anis@sitrobbani.sch.id` | Pusat Pembayaran Siswa Seluruh Unit |
| **Admin TKIT** | **Rojanah, S.E.** | `tk@sitrobbani.sch.id` | Kasir & Tagihan KB/TKIT Robbani |
| **Admin SDIT** | **Risma, S.Pd** | `sd@sitrobbani.sch.id` | Kasir & Tagihan SDIT Robbani |
| **Admin SMPIT** | **Adelia Jesika, S.Pd** | `smp@sitrobbani.sch.id` | Kasir & Tagihan SMPIT Robbani |
| **Admin SMAIT** | **Admin Keuangan SMAIT Robbani** | `sma@sitrobbani.sch.id` | Kasir & Tagihan SMAIT Robbani |
| **Kepsek TKIT** | **Ani** | `ani@sitrobbani.sch.id` | Persetujuan Tahap 1 KB/TKIT Robbani |
| **Kepsek SDIT** | **Nur** | `nur@sitrobbani.sch.id` | Persetujuan Tahap 1 SDIT Robbani |
| **Kepsek SMPIT** | **Tia** | `tia@sitrobbani.sch.id` | Persetujuan Tahap 1 SMPIT Robbani |
| **Kepsek SMAIT** | **Kepala Sekolah SMAIT Robbani** | `kepsek.sma@sitrobbani.sch.id` | Persetujuan Tahap 1 SMAIT Robbani |

*Kata Sandi Default Awal:* `password123` *(Segera ubah di menu Pengaturan Aplikasi pada lingkungan produksi).*

---

## 🚀 Panduan Instalasi Lokal (Laravel Herd / CLI)

```bash
git clone https://github.com/septaryanhidayat/SmartKas.git
cd SmartKas

composer install
npm install
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi MySQL lokal di `.env` (`DB_DATABASE=keuangan_yayasan`), lalu jalankan migrasi, build asset, dan server:

```bash
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Untuk mengimpor database historis dari staging `pesonaas_db_arsi`:
```bash
php artisan smartkas:import-legacy --fresh
```

---

## 🌐 Catatan Deployment cPanel
* **Document Root:** Arahkan domain/subdomain ke direktori `public/` repository (misal `/home/<user>/repositories/SmartKas/public`) agar folder utama `public_html` tidak terganggu dan file sensitif tetap terlindungi.
* **Versi PHP Server:** Aplikasi **mewajibkan PHP >= 8.4.1**. Jika default domain cPanel lebih rendah sementara binary `ea-php84` atau `alt-php84` tersedia di server, aktifkan handler PHP 8.4 yang sesuai di [public/.htaccess](public/.htaccess) (dan/atau [.htaccess](.htaccess)).
* **Environment & Asset:** Gunakan [.env.production.example](.env.production.example) sebagai acuan membuat `.env` produksi di server (jangan pernah commit `.env` atau file `.sql` ke GitHub). Asset terkompilasi pada `public/build/` sudah disertakan di repositori agar server tanpa Node.js tetap dapat langsung berjalan.
* **Izin Direktori & Cache:** Pastikan direktori repository dapat dilalui web server (`chmod 755`) serta `storage/`, `bootstrap/cache/`, dan `public/uploads/` bersifat writable (`chmod -R 775`), lalu jalankan `php artisan optimize:clear && php artisan optimize`.

---

## 🧪 Pengujian Otomatis & Linter

```bash
# Menjalankan seluruh test suite (Pest: 35 tests, 184 assertions)
php artisan test

# Memeriksa standar gaya kode (Laravel Pint)
./vendor/bin/pint --test
```

---

## ⚙️ Perintah Artisan Khusus

```bash
# Generate tagihan bulanan SPP rutin seluruh unit
php artisan billing:generate-monthly

# Generate tagihan untuk unit tertentu pada bulan/tahun spesifik
php artisan billing:generate-monthly --unit=2 --month=7 --year=2026

# Impor data historis dari database legacy MySQL
php artisan smartkas:import-legacy --fresh
```

---
&copy; 2026 SmartKas — Yayasan Generasi Robbani Sumatera Selatan. All rights reserved.
