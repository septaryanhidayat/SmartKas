# Konteks Proyek: Sistem Keuangan Terpusat Yayasan SIT

Dokumen ini adalah panduan utama (*always-on context*) untuk Agent di **Antigravity IDE**. Bacalah seluruh aturan dan referensi dokumen di bawah ini sebelum merancang atau menulis kode.

## 1. Gambaran Umum Proyek
Aplikasi web manajemen keuangan terpusat untuk Yayasan Pendidikan Islam Terpadu yang menaungi **4 unit sekolah**:
1. **KB/TKIT**
2. **SDIT**
3. **SMPIT**
4. **SMAIT**

Fokus utama aplikasi adalah akurasi pencatatan kas, manajemen tagihan & pembayaran siswa (termasuk cicilan), persetujuan pengeluaran berjenjang, pelaporan konsolidasi Yayasan vs per Unit, serta keamanan audit (*Audit Trail* & *Tutup Buku*).

## 2. Tech Stack & Lingkungan Pengembangan
* **Local Environment:** Laravel Herd (PHP 8.4, Nginx, local domain `*.test`, misal `keuangan-yayasan.test`)
* **Framework:** Laravel 13.x
* **Bahasa:** PHP 8.4 (gunakan fitur modern: *Property Hooks*, *Asymmetric Visibility*, *Backed Enums*, *Strict Types*)
* **Database:** MySQL 8.x (Single Database dengan arsitektur scoping `unit_id`)
* **Frontend:** Blade + Livewire / Alpine.js + Tailwind CSS (atau sesuai preferensi UI yang disepakati di fase setup)
* **PDF & Excel:** `barryvdh/laravel-dompdf` (e-Kwitansi & Laporan PDF), `maatwebsite/excel` (Export Laporan)
* **RBAC:** `spatie/laravel-permission`
* **Audit Trail:** `spatie/laravel-activitylog`

## 3. Perintah Lingkungan Laravel Herd
Saat menjalankan perintah CLI di terminal Antigravity, gunakan binary dari Laravel Herd:
* Menjalankan Artisan: `php artisan <command>`
* Menjalankan Test (Pest/PHPUnit): `php artisan test`
* Menjalankan Pint (Code Formatter): `./vendor/bin/pint`
* Migrasi & Seeding: `php artisan migrate:fresh --seed`

## 4. Referensi Dokumen Proyek
Gunakan file-file berikut sebagai *single source of truth* saat mengerjakan fitur:
* Kebutuhan Produk & Alur Bisnis: @docs/PRD.md
* Skema Database & Relasi MySQL: @docs/DATABASE_SCHEMA.md
* Arsitektur Aplikasi & Setup Herd: @docs/ARCHITECTURE.md
* Rencana Pengerjaan Bertahap: @docs/ROADMAP.md

## 5. Prinsip Wajib (Non-Negotiable)
1. **Integritas Finansial:** Setiap mutasi uang (pembayaran, pencairan dana, kas kecil) **WAJIB** dibungkus `DB::transaction()`.
2. **Zero Hard Delete:** Semua tabel transaksi dan master data keuangan **WAJIB** menggunakan `SoftDeletes`.
3. **Data Isolation:** Admin Keuangan Unit **TIDAK BOLEH** bisa melihat, mengubah, atau menginput data di luar `unit_id` miliknya.
4. **Tutup Buku (Closing Lock):** Transaksi pada bulan/tahun yang sudah berstatus *closed* **TIDAK BOLEH** bisa ditambah, diubah, atau dihapus oleh role manapun (kecuali proses *re-open* resmi oleh Bendahara Yayasan dengan catatan audit).
5. **Verifikasi Sebelum Klaim Selesai:** Selalu jalankan `php artisan test` dan `./vendor/bin/pint --test` setelah menyelesaikan suatu fitur.