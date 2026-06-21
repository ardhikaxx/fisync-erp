# DESIGN-ERP.md
## FINSYNC ERP — Integrated Finance & Accounting Management System
### Blueprint Desain UI/UX

---

## 1. FILOSOFI DESAIN

FINSYNC ERP adalah sistem keuangan, bukan aplikasi konsumer. Setiap keputusan desain harus mendukung tiga nilai: **presisi (precision)**, **kepercayaan (trust)**, dan **kejelasan dalam kepadatan data (clarity in density)**. Pengguna sistem ini (Accountant, Finance Manager, Direktur) menghabiskan berjam-jam membaca tabel angka, laporan, dan dashboard — desain tidak boleh "ramai" atau dekoratif berlebihan, tapi juga tidak boleh terasa kaku/membosankan seperti aplikasi akuntansi legacy era 2005.

**Identitas visual:** *"Korporat Modern dengan Sentuhan Fintech"* — bersih, tegas, dengan penggunaan warna yang disiplin untuk membedakan status (debit/kredit, approved/pending/rejected, positif/negatif) secara instan tanpa harus membaca teks.

---

## 2. PALET WARNA

### 2.1 Warna Primer — Teal (Finance Trust Tone)
```css
--fs-primary:         #0D7377;   /* Teal Tua - header, navbar, tombol utama */
--fs-primary-dark:    #0A5C5F;   /* Hover state, active sidebar */
--fs-primary-light:   #14A8AD;   /* Aksen, link, highlight */
--fs-primary-soft:    #E6F6F6;   /* Background card terpilih, badge soft */
```

### 2.2 Warna Sekunder — Navy (Otoritas & Stabilitas)
```css
--fs-secondary:       #1B2A4A;   /* Sidebar background, teks heading penting */
--fs-secondary-light: #2E4170;   /* Hover sidebar item */
```

### 2.3 Warna Status Semantik (Krusial untuk Sistem Finance)
```css
--fs-success:         #1E8E5A;   /* Approved, Lunas, Posted, Debit cocok */
--fs-success-bg:      #E8F6EF;
--fs-danger:          #D32F4E;   /* Rejected, Overdue, Discrepancy, Unbalance */
--fs-danger-bg:       #FCEAEE;
--fs-warning:         #E89A1C;   /* Pending Approval, Mendekati limit budget */
--fs-warning-bg:      #FDF3E2;
--fs-info:            #2C7EC9;   /* Draft, Informasi netral */
--fs-info-bg:         #E8F2FB;
```

### 2.4 Warna Akuntansi Spesifik (Konvensi Universal)
```css
--fs-debit-color:     #1B6FA8;   /* Biru - nilai Debit di tabel jurnal */
--fs-credit-color:    #B8651E;   /* Oranye burnt - nilai Kredit di tabel jurnal */
```
> Konvensi ini dipakai konsisten di seluruh tabel jurnal, buku besar, dan neraca saldo agar mata pengguna terlatih membedakan kolom tanpa membaca header berulang kali.

### 2.5 Warna Netral
```css
--fs-bg-body:         #F4F6F9;   /* Background utama aplikasi */
--fs-bg-card:         #FFFFFF;
--fs-border:          #E2E6ED;
--fs-text-primary:    #1A1D29;
--fs-text-secondary:  #6B7280;
--fs-text-muted:      #9CA3AF;
```

---

## 3. TIPOGRAFI

```css
/* Google Fonts CDN */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap');

--fs-font-primary: 'Plus Jakarta Sans', -apple-system, sans-serif;   /* Seluruh teks UI */
--fs-font-mono:    'JetBrains Mono', 'Courier New', monospace;       /* WAJIB untuk semua nominal angka/uang */
```

### Aturan Penggunaan
- **Plus Jakarta Sans** → heading, label, teks navigasi, paragraf, tombol.
- **JetBrains Mono** → SEMUA angka nominal (Rupiah, persentase, nomor invoice, nomor akun, kode transaksi). Font monospace membuat kolom angka di tabel jurnal/neraca rapi sejajar secara visual — krusial untuk laporan keuangan agar mudah di-scan vertikal.

### Skala
```css
--fs-text-xs:    0.75rem;   /* 12px - label kecil, caption tabel */
--fs-text-sm:    0.8125rem; /* 13px - body tabel data */
--fs-text-base:  0.875rem;  /* 14px - body default */
--fs-text-lg:    1rem;      /* 16px - sub-heading */
--fs-text-xl:    1.25rem;   /* 20px - card title */
--fs-text-2xl:   1.75rem;   /* 28px - page title */
--fs-text-3xl:   2.25rem;   /* 36px - angka besar di dashboard widget */

--fs-weight-regular: 400;
--fs-weight-medium:  500;
--fs-weight-semibold: 600;
--fs-weight-bold:    700;
--fs-weight-extrabold: 800;  /* khusus angka dashboard utama */
```

---

## 4. LAYOUT STRUKTUR APLIKASI

### 4.1 Struktur Umum
```
┌─────────────────────────────────────────────────────┐
│  TOPBAR (fixed)  - logo kecil, search global,        │
│  notifikasi approval, profil user, branch switcher    │
├──────────┬──────────────────────────────────────────┤
│          │  BREADCRUMB + PAGE HEADER + ACTION BUTTONS │
│ SIDEBAR  ├──────────────────────────────────────────┤
│ (fixed,  │                                            │
│ collapsible)│         KONTEN UTAMA                    │
│          │   (card-card, tabel, form, dashboard)      │
│          │                                            │
└──────────┴──────────────────────────────────────────┘
```

### 4.2 Sidebar
- Background: `var(--fs-secondary)` (Navy gelap) — kontras tegas dengan area konten putih/abu terang.
- Lebar: 260px (expanded), 72px (collapsed, hanya icon).
- Grouping menu sesuai modul: **Dashboard** → **Master Data** → **Kas & Bank** → **Piutang (AR)** → **Hutang (AP)** → **Buku Besar** → **Anggaran** → **Aset Tetap** → **Laporan** → **Approval** → **Audit Trail** → **Pengaturan**.
- Menu aktif: background `var(--fs-primary)`, border-left 3px solid `var(--fs-primary-light)`.
- Setiap item menu pakai Font Awesome icon + label, badge angka merah kecil untuk notifikasi pending approval.

### 4.3 Topbar
- Background putih, shadow tipis (`box-shadow: 0 1px 3px rgba(0,0,0,0.06)`).
- **Branch Switcher** dropdown (untuk role multi-cabang) di kiri dekat logo.
- **Global Search** (cari nomor invoice/transaksi/customer) di tengah.
- **Lonceng notifikasi** dengan badge merah — daftar approval pending real-time.
- **Avatar + nama + role** di kanan, dropdown berisi "Profil", "Pengaturan", dan "Logout" (logout WAJIB trigger SweetAlert2 konfirmasi).

### 4.4 Responsivitas
- Breakpoint Bootstrap standar (`sm`, `md`, `lg`, `xl`).
- Sidebar otomatis collapse jadi off-canvas di bawah `lg` (<992px).
- Tabel data lebar (jurnal, neraca) menggunakan wrapper `table-responsive` + opsi "freeze kolom pertama" via CSS `position: sticky` pada kolom kode akun.

---

## 5. KOMPONEN UI UTAMA

### 5.1 Card Dashboard (KPI Widget)
```html
<div class="fs-kpi-card">
  <div class="fs-kpi-icon bg-primary-soft"><i class="fa-solid fa-wallet"></i></div>
  <div class="fs-kpi-content">
    <span class="fs-kpi-label">Total Kas & Bank</span>
    <span class="fs-kpi-value">Rp 1.245.800.000</span>
    <span class="fs-kpi-trend text-success"><i class="fa-solid fa-arrow-up"></i> 12.4% bulan ini</span>
  </div>
</div>
```
CSS kunci:
```css
.fs-kpi-card {
  background: var(--fs-bg-card);
  border-radius: 14px;
  border: 1px solid var(--fs-border);
  padding: 1.25rem;
  display: flex;
  gap: 1rem;
  box-shadow: 0 2px 8px rgba(13,115,119,0.04);
  transition: box-shadow .2s ease, transform .2s ease;
}
.fs-kpi-card:hover { box-shadow: 0 6px 16px rgba(13,115,119,0.10); transform: translateY(-2px); }
.fs-kpi-value { font-family: var(--fs-font-mono); font-size: var(--fs-text-2xl); font-weight: var(--fs-weight-bold); color: var(--fs-text-primary); }
```

### 5.2 Tabel Data Finansial (Jurnal, GL, Neraca)
- Header tabel: background `var(--fs-bg-body)`, teks uppercase kecil, `letter-spacing: 0.03em`, sticky di atas saat scroll.
- Baris zebra subtle: `nth-child(even) { background: #FAFBFC; }`.
- Kolom Debit → teks `var(--fs-debit-color)`, rata kanan, font mono.
- Kolom Kredit → teks `var(--fs-credit-color)`, rata kanan, font mono.
- Baris total/subtotal: `font-weight: 700`, border-top `2px solid var(--fs-primary)`.
- Hover baris: `background: var(--fs-primary-soft)`.

### 5.3 Badge Status
```css
.fs-badge { padding: 0.3rem 0.7rem; border-radius: 20px; font-size: var(--fs-text-xs); font-weight: 600; }
.fs-badge-success { background: var(--fs-success-bg); color: var(--fs-success); }
.fs-badge-danger  { background: var(--fs-danger-bg);  color: var(--fs-danger); }
.fs-badge-warning { background: var(--fs-warning-bg); color: var(--fs-warning); }
.fs-badge-info    { background: var(--fs-info-bg);    color: var(--fs-info); }
```
Mapping status → badge:
| Status | Badge |
|---|---|
| Posted / Approved / Lunas / Matched | `fs-badge-success` |
| Pending / Draft / Belum Lunas Sebagian | `fs-badge-warning` |
| Rejected / Overdue / Discrepancy / Unbalance | `fs-badge-danger` |
| Draft awal / Informasi | `fs-badge-info` |

### 5.4 Form Input
- Gunakan `form-floating` Bootstrap 5 untuk input standar agar hemat ruang vertikal.
- Input nominal uang: prefix "Rp" statis di kiri (`input-group-text`), font mono di dalam input, auto-format ribuan via JS (tanpa library tambahan — vanilla JS formatter).
- Dropdown akun (Chart of Accounts) menggunakan **Select2** *atau* native dengan indentasi visual `padding-left` sesuai `level` hierarki akun agar struktur pohon terlihat di dropdown.
- Validasi error: border merah `var(--fs-danger)` + teks kecil di bawah field, bukan alert popup (alert popup hanya untuk hasil submit form, bukan validasi inline).

### 5.5 Tombol Aksi
```css
.btn-fs-primary   { background: var(--fs-primary); border: none; color: #fff; }
.btn-fs-primary:hover { background: var(--fs-primary-dark); }
.btn-fs-outline   { background: transparent; border: 1.5px solid var(--fs-primary); color: var(--fs-primary); }
.btn-fs-danger-soft { background: var(--fs-danger-bg); color: var(--fs-danger); border: none; }
```
- Tombol "Posting Jurnal" / "Approve" / "Tutup Periode" → selalu `btn-fs-primary`, ditempatkan kanan atas konten, ikon Font Awesome relevan (`fa-check-double`, `fa-lock`).
- Tombol destruktif (Hapus draft, Reject, Reverse) → `btn-fs-danger-soft`, WAJIB trigger SweetAlert2 konfirmasi sebelum eksekusi.

---

## 6. SWEETALERT2 — STANDAR WRAPPER

Karena seluruh interaksi kritikal (hapus, logout, approve, reject, posting jurnal, tutup periode) memakai SweetAlert2, buat wrapper JS terpusat di `public/js/fs-alert.js` agar konsisten di seluruh sistem:

```javascript
// public/js/fs-alert.js

const FSAlert = {
  sukses: (pesan = 'Data berhasil disimpan.') => {
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: pesan,
      confirmButtonColor: '#0D7377',
      timer: 2200,
      timerProgressBar: true,
      customClass: { popup: 'fs-swal-popup' }
    });
  },

  gagal: (pesan = 'Terjadi kesalahan, silakan coba lagi.') => {
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: pesan,
      confirmButtonColor: '#D32F4E',
      customClass: { popup: 'fs-swal-popup' }
    });
  },

  konfirmasiHapus: (callback, namaItem = 'data ini') => {
    Swal.fire({
      icon: 'warning',
      title: 'Hapus Data?',
      text: `Anda yakin ingin menghapus ${namaItem}? Tindakan ini tidak dapat dibatalkan.`,
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#D32F4E',
      cancelButtonColor: '#9CA3AF',
      reverseButtons: true,
      customClass: { popup: 'fs-swal-popup' }
    }).then((result) => { if (result.isConfirmed) callback(); });
  },

  konfirmasiLogout: (callback) => {
    Swal.fire({
      icon: 'question',
      title: 'Keluar dari Sistem?',
      text: 'Sesi Anda akan diakhiri.',
      showCancelButton: true,
      confirmButtonText: 'Ya, Keluar',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#0D7377',
      cancelButtonColor: '#9CA3AF',
      reverseButtons: true,
      customClass: { popup: 'fs-swal-popup' }
    }).then((result) => { if (result.isConfirmed) callback(); });
  },

  konfirmasiPosting: (callback, pesan = 'Jurnal akan diposting permanen ke Buku Besar dan tidak dapat dihapus.') => {
    Swal.fire({
      icon: 'warning',
      title: 'Posting Transaksi?',
      text: pesan,
      showCancelButton: true,
      confirmButtonText: 'Ya, Posting',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#0D7377',
      cancelButtonColor: '#9CA3AF',
      reverseButtons: true,
      customClass: { popup: 'fs-swal-popup' }
    }).then((result) => { if (result.isConfirmed) callback(); });
  },

  konfirmasiApproval: (callback, aksi = 'menyetujui') => {
    Swal.fire({
      icon: 'question',
      title: `Yakin ingin ${aksi} transaksi ini?`,
      showCancelButton: true,
      confirmButtonText: 'Ya, Lanjutkan',
      cancelButtonText: 'Batal',
      confirmButtonColor: aksi === 'menolak' ? '#D32F4E' : '#1E8E5A',
      cancelButtonColor: '#9CA3AF',
      reverseButtons: true,
      customClass: { popup: 'fs-swal-popup' }
    }).then((result) => { if (result.isConfirmed) callback(); });
  },

  peringatanBudget: (sisaAnggaran) => {
    Swal.fire({
      icon: 'warning',
      title: 'Melebihi Plafon Anggaran!',
      html: `Transaksi ini melampaui sisa anggaran cost center sebesar <b>Rp ${sisaAnggaran}</b>. Diperlukan persetujuan tambahan.`,
      confirmButtonText: 'Mengerti',
      confirmButtonColor: '#E89A1C',
      customClass: { popup: 'fs-swal-popup' }
    });
  },

  errorUnbalance: () => {
    Swal.fire({
      icon: 'error',
      title: 'Jurnal Tidak Balance',
      text: 'Total Debit harus sama dengan total Kredit sebelum transaksi dapat disimpan.',
      confirmButtonColor: '#D32F4E',
      customClass: { popup: 'fs-swal-popup' }
    });
  }
};
```

```css
/* Override visual SweetAlert2 agar selaras identitas FINSYNC */
.fs-swal-popup { border-radius: 16px !important; font-family: var(--fs-font-primary) !important; }
.fs-swal-popup .swal2-title { font-weight: 700; }
```

**Aturan pemakaian:** setiap tombol Hapus/Logout/Posting/Approve/Reject di seluruh Blade view WAJIB memanggil fungsi `FSAlert` terkait — tidak ada `confirm()` atau `alert()` native JavaScript di production.

---

## 7. HALAMAN-HALAMAN KUNCI (WIREFRAME KONSEPTUAL)

### 7.1 Dashboard Utama
- Baris 1: 4 KPI card (Total Kas & Bank, Piutang Belum Tertagih, Hutang Jatuh Tempo, Laba Bersih Bulan Ini).
- Baris 2: Chart.js line chart "Arus Kas 12 Bulan Terakhir" (kiri, 8 kolom) + donut chart "Komposisi Beban per Kategori" (kanan, 4 kolom).
- Baris 3: Tabel ringkas "5 Invoice Jatuh Tempo Terdekat" + "5 Approval Menunggu Tindakan Anda".
- Filter cabang & rentang tanggal di pojok kanan atas konten (bukan di sidebar).

### 7.2 Halaman Jurnal / Buku Besar
- Tabel dengan kolom: Tanggal | No. Transaksi | Keterangan | Kode Akun | Nama Akun | Cost Center | Debit | Kredit.
- Baris footer sticky menampilkan total Debit & Kredit real-time saat input jurnal manual (form dinamis tambah baris).
- Tombol "Posting" non-aktif (disabled, abu-abu) sampai total Debit = total Kredit, baru aktif berwarna `btn-fs-primary` — indikator visual instan ke pengguna.

### 7.3 Halaman 3-Way Matching (AP)
- Tampilan **3 kolom berdampingan** (PO | GRN | Invoice Supplier) agar pengguna bisa membandingkan visual langsung.
- Baris/item yang cocok: highlight hijau tipis (`fs-success-bg`). Baris yang diskrepansi: highlight merah tipis (`fs-danger-bg`) dengan ikon `fa-triangle-exclamation`.

### 7.4 Halaman Approval Center
- List card per approval pending, bukan tabel — setiap card menampilkan: jenis transaksi, nominal besar (font mono, bold), pemohon, tanggal submit, tombol "Setujui" (hijau) dan "Tolak" (merah outline) berdampingan.
- Riwayat approval level sebelumnya ditampilkan sebagai **stepper/timeline vertikal** kecil di bawah card (icon centang hijau per level yang sudah lewat).

### 7.5 Halaman Laporan Keuangan (Neraca/Laba Rugi)
- Toolbar atas: filter cabang, filter periode (date range picker), tombol "Export Excel" (ikon `fa-file-excel`, hijau) dan "Export PDF" (ikon `fa-file-pdf`, merah) — keduanya `btn-fs-outline` dengan warna ikon sesuai jenis file.
- Layout laporan meniru format akuntansi formal: indentasi berjenjang per sub-klasifikasi akun, garis pemisah tegas sebelum baris total/subtotal, angka negatif ditampilkan dalam kurung merah `(Rp 1.000.000)` bukan minus.

### 7.6 Halaman Audit Trail
- Tabel read-only dengan filter: User, Modul, Jenis Aksi, Rentang Tanggal.
- Klik baris membuka **modal diff viewer** menampilkan `old_values` vs `new_values` berdampingan side-by-side, field yang berubah di-highlight kuning.

---

## 8. ICON MAPPING (FONT AWESOME 6)

| Modul/Aksi | Icon Class |
|---|---|
| Dashboard | `fa-solid fa-gauge-high` |
| Master Data | `fa-solid fa-database` |
| Chart of Accounts | `fa-solid fa-sitemap` |
| Kas & Bank | `fa-solid fa-building-columns` |
| Piutang (AR) | `fa-solid fa-file-invoice-dollar` |
| Hutang (AP) | `fa-solid fa-money-bill-transfer` |
| Buku Besar (GL) | `fa-solid fa-book` |
| Anggaran/Budget | `fa-solid fa-chart-pie` |
| Aset Tetap | `fa-solid fa-warehouse` |
| Laporan | `fa-solid fa-chart-line` |
| Approval | `fa-solid fa-clipboard-check` |
| Audit Trail | `fa-solid fa-shield-halved` |
| Pengaturan | `fa-solid fa-gear` |
| Tambah | `fa-solid fa-plus` |
| Edit | `fa-solid fa-pen` |
| Hapus | `fa-solid fa-trash` |
| Posting | `fa-solid fa-check-double` |
| Reversal | `fa-solid fa-rotate-left` |
| Lock Period | `fa-solid fa-lock` |
| Logout | `fa-solid fa-right-from-bracket` |
| Notifikasi | `fa-solid fa-bell` |
| Multi-cabang | `fa-solid fa-code-branch` |
| Multi-currency | `fa-solid fa-money-bill-wave` |

---

## 9. STACK CDN WAJIB (HEAD LAYOUT BLADE)

```html
<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<!-- Chart.js (untuk dashboard & laporan grafis) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<!-- Custom CSS & JS proyek -->
<link rel="stylesheet" href="{{ asset('css/finsync.css') }}">
<script src="{{ asset('js/fs-alert.js') }}" defer></script>
```

---

## 10. PRINSIP UX TAMBAHAN KHUSUS FINANCE

1. **Tidak ada angka tanpa konteks** — setiap nominal besar di dashboard selalu didampingi label periode/perbandingan (mis. "vs bulan lalu").
2. **Status warna konsisten lintas modul** — hijau selalu berarti "selesai/aman/cocok", merah selalu "bermasalah/butuh perhatian", oranye/kuning selalu "menunggu tindakan". Jangan pernah dibalik di modul manapun.
3. **Aksi destruktif/ireversibel selalu 2 langkah** — klik tombol → SweetAlert2 konfirmasi → baru eksekusi. Tidak ada aksi finansial sekali klik langsung tereksekusi (posting, reverse, tutup periode, approve nominal besar).
4. **Indikator balance real-time** — di setiap form input jurnal manual, total Debit dan Kredit ditampilkan mengambang (sticky footer) dan berubah warna (merah jika belum balance, hijau jika sudah) sehingga staf tidak perlu submit untuk tahu errornya.
5. **Hierarki visual Chart of Accounts** — gunakan indentasi + warna teks lebih pudar untuk akun induk non-postable, dan teks tegas untuk akun anak yang bisa diposting, agar staf tidak salah pilih akun header saat input.
6. **Print-friendly laporan** — seluruh halaman laporan punya CSS `@media print` terpisah agar saat dicetak langsung dari browser, sidebar/topbar otomatis hilang dan hanya konten laporan yang tampil.
