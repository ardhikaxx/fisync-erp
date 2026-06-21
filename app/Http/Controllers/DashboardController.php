<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\Transaction;
use App\Models\AR\Invoice;
use App\Models\AP\SupplierInvoice;

class DashboardController extends Controller
{
    public function index()
    {
        // 4 KPI Card Data
        // 1. Total Kas & Bank (Akun 1-1100 series)
        $totalKasBank = JournalEntry::whereHas('account', function($q) {
            $q->where('account_code', 'like', '1-11%');
        })->selectRaw('SUM(debit_base) - SUM(credit_base) as balance')->value('balance') ?? 0;

        // 2. Piutang Belum Tertagih
        $piutangUnpaid = Invoice::whereNotIn('status', ['paid', 'void'])->sum('balance_due');

        // 3. Hutang Jatuh Tempo (Status approved tapi belum dibayar lunas)
        $hutangUnpaid = SupplierInvoice::whereIn('status', ['matched', 'approved'])->sum('total_amount'); // Simplified

        // 4. Laba Bersih Bulan Ini (Pendapatan - Beban)
        $currentMonth = date('m');
        $currentYear = date('Y');
        
        $pendapatan = JournalEntry::whereHas('transaction', function($q) use ($currentMonth, $currentYear) {
            $q->whereMonth('transaction_date', $currentMonth)->whereYear('transaction_date', $currentYear);
        })->whereHas('account', function($q) {
            $q->where('account_type', 'revenue');
        })->selectRaw('SUM(credit_base) - SUM(debit_base) as balance')->value('balance') ?? 0;

        $beban = JournalEntry::whereHas('transaction', function($q) use ($currentMonth, $currentYear) {
            $q->whereMonth('transaction_date', $currentMonth)->whereYear('transaction_date', $currentYear);
        })->whereHas('account', function($q) {
            $q->where('account_type', 'expense');
        })->selectRaw('SUM(debit_base) - SUM(credit_base) as balance')->value('balance') ?? 0;

        $labaBersih = $pendapatan - $beban;

        // Tabel Data
        $recentInvoices = Invoice::whereNotIn('status', ['paid', 'void'])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalKasBank', 'piutangUnpaid', 'hutangUnpaid', 'labaBersih', 'recentInvoices'
        ));
    }
}
