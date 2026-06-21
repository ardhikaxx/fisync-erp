<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Accounting\Branch;
use App\Models\Accounting\Department;
use App\Models\Accounting\Currency;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\FiscalPeriod;
use App\Models\CashBank\Bank;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles and Permissions
        $roles = [
            'Super Admin', 'Direktur', 'Finance Manager', 'Accountant', 
            'Finance Staff', 'Cashier', 'Auditor Internal', 'Manager Divisi'
        ];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
        
        $permissions = [
            'manage_users', 'manage_roles', 'manage_branches', 'manage_coa',
            'create_journal', 'post_journal', 'reverse_journal',
            'close_period', 'override_closed_period',
            'view_reports', 'manage_budget'
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $superAdminRole->syncPermissions(Permission::all());

        // 2. Branch & Department
        $hq = Branch::firstOrCreate(
            ['branch_code' => 'HQ-01'],
            ['branch_name' => 'Kantor Pusat', 'is_head_office' => true, 'address' => 'Jakarta']
        );
        $dept = Department::firstOrCreate(
            ['name' => 'Finance & Accounting', 'branch_id' => $hq->id]
        );

        // 3. User
        $admin = User::firstOrCreate(
            ['email' => 'admin@finsync.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'branch_id' => $hq->id,
                'department_id' => $dept->id,
                'is_active' => true
            ]
        );
        if (!$admin->hasRole('Super Admin')) {
            $admin->assignRole('Super Admin');
        }

        // 4. Currency
        $idr = Currency::firstOrCreate(
            ['currency_code' => 'IDR'],
            ['currency_name' => 'Rupiah', 'symbol' => 'Rp', 'is_base_currency' => true]
        );

        // 5. Fiscal Year & Period
        $currentYear = date('Y');
        $fy = FiscalYear::firstOrCreate(['year' => $currentYear]);
        for ($i = 1; $i <= 12; $i++) {
            $date = Carbon::create($currentYear, $i, 1);
            FiscalPeriod::firstOrCreate(
                ['period_month' => $i, 'period_year' => $currentYear],
                [
                    'period_name' => $date->translatedFormat('F Y'),
                    'start_date' => $date->startOfMonth()->toDateString(),
                    'end_date' => $date->endOfMonth()->toDateString(),
                    'status' => 'open'
                ]
            );
        }

        // 6. Chart of Accounts (Basic)
        $coaData = [
            ['1-0000', 'Aset', 'asset', 'debit', 1, false, null],
            ['1-1000', 'Aset Lancar', 'asset', 'debit', 2, false, '1-0000'],
            ['1-1100', 'Kas & Bank', 'asset', 'debit', 3, false, '1-1000'],
            ['1-1110', 'Kas Utama', 'asset', 'debit', 4, true, '1-1100'],
            ['1-1120', 'Bank BCA', 'asset', 'debit', 4, true, '1-1100'],
            ['1-1200', 'Piutang Usaha', 'asset', 'debit', 3, true, '1-1000'],
            ['1-1300', 'Persediaan', 'asset', 'debit', 3, true, '1-1000'],
            
            ['2-0000', 'Kewajiban', 'liability', 'credit', 1, false, null],
            ['2-1000', 'Kewajiban Lancar', 'liability', 'credit', 2, false, '2-0000'],
            ['2-1100', 'Hutang Usaha', 'liability', 'credit', 3, true, '2-1000'],
            ['2-1200', 'Hutang PPN', 'liability', 'credit', 3, true, '2-1000'],
            
            ['3-0000', 'Ekuitas', 'equity', 'credit', 1, false, null],
            ['3-1000', 'Modal Disetor', 'equity', 'credit', 2, true, '3-0000'],
            ['3-2000', 'Laba Ditahan', 'equity', 'credit', 2, true, '3-0000'],
            
            ['4-0000', 'Pendapatan', 'revenue', 'credit', 1, false, null],
            ['4-1000', 'Pendapatan Usaha', 'revenue', 'credit', 2, true, '4-0000'],
            
            ['5-0000', 'Beban Pokok Penjualan', 'expense', 'debit', 1, true, null],
            
            ['6-0000', 'Beban Operasional', 'expense', 'debit', 1, false, null],
            ['6-1000', 'Beban Gaji', 'expense', 'debit', 2, true, '6-0000'],
            ['6-2000', 'Beban Listrik & Air', 'expense', 'debit', 2, true, '6-0000'],
            ['6-3000', 'Beban Penyusutan', 'expense', 'debit', 2, true, '6-0000'],
        ];

        $accountIds = [];
        foreach ($coaData as $row) {
            $parent = $row[6] ? ($accountIds[$row[6]] ?? null) : null;
            $acc = ChartOfAccount::firstOrCreate(
                ['account_code' => $row[0]],
                [
                    'account_name' => $row[1],
                    'account_type' => $row[2],
                    'normal_balance' => $row[3],
                    'level' => $row[4],
                    'is_postable' => $row[5],
                    'parent_id' => $parent,
                    'branch_id' => $hq->id
                ]
            );
            $accountIds[$row[0]] = $acc->id;
        }

        // 7. Banks
        Bank::firstOrCreate(['code' => 'BCA', 'name' => 'Bank Central Asia']);
        Bank::firstOrCreate(['code' => 'MANDIRI', 'name' => 'Bank Mandiri']);
        Bank::firstOrCreate(['code' => 'BSI', 'name' => 'Bank Syariah Indonesia']);

        $this->call(ContactSeeder::class);
    }
}
