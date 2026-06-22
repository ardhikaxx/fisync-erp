
<h1 align="center">FiSync ERP</h1>

<p align="center">
  Sistem <b>Enterprise Resource Planning (ERP)</b> dan <b>Manajemen Keuangan Akuntansi</b> modern berbasis web. Dibangun menggunakan <b>Laravel 12</b> dengan fokus utama pada kecepatan, keakuratan data (Double-Entry Accounting), serta antarmuka (UI/UX) yang premium dan responsif.
</p>

---

## 🚀 Fitur Utama (Features)

Sistem **FiSync ERP** dilengkapi dengan modul-modul esensial untuk mengelola keuangan bisnis skala menengah ke atas:

### 📊 1. Premium Executive Dashboard
- **Real-time KPI Cards**: Pantau Total Kas & Bank, Piutang Belum Tertagih, Hutang Jatuh Tempo, dan estimasi Laba Bersih secara instan.
- **Grafik Arus Kas (Cash Flow)**: Visualisasi tren pemasukan vs pengeluaran dalam 6 bulan terakhir menggunakan Chart.js.
- **Komposisi Pengeluaran**: *Donut chart* interaktif untuk menganalisis pengeluaran terbesar bisnis Anda.
- **Tracker Invoice Jatuh Tempo**: Daftar interaktif tagihan yang mendekati/lewat batas waktu. Cukup klik untuk langsung diarahkan ke halaman pembayaran.

### 💰 2. Core Accounting (Double-Entry System)
- **Chart of Accounts (COA)**: Manajemen hierarki akun standar akuntansi secara dinamis.
- **Jurnal Umum (General Journal)**: Pencatatan jurnal manual berbasis *double-entry* (Debit/Kredit harus seimbang/balance).
- **Laporan Keuangan Otomatis (Reports)**:
  - **Buku Besar (General Ledger)**: Melacak histori setiap akun secara mendetail.
  - **Laba Rugi (Income Statement)**: Kalkulasi otomatis Pendapatan vs Beban per periode.
  - **Neraca (Balance Sheet)**: Laporan aset, kewajiban, dan ekuitas yang selalu akurat dan seimbang.

### 🤝 3. Accounts Receivable (AR) & Accounts Payable (AP)
- **Manajemen Invoice (Tagihan)**: Buat, cetak, dan pantau tagihan untuk pelanggan (AR).
- **Penerimaan Pembayaran (Receipts)**: Catat pembayaran parsial maupun lunas terhadap suatu invoice pelanggan.
- **Tagihan Supplier (Vendor Invoices)**: Catat hutang ke vendor dan jadwalkan pembayaran (AP).
- **Buku Kontak**: Manajemen data Pelanggan (Customers) dan Pemasok (Suppliers).

### 🏦 4. Cash & Bank Management
- **Kas Keluar & Masuk**: Catat pengeluaran operasional instan dan pemasukan kas secara rapi.
- **Multi-Branch & Currency**: Dukungan transaksi untuk banyak cabang (Pusat/Cabang) secara bersamaan.

### 📥 5. Ekspor Laporan Sekali Klik
- **Export to PDF**: Cetak tabel aktivitas, tabel jurnal, hingga laporan keuangan menjadi PDF berdesain rapi dengan kop dokumen otomatis (menggunakan `barryvdh/laravel-dompdf`).
- **Export to Excel**: Ekspor tabel transaksi ke dalam format *spreadsheet* `.xlsx` yang sudah dikustomisasi ukuran kolom dan ketebalan teksnya (menggunakan `maatwebsite/excel`).

### 🛡️ 6. Keamanan & Audit Trail (Log Aktivitas)
- **Sistem Otentikasi Aman**: Login, deteksi *user* aktif, dan pengubahan kata sandi terenkripsi.
- **Log Aktivitas Pengguna (Audit Trail)**: Merekam setiap jejak aktivitas pengguna (*Siapa*, *Melakukan Apa*, *Kapan*, dan dari *IP Address mana*). Sistem merekam otomatis kejadian Login, Logout, dan perubahan data krusial.
- **Konfirmasi Interaktif (SweetAlert2)**: Cegah salah pencet atau *accidental clicks* saat melakukan *Logout* atau menghapus data berkat validasi pop-up elegan.

---

## 🛠️ Teknologi & Stack

Aplikasi ini dibangun di atas pondasi teknologi modern:
- **Backend Framework**: [Laravel 12.x](https://laravel.com) (PHP 8.2+)
- **Database**: MySQL / MariaDB (via Eloquent ORM)
- **Frontend & Styling**: 
  - Bootstrap 5 (Customized)
  - Custom CSS (`finsync.css` dengan CSS Variables untuk desain premium)
  - Font Awesome 6 (Icons)
- **Data Visualization**: Chart.js
- **Packages/Dependencies**:
  - `maatwebsite/excel` (Ekspor Excel)
  - `barryvdh/laravel-dompdf` (Ekspor PDF)
  - `spatie/laravel-permission` (Manajemen Hak Akses)

---

## 💻 Instalasi Lokal (Development)

Untuk menjalankan proyek FiSync ERP di komputer lokal Anda, ikuti langkah-langkah berikut:

1. **Kloning Repositori**
   ```bash
   git clone https://github.com/ardhikaxx/fisync-erp.git
   cd fisync-erp
   ```

2. **Install Dependensi Composer & NPM**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Konfigurasi Environment (.env)**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Sesuaikan konfigurasi koneksi `DB_` (Database) di dalam file `.env` Anda sesuai dengan pengaturan MySQL lokal Anda.*

4. **Migrasi Database & Seeding (Opsional jika ada seeder)**
   ```bash
   php artisan migrate
   # php artisan db:seed (Jika ada seeder awal)
   ```

5. **Jalankan Development Server**
   ```bash
   php artisan serve
   ```
   *Aplikasi kini bisa diakses melalui `http://127.0.0.1:8000`*

---

## 🎨 Panduan Desain (UI/UX)
Bagi *developer* yang ingin menambahkan tampilan baru, harap berpedoman pada `design-erp.md` dan menggunakan *utility classes* pada `finsync.css`. Beberapa aturan emas:
- Selalu gunakan warna *primary* `--fs-primary` (`#0D7377`) untuk tombol utama.
- Hindari penggunaan *alert* bawaan browser; selalu gunakan `FSAlert` (SweetAlert2) untuk konfirmasi.
- Pastikan *Card* di dashboard dipasang kelas `.fs-custom-scrollbar` jika isinya memanjang ke bawah.

---
**FiSync ERP** - Diciptakan untuk menyederhanakan kompleksitas, tanpa mengorbankan fungsionalitas.
