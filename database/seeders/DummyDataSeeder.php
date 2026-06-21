<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Accounting\Branch;
use App\Models\Accounting\Department;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Currency;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\Transaction;
use App\Models\Budget\CostCenter;
use App\Models\Budget\Budget;
use App\Models\CashBank\Bank;
use App\Models\CashBank\BankAccount;
use App\Models\CashBank\TransactionCategory;
use App\Models\CashBank\CashBankTransaction;
use App\Models\AR\Customer;
use App\Models\AR\Invoice;
use App\Models\AR\Receipt;
use App\Models\AP\Supplier;
use App\Models\AP\SupplierInvoice;
use App\Models\AP\ApPayment;
use App\Models\Asset\FixedAsset;
use App\Models\User;
use App\Services\Accounting\JournalEngineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    protected $journalEngine;

    public function __construct()
    {
        $this->journalEngine = app(JournalEngineService::class);
    }

    public function run(): void
    {
        $admin = User::first();
        $branch = Branch::first();
        $dept = Department::first();
        $currency = Currency::where('is_base_currency', true)->first();
        $currentDate = Carbon::now();
        $period = FiscalPeriod::where('start_date', '<=', $currentDate)
            ->where('end_date', '>=', $currentDate)
            ->first();

        // 1. Cost Centers
        $ccMarketing = CostCenter::firstOrCreate(
            ['code' => 'CC-MKT'],
            ['name' => 'Divisi Marketing', 'branch_id' => $branch->id, 'department_id' => $dept->id]
        );
        $ccIT = CostCenter::firstOrCreate(
            ['code' => 'CC-IT'],
            ['name' => 'Divisi IT & Development', 'branch_id' => $branch->id, 'department_id' => $dept->id]
        );

        // 2. Bank Accounts
        $bankBca = Bank::where('code', 'BCA')->first();
        $accountBca = BankAccount::firstOrCreate(
            ['account_number' => '1234567890'],
            [
                'bank_id' => $bankBca->id, 
                'account_name' => 'PT FINSYNC ERPSYS BCA', 
                'currency_id' => $currency->id, 
                'branch_id' => $branch->id,
                'opening_balance' => 0
            ]
        );

        // 3. Transaction Categories
        $catSales = TransactionCategory::firstOrCreate(
            ['name' => 'Penerimaan Penjualan'],
            ['type' => 'in', 'default_expense_account_id' => ChartOfAccount::where('account_code', '4-1000')->first()->id]
        );
        $catListrik = TransactionCategory::firstOrCreate(
            ['name' => 'Pembayaran Listrik & Air'],
            ['type' => 'out', 'default_expense_account_id' => ChartOfAccount::where('account_code', '6-2000')->first()->id]
        );
        $catGaji = TransactionCategory::firstOrCreate(
            ['name' => 'Pembayaran Gaji Karyawan'],
            ['type' => 'out', 'default_expense_account_id' => ChartOfAccount::where('account_code', '6-1000')->first()->id]
        );

        // 4. Setoran Modal Awal (Manual Journal)
        $kasAkun = ChartOfAccount::where('account_code', '1-1120')->first(); // BCA
        $modalAkun = ChartOfAccount::where('account_code', '3-1000')->first(); // Modal Disetor
        
        $modalTransaction = new Transaction([
            'transaction_number' => 'JV-' . date('Ym') . '-0001',
            'transaction_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
            'description' => 'Setoran Modal Awal Pemegang Saham',
            'branch_id' => $branch->id,
            'fiscal_period_id' => $period->id,
            'currency_id' => $currency->id,
            'status' => 'posted',
            'created_by' => $admin->id,
            'posted_by' => $admin->id,
            'posted_at' => Carbon::now()
        ]);
        $modalTransaction->save();
        
        $this->journalEngine->post([
            ['account_id' => $kasAkun->id, 'debit' => 500000000, 'credit' => 0, 'description' => 'Kas Masuk'],
            ['account_id' => $modalAkun->id, 'debit' => 0, 'credit' => 500000000, 'description' => 'Modal Saham']
        ], $modalTransaction, [
            'transaction_date' => $modalTransaction->transaction_date,
            'description' => $modalTransaction->description,
            'branch_id' => $branch->id,
            'fiscal_period_id' => $period->id,
            'currency_id' => $currency->id,
            'exchange_rate' => 1,
            'created_by' => $admin->id
        ]);

        // 5. Customer & Invoices (AR)
        $customer1 = Customer::firstOrCreate(['email' => 'tech@inovasi.com'], ['name' => 'PT Tech Inovasi', 'phone' => '08111', 'address' => 'Jakarta']);
        $customer2 = Customer::firstOrCreate(['email' => 'mandiri@retail.com'], ['name' => 'CV Mandiri Retail', 'phone' => '08222', 'address' => 'Surabaya']);

        // Invoice 1 (Lunas)
        $inv1 = Invoice::create([
            'invoice_number' => 'INV-'.date('Ym').'-101',
            'customer_id' => $customer1->id,
            'invoice_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
            'due_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
            'subtotal' => 25000000,
            'total_amount' => 25000000,
            'balance_due' => 25000000,
            'status' => 'posted',
            'branch_id' => $branch->id,
            'created_by' => $admin->id
        ]);
        $arAccount = ChartOfAccount::where('account_code', '1-1200')->first();
        $revAccount = ChartOfAccount::where('account_code', '4-1000')->first();
        $this->journalEngine->post([
            ['account_id' => $arAccount->id, 'debit' => 25000000, 'credit' => 0, 'description' => 'Piutang'],
            ['account_id' => $revAccount->id, 'debit' => 0, 'credit' => 25000000, 'description' => 'Pendapatan']
        ], $inv1, [
            'transaction_date' => $inv1->invoice_date, 'description' => 'Penjualan ' . $inv1->invoice_number,
            'branch_id' => $branch->id, 'fiscal_period_id' => $period->id, 'currency_id' => $currency->id, 'created_by' => $admin->id
        ]);

        // Receipt Inv 1 (Lunas)
        $receipt = Receipt::create([
            'receipt_number' => 'RCP-'.date('Ym').'-101', 'invoice_id' => $inv1->id,
            'payment_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'amount' => 25000000,
            'customer_id' => $customer1->id,
            'payment_method' => 'bank_transfer',
            'bank_account_id' => $accountBca->id,
            'created_by' => $admin->id
        ]);
        $inv1->update(['paid_amount' => 25000000, 'balance_due' => 0, 'status' => 'paid']);
        $this->journalEngine->post([
            ['account_id' => $kasAkun->id, 'debit' => 25000000, 'credit' => 0, 'description' => 'Penerimaan Kas'],
            ['account_id' => $arAccount->id, 'debit' => 0, 'credit' => 25000000, 'description' => 'Pelunasan Piutang']
        ], $receipt, [
            'transaction_date' => $receipt->payment_date, 'description' => 'Penerimaan Piutang ' . $inv1->invoice_number,
            'branch_id' => $branch->id, 'fiscal_period_id' => $period->id, 'currency_id' => $currency->id, 'created_by' => $admin->id
        ]);

        // Invoice 2 (Belum Dibayar - Outstanding Piutang)
        $inv2 = Invoice::create([
            'invoice_number' => 'INV-'.date('Ym').'-102',
            'customer_id' => $customer2->id,
            'invoice_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'due_date' => Carbon::now()->addDays(28)->format('Y-m-d'),
            'subtotal' => 45000000,
            'total_amount' => 45000000,
            'balance_due' => 45000000,
            'status' => 'posted',
            'branch_id' => $branch->id,
            'created_by' => $admin->id
        ]);
        $this->journalEngine->post([
            ['account_id' => $arAccount->id, 'debit' => 45000000, 'credit' => 0, 'description' => 'Piutang'],
            ['account_id' => $revAccount->id, 'debit' => 0, 'credit' => 45000000, 'description' => 'Pendapatan']
        ], $inv2, [
            'transaction_date' => $inv2->invoice_date, 'description' => 'Penjualan ' . $inv2->invoice_number,
            'branch_id' => $branch->id, 'fiscal_period_id' => $period->id, 'currency_id' => $currency->id, 'created_by' => $admin->id
        ]);

        // 6. Supplier & Invoices (AP)
        $supplier1 = Supplier::firstOrCreate(['email' => 'sales@distributor.com'], ['name' => 'PT Maju Distributor', 'phone' => '08333', 'address' => 'Bandung']);
        
        // AP Invoice (Belum Dibayar - Outstanding Hutang)
        $suppInv = SupplierInvoice::create([
            'invoice_number' => 'SUP-'.date('Ym').'-999',
            'supplier_id' => $supplier1->id,
            'invoice_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'due_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
            'total_amount' => 15000000,
            'status' => 'approved',
            'branch_id' => $branch->id,
            'created_by' => $admin->id
        ]);
        $apAccount = ChartOfAccount::where('account_code', '2-1100')->first();
        $expenseAccount = ChartOfAccount::where('account_code', '6-2000')->first(); // Asumsikan biaya listrik/air atau operasional
        $this->journalEngine->post([
            ['account_id' => $expenseAccount->id, 'debit' => 15000000, 'credit' => 0, 'description' => 'Biaya Operasional'],
            ['account_id' => $apAccount->id, 'debit' => 0, 'credit' => 15000000, 'description' => 'Hutang Usaha']
        ], $suppInv, [
            'transaction_date' => $suppInv->invoice_date, 'description' => 'Tagihan Supplier ' . $suppInv->invoice_number,
            'branch_id' => $branch->id, 'fiscal_period_id' => $period->id, 'currency_id' => $currency->id, 'created_by' => $admin->id
        ]);

        // 7. Pengeluaran Kas Langsung (Pembayaran Gaji)
        $gajiAccount = ChartOfAccount::where('account_code', '6-1000')->first();
        
        $gajiTransaction = new Transaction([
            'transaction_number' => 'JV-' . date('Ym') . '-0002',
            'transaction_date' => Carbon::now()->format('Y-m-d'),
            'description' => 'Pembayaran Gaji Karyawan Bulan Ini',
            'branch_id' => $branch->id,
            'fiscal_period_id' => $period->id,
            'currency_id' => $currency->id,
            'status' => 'posted',
            'created_by' => $admin->id,
            'posted_by' => $admin->id,
            'posted_at' => Carbon::now()
        ]);
        $gajiTransaction->save();

        $cbTransaction = CashBankTransaction::create([
            'transaction_id' => $gajiTransaction->id,
            'bank_account_id' => $accountBca->id,
            'type' => 'cash_out',
            'amount' => 12500000,
            'category_id' => $catGaji->id,
            'branch_id' => $branch->id,
            'created_by' => $admin->id
        ]);
        
        $this->journalEngine->post([
            ['account_id' => $gajiAccount->id, 'debit' => 12500000, 'credit' => 0, 'description' => 'Pembayaran Gaji Karyawan'],
            ['account_id' => $kasAkun->id, 'debit' => 0, 'credit' => 12500000, 'description' => 'Kas Keluar']
        ], $gajiTransaction, [
            'transaction_date' => $gajiTransaction->transaction_date, 'description' => $gajiTransaction->description,
            'branch_id' => $branch->id, 'fiscal_period_id' => $period->id, 'currency_id' => $currency->id, 'created_by' => $admin->id
        ]);

        // 8. Fixed Asset
        $asset = FixedAsset::create([
            'asset_code' => 'AST-001',
            'asset_name' => 'Laptop MacBook Pro M3',
            'category' => 'IT Equipment',
            'acquisition_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'cost_basis' => 30000000,
            'useful_life_months' => 48,
            'salvage_value' => 5000000,
            'asset_account_id' => ChartOfAccount::where('account_code', '1-0000')->first()->id,
            'accum_depreciation_account_id' => ChartOfAccount::where('account_code', '1-0000')->first()->id,
            'expense_account_id' => ChartOfAccount::where('account_code', '6-3000')->first()->id,
            'branch_id' => $branch->id,
            'status' => 'active'
        ]);

        // 9. Budget
        $fy = \App\Models\Accounting\FiscalYear::first();
        $budget = Budget::create([
            'cost_center_id' => $ccIT->id,
            'fiscal_year' => $fy->year,
            'period_type' => 'annual',
            'account_id' => $gajiAccount->id,
            'budgeted_amount' => 240000000
        ]);
    }
}
