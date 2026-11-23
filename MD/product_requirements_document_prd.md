# Product Requirements Document (PRD)
## Sistem Keuangan Terpusat Yayasan Pendidikan Islam Terpadu

### 1. Tujuan Produk
Membangun sistem informasi keuangan terpusat untuk membantu **Bendahara Yayasan** dan **Admin Keuangan Unit** mengelola pemasukan, pengeluaran, tunggakan, serta pelaporan keuangan secara akurat dan transparan untuk 4 unit sekolah: **KB/TKIT, SDIT, SMPIT, dan SMAIT**.

---

### 2. Peran Pengguna (Roles & Permissions)

| Role | Cakupan Data | Hak Akses Utama |
| :--- | :--- | :--- |
| **Superadmin / Ketua Yayasan** | Seluruh Unit (Konsolidasi) | *Read-only* tingkat tinggi: Dashboard eksekutif, Laporan Laba/Rugi konsolidasi & per unit, Buku Kas, Audit Log. |
| **Bendahara Yayasan** | Seluruh Unit (Konsolidasi) | Kelola master akun kas/bank, persetujuan akhir pengajuan dana, catat pengeluaran yayasan, *Tutup Buku* (Closing), lihat seluruh laporan & audit log. |
| **Kepala Sekolah Unit** | Hanya Unit Miliknya | Menyetujui tahap 1 (*Approval Level 1*) atas pengajuan dana kegiatan/operasional dari unitnya, melihat ringkasan tunggakan & anggaran unitnya. |
| **Admin Keuangan Unit** | Hanya Unit Miliknya | Kelola data siswa & kelas unitnya, generate & terima pembayaran tagihan siswa, cetak/kirim e-Kwitansi WA, ajukan dana operasional, kelola *Petty Cash* unit. |

---

### 3. Spesifikasi Fitur per Modul

#### Modul 1: Master Data (Pengaturan Dasar)
1. **Manajemen Unit Sekolah:** Data 4 unit (KB/TKIT, SDIT, SMPIT, SMAIT) lengkap dengan kode unit, alamat, nama kepala sekolah, dan kop kwitansi.
2. **Manajemen Tahun Ajaran:** Pengaturan tahun ajaran aktif (misal: `2026/2027`), mendukung perbedaan tarif SPP antar angkatan/tahun ajaran.
3. **Manajemen Siswa & Kelas:** Data profil siswa, NIS/NISN, nomor WhatsApp orang tua, kelas saat ini, dan status (`active`, `graduated`, `transferred`, `on_leave`). Siswa berstatus selain `active` otomatis dikecualikan dari *auto-generate* tagihan bulanan.
4. **Master Komponen Biaya:** Klasifikasi jenis tagihan per unit:
   * **Rutin/Bulanan:** SPP, Katering, Antar-Jemput, Ekstrakurikuler.
   * **Insidentil/Tahunan:** Uang Pangkal/Gedung, Daftar Ulang, Seragam, Buku Paket, Kegiatan/Outing.
5. **Master Diskon & Beasiswa:** Aturan potongan harga (persentase atau nominal tetap) untuk kategori khusus (Anak Yatim, Anak Guru, Prestasi, Saudara Kandung) yang bisa ditempelkan ke siswa dan otomatis memotong tagihan saat *billing*.
6. **Master Akun Kas & Bank:** Daftar rekening bank Yayasan, rekening penampung unit, dan brankas kas tunai / *petty cash* per unit.

#### Modul 2: Manajemen Pemasukan & Tagihan (Account Receivable)
1. **Auto-Generate Tagihan Bulanan (Billing):**
   * *Scheduled job* otomatis setiap tanggal 1 pukul 00:05 (plus tombol manual untuk admin) untuk membuat tagihan rutin seluruh siswa aktif sesuai tarif unit dan potongan diskonnya.
   * Mencegah duplikasi tagihan untuk siswa, komponen, bulan, dan tahun yang sama.
2. **Generate Tagihan Insidentil:** Pembuatan tagihan massal (per kelas/unit) atau individu (misal: Uang Pangkal siswa baru).
3. **Penerimaan Pembayaran (Kasir / Multi-Payment):**
   * Mendukung metode pembayaran: Tunai, Transfer Bank, QRIS/Virtual Account.
   * Mendukung **pembayaran penuh** maupun **pembayaran parsial (cicilan)** (terutama untuk Uang Pangkal/Daftar Ulang).
   * Mendukung pembayaran beberapa tagihan sekaligus dalam satu transaksi kwitansi (*cart checkout*).
4. **Dashboard Tunggakan (Arrears):**
   * Tampilan *real-time* daftar siswa menunggak, bisa difilter berdasarkan Unit, Kelas, Komponen Biaya, dan jumlah bulan menunggak.
   * Fitur kirim pengingat tagihan (*billing reminder*) via WhatsApp.
5. **e-Kwitansi PDF & Notifikasi WhatsApp:**
   * Otomatis membuat file PDF kwitansi resmi berkop unit sekolah.
   * Integrasi *webhook/API* WhatsApp (seperti Fonnte/Wablas/WA Cloud API) melalui *Laravel Queue* untuk mengirim pesan konfirmasi pembayaran & tautan e-Kwitansi ke nomor orang tua.

#### Modul 3: Manajemen Pengeluaran & Anggaran (Account Payable)
1. **Pengajuan Dana (Cash Advance / RAB Kegiatan):**
   * Admin Unit mengajukan permohonan dana disertai rincian item, nominal, dan lampiran pendukung.
2. **Approval Berjenjang:**
   * Alur status: `draft` $\rightarrow$ `submitted` $\rightarrow$ `approved_principal` (Kepsek) $\rightarrow$ `approved_treasurer` (Bendahara) $\rightarrow$ `disbursed` (Cair & tercatat di Buku Kas) atau `rejected` (dengan alasan wajib).
3. **Pencatatan Biaya Operasional:**
   * Pencatatan langsung pengeluaran rutin (listrik, air, internet, ATK, pemeliharaan, gaji guru/karyawan) dengan unggah bukti struk/nota.
4. **Manajemen Petty Cash (Kas Kecil):**
   * Pencatatan pengisian kas kecil (*top-up/imprest*) dari Yayasan ke unit dan pelaporan pengeluaran harian kas kecil beserta bukti nota untuk rekonsiliasi saldo.

#### Modul 4: Pelaporan & Akuntansi
1. **Buku Kas Umum (Mutasi Kas):** Menampilkan arus masuk (debet), keluar (kredit), dan saldo berjalan harian per akun Kas/Bank.
2. **Laporan Surplus/Defisit (Laba Rugi):** Perbandingan total pendapatan vs total pengeluaran per periode. Wajib memiliki filter **Per Unit Sekolah** dan **Konsolidasi Total Yayasan**.
3. **Laporan Rekap Penerimaan per Komponen:** Rincian total uang masuk dikelompokkan berdasarkan pos biaya (SPP, Seragam, Uang Pangkal, dll.).
4. **Export Data:** Seluruh laporan wajib dapat diekspor ke format **Excel (`.xlsx`)** dan **PDF**.

#### Modul 5: Keamanan, Audit & Tutup Buku
1. **Audit Trail:** Merekam setiap aksi krusial (create, update, void/delete, approval, login) lengkap dengan `user_id`, alamat IP, nilai lama (*old values*), nilai baru (*new values*), dan *timestamp*.
2. **Pembatalan Transaksi (Void):** Transaksi pembayaran yang salah input tidak dihapus permanen, melainkan di-*void* (disertai alasan wajib) sehingga otomatis membuat jurnal pembalik di Buku Kas dan mengembalikan status tagihan.
3. **Tutup Buku (Closing Period):** Penguncian data keuangan per bulan/tahun oleh Bendahara Yayasan agar transaksi pada periode tersebut bersifat *immutable*.