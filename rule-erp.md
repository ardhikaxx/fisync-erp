# RULE-ERP.md
## FINSYNC ERP — Integrated Finance & Accounting Management System
### Blueprint Arsitektur, Skema Database, dan Business Logic

---

## 1. RINGKASAN SISTEM

**Nama Sistem:** FINSYNC ERP
**Konsep:** ERP Finance Mini — Integrated Finance & Accounting Management System
**Framework:** Laravel 12
**Frontend:** Bootstrap 5 (CDN) + CSS custom tambahan
**Alert/Notifikasi:** SweetAlert2 (CDN) — untuk semua alert sukses, gagal, dan konfirmasi (hapus, logout, approve, reject, dll)
**Icon:** Font Awesome 6 (CDN)
**Database:** MySQL/MariaDB (struktur relasional penuh, mendukung transaksi ACID)
**Prinsip Inti:** Tidak ada NPM/Vite di frontend — seluruh asset frontend dimuat lewat CDN agar instalasi ringan dan deployment cepat di server shared hosting maupun VPS.

### Filosofi Arsitektur
FINSYNC ERP dibangun di atas **satu mesin akuntansi sentral (Double-Entry Engine)** yang menjadi satu-satunya pintu masuk pencatatan keuangan. Seluruh modul operasional (Kas/Bank, AR, AP, Aset Tetap, dst.) **tidak boleh menulis langsung** ke tabel jurnal. Modul-modul tersebut hanya mengumpulkan data transaksi lalu memanggil service layer terpusat untuk memvalidasi dan memposting jurnal secara otomatis. Ini menjamin `total debit = total kredit` di setiap transaksi, tanpa kecuali.

---

## 2. ARSITEKTUR INTI: DOUBLE-ENTRY ENGINE

### 2.1 Prinsip Dasar
- Persamaan akuntansi: `Assets = Liabilities + Equity`
- Setiap transaksi WAJIB melibatkan minimal 2 akun (1 debit, 1 kredit), dan total nominal debit harus selalu sama dengan total nominal kredit.
- Mesin ini diimplementasikan sebagai **Service Layer** terpisah: `app/Services/Accounting/JournalEngineService.php` — semua modul lain memanggil service ini, tidak ada modul yang menulis manual ke tabel jurnal.

### 2.2 Struktur Tabel Inti

**`transactions`** — metadata kejadian ekonomi (header)
```
id                  BIGINT UNSIGNED PK
transaction_number  VARCHAR(50) UNIQUE      -- format: TRX-YYYYMM-00001
transaction_date    DATE
description         TEXT
source_type         VARCHAR(100)            -- polymorphic: App\Models\Invoice, App\Models\Payment, dst
source_id           BIGINT UNSIGNED         -- polymorphic id
branch_id           BIGINT UNSIGNED FK -> branches
fiscal_period_id    BIGINT UNSIGNED FK -> fiscal_periods
currency_id         BIGINT UNSIGNED FK -> currencies
exchange_rate       DECIMAL(18,6) DEFAULT 1
status               ENUM('draft','posted','reversed') DEFAULT 'draft'
created_by          BIGINT UNSIGNED FK -> users
posted_by           BIGINT UNSIGNED FK -> users NULLABLE
posted_at           TIMESTAMP NULLABLE
reversal_of_id      BIGINT UNSIGNED FK -> transactions NULLABLE  -- menunjuk transaksi asal jika ini jurnal pembalik
created_at, updated_at
```

**`journal_entries`** — baris detail debit/kredit (detail)
```
id                  BIGINT UNSIGNED PK
transaction_id      BIGINT UNSIGNED FK -> transactions
account_id          BIGINT UNSIGNED FK -> chart_of_accounts
cost_center_id      BIGINT UNSIGNED FK -> cost_centers NULLABLE
debit               DECIMAL(18,2) DEFAULT 0
credit              DECIMAL(18,2) DEFAULT 0
debit_base          DECIMAL(18,2) DEFAULT 0   -- nilai setara mata uang dasar (IDR)
credit_base         DECIMAL(18,2) DEFAULT 0
description         VARCHAR(255) NULLABLE
created_at, updated_at
```

> Relasi: `transactions` 1—N `journal_entries`. Relasi ke dokumen sumber (Invoice, Payment, Receipt, Purchase Order) menggunakan **polymorphic relationship** (`source_type`, `source_id`) agar mesin jurnal tetap generik dan dapat dipakai modul apa pun di masa depan tanpa migrasi ulang struktur tabel inti.

### 2.3 Aturan Wajib Service Layer
1. `JournalEngineService::post(array $lines, $sourceModel)` — validasi `SUM(debit) == SUM(credit)` sebelum insert, dibungkus dalam **DB Transaction** (`DB::transaction()`), jika tidak balance maka `throw UnbalancedJournalException`.
2. Tidak ada hard delete pada `transactions` maupun `journal_entries` yang berstatus `posted`. Hanya bisa dibatalkan lewat **reversal entry** (lihat §9).
3. Setiap insert journal_entries WAJIB mengisi `debit_base`/`credit_base` hasil konversi `exchange_rate` agar laporan konsolidasi tidak terdistorsi nilai tukar.

---

## 3. MODUL 1 — MASTER DATA SENTRAL

### 3.1 Entitas yang Dikelola
- Pelanggan (`customers`)
- Pemasok (`suppliers`)
- Bank (`banks`, `bank_accounts`)
- Cabang (`branches`)
- Departemen (`departments`)
- Pusat Biaya / Cost Center (`cost_centers`)
- Kategori Transaksi (`transaction_categories`)
- Pengguna & Role (`users`, `roles`, `permissions`)
- Mata Uang (`currencies`, `exchange_rates`)
- Bagan Akun (`chart_of_accounts`)

### 3.2 Chart of Accounts (Bagan Akun)
Struktur **nested tree** (self-referencing) agar mendukung hierarki akun induk → sub-akun analitik.

```
chart_of_accounts
id              BIGINT UNSIGNED PK
parent_id       BIGINT UNSIGNED FK -> chart_of_accounts NULLABLE
account_code    VARCHAR(20) UNIQUE      -- contoh: 1-1000, 1-1001
account_name    VARCHAR(150)
account_type    ENUM('asset','liability','equity','revenue','expense')
normal_balance  ENUM('debit','credit')
level           TINYINT                  -- kedalaman hierarki
is_postable     BOOLEAN DEFAULT true     -- akun induk biasanya false (header saja)
branch_id       BIGINT UNSIGNED FK -> branches NULLABLE
is_active       BOOLEAN DEFAULT true
created_at, updated_at
```

| Klasifikasi | Saldo Normal | Laporan |
|---|---|---|
| Aset (Kas, Bank, Piutang, Inventaris) | Debit | Neraca |
| Kewajiban (Hutang, Akrual) | Kredit | Neraca |
| Ekuitas (Modal, Laba Ditahan) | Kredit | Neraca |
| Pendapatan (Penjualan, Bunga) | Kredit | Laba Rugi |
| Beban (Operasional, Penyusutan) | Debit | Laba Rugi |

### 3.3 Multi-Cabang & Multi-Mata Uang
```
branches
id, branch_code, branch_name, address, is_head_office, parent_branch_id, is_active

currencies
id, currency_code (IDR/USD/dll), currency_name, symbol, is_base_currency

exchange_rates
id, currency_id FK, rate_date, rate_to_base, created_by
```
- Setiap entri jurnal menyimpan **nilai ganda**: nominal asli (currency transaksi) dan nilai setara IDR (`*_base`).
- Cabang beroperasi otonom secara pencatatan lokal, namun seluruh `journal_entries` diagregasi otomatis ke buku besar pusat via `branch_id` pada tabel `transactions`.

---

## 4. MODUL 2 — KAS DAN BANK MANAGEMENT

### 4.1 Tabel
```
cash_bank_transactions
id, transaction_id FK -> transactions, bank_account_id FK -> bank_accounts NULLABLE
type            ENUM('cash_in','cash_out','transfer','petty_cash')
amount          DECIMAL(18,2)
category_id     FK -> transaction_categories
attachment_path VARCHAR(255) NULLABLE   -- bukti scan kuitansi
branch_id, created_by, created_at, updated_at

bank_accounts
id, bank_id FK -> banks, account_number, account_name, currency_id FK, opening_balance, branch_id

bank_reconciliations
id, bank_account_id FK, period_month, period_year, statement_balance, system_balance,
status ENUM('draft','matched','completed'), reconciled_by, reconciled_at

bank_reconciliation_items
id, reconciliation_id FK, journal_entry_id FK NULLABLE, statement_reference,
statement_date, amount, match_status ENUM('matched','unmatched','bank_fee','unknown_deposit')
```

### 4.2 Business Logic
1. **Pengeluaran kas**: staf input nominal + kategori → sistem otomatis `Kredit: Kas/Bank` & `Debit: akun Beban` sesuai mapping `transaction_categories.default_expense_account_id`. Tidak ada input jurnal manual oleh staf.
2. **Rekonsiliasi Bank**: algoritma pencocokan membandingkan nominal + tanggal + referensi antara `journal_entries` (sisi bank) vs data rekening koran yang diunggah/diinput. Item yang tidak cocok ditandai `unmatched` dan memunculkan dialog konfirmasi tindak lanjut (biaya admin bank belum dicatat / transfer tak dikenal).
3. Setiap transaksi kas/bank WAJIB menghasilkan 1 `transaction_id` di mesin jurnal — modul ini murni antarmuka pengumpul data.

---

## 5. MODUL 3 — ACCOUNTS RECEIVABLE (AR)

### 5.1 Tabel
```
invoices
id, invoice_number UNIQUE, customer_id FK, transaction_id FK -> transactions,
invoice_date, due_date, subtotal, tax_amount, total_amount, paid_amount, balance_due,
status ENUM('draft','posted','partial','paid','overdue','void'),
branch_id, created_by

invoice_items
id, invoice_id FK, product_id FK NULLABLE, description, qty, unit_price, line_total,
cogs_amount NULLABLE   -- diisi otomatis dari hasil kalkulasi FIFO

receipts (penerimaan pembayaran piutang)
id, receipt_number UNIQUE, invoice_id FK, customer_id FK, transaction_id FK,
payment_date, amount, payment_method, bank_account_id FK NULLABLE, created_by
```

### 5.2 Business Logic
1. **Penjualan kredit terbit** → mesin jurnal otomatis: `Debit: Piutang Usaha`, `Kredit: Pendapatan` (+ `Kredit: Hutang PPN Keluaran` jika PKP, lihat §10).
2. **Jika barang berwujud** → integrasi FIFO costing dieksekusi paralel: `Debit: HPP`, `Kredit: Persediaan` berdasarkan harga perolehan stok yang paling awal masuk (lihat `inventory_layers` di §5.3).
3. **Penerimaan pembayaran (parsial/bertahap)** → `Debit: Kas/Bank`, `Kredit: Piutang Usaha`; sistem mengurangi `invoices.balance_due` dan mengubah status otomatis (`partial` jika `paid_amount < total_amount`, `paid` jika lunas).
4. **AR Aging Matrix**: query terjadwal mengelompokkan `balance_due` berdasarkan `due_date` ke bucket: 1–30 hari, 31–60 hari, 61–90 hari, >90 hari.
5. Reminder jatuh tempo otomatis (Laravel Scheduler + Notification) dikirim H-3 sebelum `due_date`.

### 5.3 Inventory Valuation (FIFO)
```
inventory_layers
id, product_id FK, branch_id FK, purchase_date, qty_in, qty_remaining, unit_cost, source_transaction_id FK
```
- Saat penjualan, sistem mengonsumsi `inventory_layers` mulai dari `purchase_date` paling awal hingga qty terjual terpenuhi, lalu menghitung `cogs_amount` berbobot dari kombinasi layer yang terpakai.

---

## 6. MODUL 4 — ACCOUNTS PAYABLE (AP) & 3-WAY MATCHING

### 6.1 Tabel
```
purchase_orders
id, po_number UNIQUE, supplier_id FK, order_date, status ENUM('draft','approved','partially_received','closed','cancelled'),
total_amount, branch_id, created_by

purchase_order_items
id, po_id FK, product_id FK, qty_ordered, unit_price, line_total

goods_receipt_notes (GRN)
id, grn_number UNIQUE, po_id FK, received_date, received_by, branch_id, status ENUM('draft','verified')

goods_receipt_items
id, grn_id FK, po_item_id FK, qty_received

supplier_invoices
id, invoice_number, supplier_id FK, po_id FK, grn_id FK NULLABLE, transaction_id FK NULLABLE,
invoice_date, due_date, total_amount, status ENUM('pending_match','matched','discrepancy','approved','paid'),
branch_id, created_by

ap_payments
id, payment_number, supplier_invoice_id FK, transaction_id FK, payment_date, amount,
bank_account_id FK, withholding_tax_id FK NULLABLE, created_by
```

### 6.2 Logika 3-Way Matching
| Dokumen | Pembuat | Fungsi Verifikasi |
|---|---|---|
| Purchase Order (PO) | Departemen Pembelian | Otoritas pesanan, qty & harga kontrak |
| Goods Receipt Note (GRN) | Gudang | Validasi qty fisik diterima |
| Vendor/Supplier Invoice | Pemasok | Tagih qty dikirim × harga sepakat |

**Algoritma:**
```
fungsi validasiTigaArah(supplier_invoice):
    po = supplier_invoice.purchase_order
    grn = supplier_invoice.goods_receipt_note

    jika supplier_invoice.unit_price > po.unit_price:
        tandai 'discrepancy', blokir pembayaran, kirim notifikasi
    jika supplier_invoice.qty_billed > grn.qty_received:
        tandai 'discrepancy', blokir pembayaran, kirim notifikasi
    jika semua cocok:
        status = 'matched' -> lanjut ke Workflow Approval (Modul 9)
```
- Hanya supplier_invoice berstatus `matched` + `approved` yang boleh diproses ke `ap_payments`.
- **Pencatatan hutang (saat invoice disetujui)**: `Debit: Persediaan/Beban`, `Kredit: Hutang Usaha`.
- **Pembayaran**: `Debit: Hutang Usaha`, `Kredit: Kas/Bank` (+ pemotongan PPh jika berlaku, lihat §10.2).
- AP Aging Report mengikuti pola sama dengan AR Aging (berbasis `due_date` supplier_invoices).

---

## 7. MODUL 5 — GENERAL LEDGER (BUKU BESAR)

### 7.1 Fungsi
- Seluruh `journal_entries` dari modul manapun terkonsolidasi otomatis di sini — tidak ada tabel terpisah; GL adalah **view/query agregat** dari `journal_entries` + `chart_of_accounts`.
- Fitur: Jurnal Umum manual, Jurnal Penyesuaian (akrual, prepaid, dll), Buku Besar per akun, Neraca Saldo (Trial Balance).

### 7.2 Jurnal Manual
```
manual_journals
id, transaction_id FK -> transactions, journal_type ENUM('general','adjusting','correction'),
description, fiscal_period_id FK, requires_approval BOOLEAN, status ENUM('draft','pending','posted'),
created_by, approved_by NULLABLE
```
- Jurnal manual & penyesuaian tetap melewati `JournalEngineService::post()` — tidak ada jalur pintas insert langsung.

### 7.3 Reversal Entry (Wajib, Tidak Ada Hard Delete)
```
fungsi reverse(transaction_id, reason):
    transaksi_asal = Transaction::find(transaction_id)
    validasi: transaksi_asal.status == 'posted'
    transaksi_baru = buat Transaction baru dengan:
        reversal_of_id = transaksi_asal.id
        deskripsi = "Reversal: " . reason
    untuk setiap journal_entry di transaksi_asal:
        buat journal_entry baru di transaksi_baru dengan debit/kredit TERBALIK
    set transaksi_asal.status = 'reversed'
    catat ke audit_trail
```
- Trial Balance dihitung real-time: `SUM(debit_base) per akun` harus selalu `= SUM(credit_base) per akun` secara keseluruhan sistem.

---

## 8. MODUL 6 — BUDGETING & COST CENTER

### 8.1 Tabel
```
cost_centers
id, code, name, department_id FK, branch_id FK, manager_user_id FK, is_active

budgets
id, cost_center_id FK, fiscal_year, period_type ENUM('annual','monthly'),
account_id FK -> chart_of_accounts, budgeted_amount, period_month NULLABLE (1-12)

budget_realizations  (kalkulasi/cache, bisa berupa view)
cost_center_id, account_id, period, realized_amount  -- SUM dari journal_entries ber-cost_center_id terkait
```

### 8.2 Business Logic — Budget Control
```
fungsi cekAnggaran(cost_center_id, account_id, nominal_baru, periode):
    terealisasi = SUM(journal_entries.debit) WHERE cost_center_id, account_id, periode SAMA
    plafon = Budget.budgeted_amount WHERE cost_center_id, account_id, periode SAMA
    jika (terealisasi + nominal_baru) > plafon:
        tahan transaksi -> status 'pending_budget_approval'
        kirim notifikasi ke Manager Divisi terkait
        wajib lolos Workflow Approval (Modul 9) sebelum lanjut
    else:
        lanjutkan proses normal
```
- Tag `cost_center_id` dipasang di `journal_entries` dan `purchase_orders` agar atribusi biaya granular tanpa membengkakkan Chart of Accounts.

---

## 9. MODUL 7 — FIXED ASSET MANAGEMENT

### 9.1 Tabel
```
fixed_assets
id, asset_code UNIQUE, asset_name, category, cost_basis DECIMAL(18,2),
salvage_value DECIMAL(18,2), useful_life_months INT, acquisition_date,
asset_account_id FK -> chart_of_accounts,            -- akun aset di neraca
accum_depreciation_account_id FK -> chart_of_accounts,
expense_account_id FK -> chart_of_accounts,           -- akun beban penyusutan
branch_id, status ENUM('active','disposed','fully_depreciated')

asset_depreciation_schedules
id, asset_id FK, period_month, period_year, depreciation_amount,
accumulated_amount, book_value, transaction_id FK NULLABLE, is_posted BOOLEAN
```

### 9.2 Formula (Straight-Line Depreciation)
```
Annual Depreciation   = (Cost Basis - Salvage Value) / Useful Life (tahun)
Monthly Depreciation  = Annual Depreciation / 12
```

### 9.3 Job Terjadwal (Laravel Scheduler)
```
fungsi jalankanPenyusutanBulanan():   // dijalankan tiap akhir bulan via Cron
    untuk setiap asset di FixedAsset::where('status','active'):
        hitung monthly_depreciation
        jika belum mencapai (cost_basis - salvage_value):
            posting jurnal: Debit Beban Penyusutan, Kredit Akumulasi Penyusutan
            simpan ke asset_depreciation_schedules
        jika accumulated_amount >= (cost_basis - salvage_value):
            set asset.status = 'fully_depreciated'
```

---

## 10. MODUL 8 — FINANCIAL REPORTING

### 10.1 Laporan Wajib
- Neraca (Balance Sheet)
- Laporan Laba Rugi (Income Statement)
- Laporan Arus Kas (Cash Flow Statement — aktivitas operasi/investasi/pendanaan)
- Neraca Saldo (Trial Balance)
- Buku Besar agregat per akun
- Jurnal Umum (ringkasan)
- AR Aging / AP Aging
- Laporan Pajak Tertahan (PPh 21/23/4(2))
- Budget vs Actual

### 10.2 Spesifikasi Teknis
- Semua laporan mendukung **filter multidimensi**: cabang (`branch_id`), cost center, rentang tanggal kustom.
- Ekspor: Excel (gunakan package `maatwebsite/excel`) dan PDF (gunakan `barryvdh/laravel-dompdf`) dengan tata letak korporat formal.
- Dashboard Keuangan: widget rasio likuiditas (current ratio, quick ratio), tren kas, top AR overdue, top AP jatuh tempo — di-cache (Laravel Cache) untuk performa, refresh berkala.

---

## 11. MODUL 9 — WORKFLOW APPROVAL SYSTEM

### 11.1 Tabel
```
approval_rules
id, rule_name, transaction_type ENUM('ap_payment','manual_journal','budget_override','po_approval'),
min_amount, max_amount NULLABLE, branch_id NULLABLE, is_active

approval_rule_levels
id, approval_rule_id FK, level_order INT, approver_role_id FK -> roles, is_final_level BOOLEAN

approvals
id, transaction_reference_type, transaction_reference_id, approval_rule_id FK,
current_level INT, status ENUM('pending','approved','rejected'), submitted_by, submitted_at

approval_logs
id, approval_id FK, level_order, approver_user_id FK (SNAPSHOT — dikunci saat submit),
action ENUM('approved','rejected'), notes, acted_at
```

### 11.2 Business Logic
```
fungsi ajukanPersetujuan(transaksi, nominal):
    rule = ApprovalRule::cariBerdasarkan(transaksi.type, nominal)
    approval = buat Approval(status: 'pending', current_level: 1)
    untuk setiap level di rule.levels (urut level_order):
        snapshot approver_user_id SAAT INI (kunci, anti-perubahan jika ada mutasi jabatan nanti)
    kirim notifikasi ke approver level 1

fungsi prosesPersetujuan(approval, user, action):
    jika user bukan approver level saat ini -> tolak akses
    catat ke approval_logs
    jika action == 'rejected':
        approval.status = 'rejected', transaksi dikembalikan ke draft
    jika action == 'approved':
        jika ada level berikutnya: approval.current_level += 1, notifikasi approver berikutnya
        jika ini level final: approval.status = 'approved', lanjutkan eksekusi transaksi (posting jurnal/pencairan dana)
```

**Contoh implementasi nominal (sesuai dokumen):**
- Pembayaran ≤ Rp 50.000.000 → cukup approval **Finance Manager** (level 1, final).
- Pembayaran > Rp 100.000.000–150.000.000 → eskalasi otomatis ke **Direktur** (level 2, final) setelah Finance Manager approve di level 1.

---

## 12. MODUL 10 — AUDIT TRAIL SYSTEM

### 12.1 Tabel
```
audit_trails
id, user_id FK, action ENUM('create','update','delete','approve','reject','reverse','login','logout'),
auditable_type, auditable_id,        -- polymorphic, model apa pun yang diobservasi
old_values JSON NULLABLE,
new_values JSON NULLABLE,
ip_address VARCHAR(45),
user_agent VARCHAR(255),
created_at
```

### 12.2 Implementasi Teknis
- Gunakan **Laravel Observer pattern** (`app/Observers/AuditObserver.php`) didaftarkan ke seluruh model finansial kritikal (`Transaction`, `JournalEntry`, `Invoice`, `SupplierInvoice`, `FixedAsset`, `Approval`, dll) via `EventServiceProvider` atau `AppServiceProvider::boot()`.
- Event `created`, `updated`, `deleted` (soft delete saja) menangkap `old_values` vs `new_values` (diff array, bukan dump penuh model).
- **Immutable**: tabel `audit_trails` TIDAK memiliki endpoint update/delete di seluruh sistem, termasuk untuk role Super Admin. Tidak ada UI maupun route yang mengizinkan modifikasi baris audit.

---

## 13. MODUL 11 — PERIOD CLOSING

### 13.1 Tabel
```
fiscal_periods
id, period_name (contoh: "Januari 2026"), period_month, period_year,
start_date, end_date, status ENUM('open','closed'), closed_by NULLABLE, closed_at NULLABLE

fiscal_years
id, year, status ENUM('open','closed'), closed_by NULLABLE, closed_at NULLABLE
```

### 13.2 Business Logic
```
middleware CekPeriodeTerkunci:
    jika transaksi.tanggal berada pada fiscal_period.status == 'closed':
        tolak request (create/update/void) kecuali user punya permission khusus 'override_closed_period'
        (override tetap tercatat di audit_trails dengan flag mencurigakan)

fungsi tutupPeriode(fiscal_period_id, user):
    validasi: Trial Balance balance (debit == kredit)
    set fiscal_period.status = 'closed', closed_by = user.id, closed_at = now()
    catat audit_trails

fungsi tutupTahunFiskal(fiscal_year_id):
    validasi: semua 12 fiscal_periods dalam tahun tsb berstatus 'closed'
    hitung Laba Tahun Berjalan = SUM(pendapatan) - SUM(beban) seluruh tahun
    posting jurnal penutup:
        Debit/Kredit seluruh akun Pendapatan & Beban -> nol kan saldo
        Kredit/Debit ke akun "Laba Ditahan" sebesar Laba Tahun Berjalan bersih
    set fiscal_year.status = 'closed'
```
- Koreksi kesalahan periode lalu **wajib** lewat Jurnal Koreksi di periode terbuka terdekat — bukan edit langsung ke periode lampau.

---

## 14. KEPATUHAN PERPAJAKAN INDONESIA

### 14.1 PPN & e-Faktur
```
tax_settings
id, tax_type ENUM('ppn','pph21','pph23','pph4_2'), rate_percentage, is_active, effective_date

customers / suppliers tambahan kolom:
npwp VARCHAR(20) NULLABLE
nik VARCHAR(20) NULLABLE
pkp_status BOOLEAN DEFAULT false

tax_invoice_numbers (NSFP)
id, nsfp_number UNIQUE, allocated_block_start, allocated_block_end,
used_count, status ENUM('available','used')
```
- Saat invoice AR dicetak untuk customer ber-status PKP: sistem mengambil **NSFP berurutan** dari pool alokasi DJP, hitung PPN 11%/12% (rate dari `tax_settings`, mudah disesuaikan jika regulasi berubah).
- Fitur ekspor CSV/XML kompatibel format **Coretax / e-Faktur Desktop** untuk setiap batch invoice periode tertentu (`app/Exports/CoretaxExport.php`).

### 14.2 Pemotongan PPh (Withholding Tax) — PPh 23 / PPh 4(2) / PPh 21
```
withholding_tax_transactions
id, supplier_invoice_id FK NULLABLE, tax_type ENUM('pph23','pph4_2','pph21'),
dpp_amount DECIMAL(18,2), tax_rate DECIMAL(5,2), tax_amount DECIMAL(18,2),
transaction_id FK -> transactions, ebupot_status ENUM('pending','submitted') DEFAULT 'pending'
```

**Contoh otomatisasi jurnal (jasa konsultan Rp 50.000.000, PPh 23 tarif 2%):**
```
Debit:  Beban Konsultasi             Rp 50.000.000
Kredit: Hutang Dagang (AP)           Rp 49.000.000
Kredit: Hutang Pemotongan PPh 23     Rp  1.000.000
```
- Logic: `tax_amount = dpp_amount * (tax_rate / 100)`; `net_payable = dpp_amount - tax_amount` dicatat sebagai Kredit AP, sedangkan `tax_amount` dicatat ke akun "Hutang Pemotongan Pajak" terpisah per jenis pajak.
- Data ini diagregasi per periode untuk ekspor ke **e-Bupot Unifikasi**.

---

## 15. ROLE-BASED ACCESS CONTROL (RBAC)

### 15.1 Tabel
```
roles            id, role_name, role_slug, description
permissions      id, permission_name, permission_slug, module
role_permissions id, role_id FK, permission_id FK
users            id, name, email, password, role_id FK, branch_id FK NULLABLE,
                 department_id FK NULLABLE, is_active, last_login_at
```
Gunakan package `spatie/laravel-permission` agar matriks granular per-role mudah dikelola tanpa hardcode middleware.

### 15.2 Matriks Peran

| Peran | Hak Akses Utama | Batasan |
|---|---|---|
| **Super Admin** | Konfigurasi organisasi, Chart of Accounts, matriks hak akses, mesin mata uang asing, akses log audit mentah | TIDAK bisa posting jurnal harian / mengubah status Period Closing — mencegah intervensi manipulasi dari TI |
| **Direktur/Owner** | Dashboard eksekutif, Laba Rugi real-time, monitoring kas, approval pembayaran nominal sangat besar (>Rp 100 juta) | Tidak ada akses input operasional (entri faktur/kas) — murni strategis |
| **Finance Manager** | Monitor seluruh jurnal & kas, approve AP level menengah, approve draft jurnal penyesuaian, rilis Period Closing, verifikasi audit trail staf | Seluruh aksinya tercatat tebal di Audit Trail (tidak terhapus) |
| **Accountant** | Kelola General Ledger penuh, jurnal manual & penyesuaian akhir bulan, rekonsiliasi bank bulanan, jurnal penyusutan aset, kompilasi laporan finansial | Mutlak TIDAK bisa ubah/hapus jurnal operasional periode yang sudah closed |
| **Finance Staff** | Input faktur AP, upload bukti bayar AR, catat kas masuk/keluar rutin, upload dokumen pendukung | Hanya gerbang input + baca; tidak bisa sentuh laporan GL tervalidasi atau lewati limit approval |
| **Cashier (Kasir)** | Pencatatan penerimaan kas tunai & likuiditas di cabang | Buta total terhadap Laba Rugi agregat, GL pusat, atau divisi lain |
| **Auditor Internal** | Read-only penuh: seluruh approval log, audit trail, riwayat akun, jurnal faktur | Diblokir keras dari Create/Update/Delete/Approve di level database |
| **Manager Divisi** | Kelola Cost Center divisinya: rancang anggaran, tinjau serapan dana, approve purchase requisition awal stafnya | Terisolasi di divisinya — tidak bisa intip serapan/jurnal divisi lain |

### 15.3 Implementasi Middleware
```php
// contoh penerapan di routes/web.php
Route::middleware(['auth', 'role:accountant|finance_manager'])->group(function () {
    Route::resource('general-ledger', GeneralLedgerController::class);
});

Route::middleware(['auth', 'permission:close_period'])->group(function () {
    Route::post('fiscal-periods/{id}/close', [FiscalPeriodController::class, 'close']);
});
```
- Setiap controller method WAJIB dicek ulang dengan `$this->authorize()` (Laravel Policy) selain middleware route, sebagai lapisan pertahanan kedua (defense in depth).

---

## 16. STRUKTUR PROYEK LARAVEL 12 (REKOMENDASI)

```
app/
├── Console/
│   └── Commands/
│       ├── RunMonthlyDepreciation.php
│       └── SendInvoiceReminders.php
├── Models/
│   ├── Accounting/  (Transaction, JournalEntry, ChartOfAccount, FiscalPeriod, FiscalYear)
│   ├── AR/          (Invoice, InvoiceItem, Receipt, Customer)
│   ├── AP/          (PurchaseOrder, GoodsReceiptNote, SupplierInvoice, ApPayment, Supplier)
│   ├── Asset/        (FixedAsset, AssetDepreciationSchedule)
│   ├── Budget/       (CostCenter, Budget)
│   ├── Tax/          (TaxSetting, WithholdingTaxTransaction, TaxInvoiceNumber)
│   ├── Approval/     (ApprovalRule, Approval, ApprovalLog)
│   └── Audit/        (AuditTrail)
├── Services/
│   ├── Accounting/JournalEngineService.php     <-- INTI SISTEM
│   ├── Accounting/PeriodClosingService.php
│   ├── AR/InventoryFifoService.php
│   ├── AP/ThreeWayMatchService.php
│   ├── Asset/DepreciationService.php
│   ├── Budget/BudgetControlService.php
│   ├── Tax/WithholdingTaxService.php
│   ├── Tax/CoretaxExportService.php
│   └── Approval/ApprovalWorkflowService.php
├── Observers/
│   └── AuditObserver.php
├── Http/
│   ├── Controllers/  (per modul, sesuai struktur Models di atas)
│   ├── Middleware/
│   │   └── CheckClosedPeriod.php
│   └── Requests/     (FormRequest validasi per modul)
├── Exports/           (laporan Excel via maatwebsite/excel)
└── Policies/          (per model, selaras RBAC §15)

database/migrations/    (urutan sesuai dependency FK: currencies -> branches -> chart_of_accounts -> ... -> journal_entries)
resources/views/        (Blade + Bootstrap 5, lihat design-erp.md)
```

---

## 17. PRINSIP PENGEMBANGAN WAJIB (NON-NEGOTIABLE)

1. **Single Source of Truth Jurnal** — tidak ada modul yang insert manual ke `journal_entries` di luar `JournalEngineService`.
2. **No Hard Delete pada data finansial yang sudah posted** — gunakan soft delete + reversal entry.
3. **Period Locking** — middleware wajib mengecek `fiscal_periods.status` di setiap operasi tulis yang menyentuh tanggal transaksi.
4. **Audit Everything** — observer pattern aktif di semua model finansial kritikal, immutable, termasuk dari Super Admin.
5. **DB Transaction Wrapping** — setiap proses yang melibatkan lebih dari 1 tabel (posting jurnal, approval, closing) WAJIB dibungkus `DB::transaction()` agar atomic.
6. **Validasi Balance Sebelum Commit** — `SUM(debit) === SUM(credit)` dicek di level service, bukan hanya di level UI/JS.
7. **Snapshot Approver** — `approval_logs.approver_user_id` dikunci saat submit, tidak mengikuti perubahan struktur organisasi setelahnya.
8. **Semua teks UI, pesan validasi, dan komentar kode dalam Bahasa Indonesia**, konsisten dengan proyek-proyek lain.
9. **Frontend CDN-only** — Bootstrap 5, Font Awesome 6, SweetAlert2 dimuat via CDN, tanpa NPM/Vite build step.
10. **SweetAlert2 wajib untuk semua dialog kritikal**: konfirmasi hapus, konfirmasi logout, konfirmasi approve/reject, alert sukses, alert gagal — tidak ada native `confirm()`/`alert()` browser yang dipakai di production.
