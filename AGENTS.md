# Konteks Proyek: Sistem Keuangan Terpusat Yayasan Generasi Robbani Sumatera Selatan

Dokumen ini adalah panduan utama (*always-on context*) untuk Agent di **Antigravity IDE**.

## 1. Gambaran Umum Proyek
Aplikasi web manajemen keuangan terpusat untuk **Yayasan Generasi Robbani Sumatera Selatan** yang menaungi **4 unit sekolah Islam Terpadu**:
1. **KB/TKIT Robbani** (Admin: Rojanah, S.E.)
2. **SDIT Robbani** (Admin: Risma, S.Pd)
3. **SMPIT Robbani** (Admin: Adelia Jesika, S.Pd)
4. **SMAIT Robbani** (Admin: Admin Keuangan SMAIT Robbani)

Pusat Yayasan:
* **Ketua Yayasan:** Sughesti Wulandari, S.Pd (Superadmin)
* **Bendahara Yayasan:** Dessy Aprianty, S.E. (Otorisator Keuangan Terpusat & Pencairan Dana)
* **Admin Yayasan (Pusat Pembayaran Siswa):** Anis Monica, S.H. (Kasir Terpusat 4 Unit)

Fokus utama aplikasi adalah akurasi pencatatan kas, manajemen tagihan & pembayaran siswa (termasuk cicilan), persetujuan pengeluaran berjenjang, pelaporan konsolidasi Yayasan vs per Unit, serta keamanan audit (*Audit Trail* & *Tutup Buku*).

## 2. Tech Stack & Lingkungan Pengembangan
* **Local Environment:** Laravel Herd (PHP 8.4, MySQL 8.0)
* **Framework:** Laravel 13.x
* **Bahasa:** PHP 8.4 (*Property Hooks*, *Asymmetric Visibility*, *Backed Enums*, *Strict Types*)
* **Database:** MySQL 8.x (Single Database dengan arsitektur scoping `unit_id`)
* **Frontend:** Blade + Tailwind CSS + Alpine.js
* **PDF & Excel:** `barryvdh/laravel-dompdf` (e-Kwitansi & Laporan PDF), `maatwebsite/excel` (Export Laporan)
* **RBAC:** `spatie/laravel-permission`
* **Audit Trail:** `spatie/laravel-activitylog`

## 3. Perintah Lingkungan Laravel Herd
* Menjalankan Artisan: `php artisan <command>`
* Menjalankan Test: `php artisan test`
* Menjalankan Pint: `./vendor/bin/pint`
* Migrasi & Seeding: `php artisan migrate:fresh --seed`

## 4. Prinsip Wajib (Non-Negotiable)
1. **Integritas Finansial:** Setiap mutasi uang (pembayaran, pencairan dana, kas kecil) **WAJIB** dibungkus `DB::transaction()`.
2. **Zero Hard Delete:** Semua tabel transaksi dan master data keuangan **WAJIB** menggunakan `SoftDeletes`.
3. **Data Isolation:** Admin Keuangan Unit **TIDAK BOLEH** bisa melihat, mengubah, atau menginput data di luar `unit_id` miliknya.
4. **Tutup Buku (Closing Lock):** Transaksi pada bulan/tahun yang sudah berstatus *closed* **TIDAK BOLEH** bisa ditambah, diubah, atau dihapus oleh role manapun (kecuali proses *re-open* resmi oleh Bendahara Yayasan dengan catatan audit).
5. **Verifikasi Sebelum Klaim Selesai:** Selalu jalankan `php artisan test` dan `./vendor/bin/pint --test` setelah menyelesaikan suatu fitur.
