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
use Faker\Factory as Faker;
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
        $faker = Faker::create('id_ID');
        $admin = User::first();
        $branch = Branch::first();
        $dept = Department::first();
        $currency = Currency::where('is_base_currency', true)->first();

        // 1. Cost Centers
        $costCenters = [];
        foreach (['Marketing', 'IT & Dev', 'HR & GA', 'Sales', 'Operation'] as $code => $name) {
            $costCenters[] = CostCenter::firstOrCreate(
                ['code' => 'CC-'.strtoupper(substr($name, 0, 3))],
                ['name' => 'Divisi ' . $name, 'branch_id' => $branch->id, 'department_id' => $dept->id]
            );
        }

        // 2. Bank Accounts
        $bankBca = Bank::where('code', 'BCA')->first();
        $bankMandiri = Bank::where('code', 'MANDIRI')->first();
        
        $accountBca = BankAccount::firstOrCreate(
            ['account_number' => '1234567890'],
            ['bank_id' => $bankBca->id, 'account_name' => 'PT FINSYNC BCA', 'currency_id' => $currency->id, 'branch_id' => $branch->id, 'opening_balance' => 0]
        );
        $accountMandiri = BankAccount::firstOrCreate(
            ['account_number' => '0987654321'],
            ['bank_id' => $bankMandiri->id, 'account_name' => 'PT FINSYNC MANDIRI', 'currency_id' => $currency->id, 'branch_id' => $branch->id, 'opening_balance' => 0]
        );

        // 3. Transaction Categories
        $catSales = TransactionCategory::firstOrCreate(['name' => 'Penerimaan Penjualan'], ['type' => 'in', 'default_expense_account_id' => ChartOfAccount::where('account_code', '4-1000')->first()->id]);
        $catListrik = TransactionCategory::firstOrCreate(['name' => 'Pembayaran Listrik & Air'], ['type' => 'out', 'default_expense_account_id' => ChartOfAccount::where('account_code', '6-2000')->first()->id]);
        $catGaji = TransactionCategory::firstOrCreate(['name' => 'Pembayaran Gaji Karyawan'], ['type' => 'out', 'default_expense_account_id' => ChartOfAccount::where('account_code', '6-1000')->first()->id]);
        $catOperational = TransactionCategory::firstOrCreate(['name' => 'Beban Operasional Lainnya'], ['type' => 'out', 'default_expense_account_id' => ChartOfAccount::where('account_code', '6-2000')->first()->id]);

        $kasAkun = ChartOfAccount::where('account_code', '1-1120')->first();
        $modalAkun = ChartOfAccount::where('account_code', '3-1000')->first();
        $arAccount = ChartOfAccount::where('account_code', '1-1200')->first();
        $apAccount = ChartOfAccount::where('account_code', '2-1100')->first();
        $revAccount = ChartOfAccount::where('account_code', '4-1000')->first();

        // Helpers to get correct period
        $getPeriod = function($date) {
            return FiscalPeriod::where('start_date', '<=', $date)->where('end_date', '>=', $date)->first();
        };

        // 4. Modal Awal (6 months ago)
        $modalDate = Carbon::now()->subMonths(6)->startOfMonth();
        $periodModal = $getPeriod($modalDate);
        if ($periodModal) {
            $modalTransaction = new Transaction([
                'transaction_number' => 'JV-' . $modalDate->format('Ym') . '-0001',
                'transaction_date' => $modalDate->format('Y-m-d'),
                'description' => 'Setoran Modal Awal Pemegang Saham',
                'branch_id' => $branch->id, 'fiscal_period_id' => $periodModal->id, 'currency_id' => $currency->id,
                'status' => 'posted', 'created_by' => $admin->id, 'posted_by' => $admin->id, 'posted_at' => Carbon::now()
            ]);
            $modalTransaction->save();
            $this->journalEngine->post([
                ['account_id' => $kasAkun->id, 'debit' => 1000000000, 'credit' => 0, 'description' => 'Kas Masuk'],
                ['account_id' => $modalAkun->id, 'debit' => 0, 'credit' => 1000000000, 'description' => 'Modal Saham']
            ], $modalTransaction, [
                'transaction_date' => $modalTransaction->transaction_date, 'description' => $modalTransaction->description,
                'branch_id' => $branch->id, 'fiscal_period_id' => $periodModal->id, 'currency_id' => $currency->id, 'created_by' => $admin->id
            ]);
        }

        // 5. Customers & Suppliers (10 each)
        $customers = [];
        $suppliers = [];
        for($i=0; $i<10; $i++) {
            $customers[] = Customer::create([
                'name' => $faker->company, 'email' => $faker->unique()->companyEmail, 
                'phone' => $faker->phoneNumber, 'address' => $faker->address, 'npwp' => $faker->numerify('##.###.###.#-###.###')
            ]);
            $suppliers[] = Supplier::create([
                'name' => $faker->company, 'email' => $faker->unique()->companyEmail, 
                'phone' => $faker->phoneNumber, 'address' => $faker->address, 'npwp' => $faker->numerify('##.###.###.#-###.###')
            ]);
        }

        // 6. Generate Transactions over the last 6 months
        for ($month = 5; $month >= 0; $month--) {
            $baseDate = Carbon::now()->subMonths($month);
            
            // Generate 5-10 AR Invoices per month
            $invCount = rand(5, 10);
            for($i=0; $i<$invCount; $i++) {
                $invDate = $baseDate->copy()->addDays(rand(1, 28));
                $period = $getPeriod($invDate);
                if (!$period) continue;

                $amount = rand(5, 50) * 1000000; // 5jt - 50jt
                $customer = $faker->randomElement($customers);
                
                $inv = Invoice::create([
                    'invoice_number' => 'INV-'.$invDate->format('Ym').'-'.str_pad($i, 3, '0', STR_PAD_LEFT).'-'.rand(10,99),
                    'customer_id' => $customer->id, 'invoice_date' => $invDate->format('Y-m-d'), 'due_date' => $invDate->copy()->addDays(30)->format('Y-m-d'),
                    'subtotal' => $amount, 'total_amount' => $amount, 'balance_due' => $amount,
                    'status' => 'posted', 'branch_id' => $branch->id, 'created_by' => $admin->id
                ]);

                $this->journalEngine->post([
                    ['account_id' => $arAccount->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Piutang'],
                    ['account_id' => $revAccount->id, 'debit' => 0, 'credit' => $amount, 'description' => 'Pendapatan']
                ], $inv, [
                    'transaction_date' => $inv->invoice_date, 'description' => 'Penjualan ' . $inv->invoice_number,
                    'branch_id' => $branch->id, 'fiscal_period_id' => $period->id, 'currency_id' => $currency->id, 'created_by' => $admin->id
                ]);

                // 70% chance to be paid if older than 1 month, 30% if this month
                $payChance = ($month > 0) ? 80 : 30;
                if (rand(1, 100) <= $payChance) {
                    $payDate = $invDate->copy()->addDays(rand(5, 25));
                    $payPeriod = $getPeriod($payDate);
                    if($payPeriod) {
                        $receipt = Receipt::create([
                            'receipt_number' => 'RCP-'.$payDate->format('Ym').'-'.str_pad($i, 3, '0', STR_PAD_LEFT).'-'.rand(10,99),
                            'invoice_id' => $inv->id, 'payment_date' => $payDate->format('Y-m-d'), 'amount' => $amount,
                            'customer_id' => $customer->id, 'payment_method' => 'bank_transfer', 'bank_account_id' => $accountBca->id, 'created_by' => $admin->id
                        ]);
                        $inv->update(['paid_amount' => $amount, 'balance_due' => 0, 'status' => 'paid']);
                        $this->journalEngine->post([
                            ['account_id' => $kasAkun->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Penerimaan Kas'],
                            ['account_id' => $arAccount->id, 'debit' => 0, 'credit' => $amount, 'description' => 'Pelunasan Piutang']
                        ], $receipt, [
                            'transaction_date' => $receipt->payment_date, 'description' => 'Penerimaan Piutang ' . $inv->invoice_number,
                            'branch_id' => $branch->id, 'fiscal_period_id' => $payPeriod->id, 'currency_id' => $currency->id, 'created_by' => $admin->id
                        ]);
                    }
                }
            }

            // Generate 3-6 AP Invoices per month
            $apCount = rand(3, 6);
            for($i=0; $i<$apCount; $i++) {
                $invDate = $baseDate->copy()->addDays(rand(1, 28));
                $period = $getPeriod($invDate);
                if (!$period) continue;

                $amount = rand(3, 20) * 1000000;
                $supplier = $faker->randomElement($suppliers);
                
                $suppInv = SupplierInvoice::create([
                    'invoice_number' => 'SUP-'.$invDate->format('Ym').'-'.str_pad($i, 3, '0', STR_PAD_LEFT).'-'.rand(10,99),
                    'supplier_id' => $supplier->id, 'invoice_date' => $invDate->format('Y-m-d'), 'due_date' => $invDate->copy()->addDays(14)->format('Y-m-d'),
                    'total_amount' => $amount, 'status' => 'approved', 'branch_id' => $branch->id, 'created_by' => $admin->id
                ]);

                $expenseAccount = ChartOfAccount::where('account_code', '6-2000')->first();
                $this->journalEngine->post([
                    ['account_id' => $expenseAccount->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Biaya Operasional'],
                    ['account_id' => $apAccount->id, 'debit' => 0, 'credit' => $amount, 'description' => 'Hutang Usaha']
                ], $suppInv, [
                    'transaction_date' => $suppInv->invoice_date, 'description' => 'Tagihan Supplier ' . $suppInv->invoice_number,
                    'branch_id' => $branch->id, 'fiscal_period_id' => $period->id, 'currency_id' => $currency->id, 'created_by' => $admin->id
                ]);

                $payChance = ($month > 0) ? 90 : 20;
                if (rand(1, 100) <= $payChance) {
                    $payDate = $invDate->copy()->addDays(rand(2, 10));
                    $payPeriod = $getPeriod($payDate);
                    if($payPeriod) {
                        $payment = ApPayment::create([
                            'payment_number' => 'PAY-'.$payDate->format('Ym').'-'.str_pad($i, 3, '0', STR_PAD_LEFT).'-'.rand(10,99),
                            'supplier_invoice_id' => $suppInv->id, 'payment_date' => $payDate->format('Y-m-d'), 'amount' => $amount,
                            'bank_account_id' => $accountMandiri->id, 'created_by' => $admin->id
                        ]);
                        $suppInv->update(['status' => 'paid']);
                        $this->journalEngine->post([
                            ['account_id' => $apAccount->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Pelunasan Hutang'],
                            ['account_id' => $kasAkun->id, 'debit' => 0, 'credit' => $amount, 'description' => 'Kas Keluar']
                        ], $payment, [
                            'transaction_date' => $payment->payment_date, 'description' => 'Pembayaran Hutang ' . $suppInv->invoice_number,
                            'branch_id' => $branch->id, 'fiscal_period_id' => $payPeriod->id, 'currency_id' => $currency->id, 'created_by' => $admin->id
                        ]);
                    }
                }
            }

            // Routine Cash Bank out for Payroll & Electricity every month
            $gajiDate = $baseDate->copy()->endOfMonth()->startOfDay();
            $gajiPeriod = $getPeriod($gajiDate);
            if($gajiPeriod) {
                $gajiTransaction = new Transaction([
                    'transaction_number' => 'JV-' . $gajiDate->format('Ym') . '-G'.rand(10,99),
                    'transaction_date' => $gajiDate->format('Y-m-d'), 'description' => 'Pembayaran Gaji Karyawan',
                    'branch_id' => $branch->id, 'fiscal_period_id' => $gajiPeriod->id, 'currency_id' => $currency->id,
                    'status' => 'posted', 'created_by' => $admin->id, 'posted_by' => $admin->id, 'posted_at' => Carbon::now()
                ]);
                $gajiTransaction->save();

                $cbTransaction = CashBankTransaction::create([
                    'transaction_id' => $gajiTransaction->id, 'bank_account_id' => $accountBca->id,
                    'type' => 'cash_out', 'amount' => 45000000, 'category_id' => $catGaji->id, 'branch_id' => $branch->id, 'created_by' => $admin->id
                ]);
                
                $this->journalEngine->post([
                    ['account_id' => ChartOfAccount::where('account_code', '6-1000')->first()->id, 'debit' => 45000000, 'credit' => 0, 'description' => 'Beban Gaji'],
                    ['account_id' => $kasAkun->id, 'debit' => 0, 'credit' => 45000000, 'description' => 'Kas Keluar']
                ], $gajiTransaction, [
                    'transaction_date' => $gajiTransaction->transaction_date, 'description' => $gajiTransaction->description,
                    'branch_id' => $branch->id, 'fiscal_period_id' => $gajiPeriod->id, 'currency_id' => $currency->id, 'created_by' => $admin->id
                ]);
            }
        }

        // 7. Fixed Assets
        for($i=1; $i<=5; $i++) {
            FixedAsset::create([
                'asset_code' => 'AST-00'.$i, 'asset_name' => 'Kendaraan Operasional ' . $i, 'category' => 'Vehicles',
                'acquisition_date' => Carbon::now()->subMonths(rand(2, 10))->format('Y-m-d'),
                'cost_basis' => rand(150, 300) * 1000000, 'useful_life_months' => 60, 'salvage_value' => 50000000,
                'asset_account_id' => ChartOfAccount::where('account_code', '1-1120')->first()->id,
                'accum_depreciation_account_id' => ChartOfAccount::where('account_code', '1-1120')->first()->id,
                'expense_account_id' => ChartOfAccount::where('account_code', '6-3000')->first()->id,
                'branch_id' => $branch->id, 'status' => 'active'
            ]);
        }

        // 8. Budgets
        $fy = \App\Models\Accounting\FiscalYear::first();
        $gajiAccId = ChartOfAccount::where('account_code', '6-1000')->first()->id;
        $opAccId = ChartOfAccount::where('account_code', '6-2000')->first()->id;

        foreach($costCenters as $cc) {
            Budget::create([
                'cost_center_id' => $cc->id, 'fiscal_year' => $fy->year, 'period_type' => 'annual',
                'account_id' => $gajiAccId, 'budgeted_amount' => 500000000
            ]);
            Budget::create([
                'cost_center_id' => $cc->id, 'fiscal_year' => $fy->year, 'period_type' => 'annual',
                'account_id' => $opAccId, 'budgeted_amount' => 150000000
            ]);
        }
    }
}
