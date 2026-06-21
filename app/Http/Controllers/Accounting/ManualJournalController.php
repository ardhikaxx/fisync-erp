<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\Transaction;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\Currency;
use App\Models\Accounting\Branch;
use App\Services\Accounting\JournalEngineService;
use Illuminate\Support\Facades\Auth;

class ManualJournalController extends Controller
{
    protected $journalEngine;

    public function __construct(JournalEngineService $journalEngine)
    {
        $this->journalEngine = $journalEngine;
    }

    public function index()
    {
        $transactions = Transaction::with('journalEntries.account')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);
            
        return view('accounting.journals.index', compact('transactions'));
    }

    public function create()
    {
        $accounts = ChartOfAccount::where('is_postable', true)->orderBy('account_code')->get();
        $currencies = Currency::all();
        $branches = Branch::where('is_active', true)->get();
        
        return view('accounting.journals.create', compact('accounts', 'currencies', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'description' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'currency_id' => 'required|exists:currencies,id',
            'entries' => 'required|array|min:2',
            'entries.*.account_id' => 'required|exists:chart_of_accounts,id',
            'entries.*.debit' => 'nullable|numeric|min:0',
            'entries.*.credit' => 'nullable|numeric|min:0',
        ]);

        $date = $request->transaction_date;
        $period = FiscalPeriod::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if (!$period || $period->status == 'closed') {
            return back()->withInput()->withErrors(['transaction_date' => 'Periode fiskal untuk tanggal ini tidak ditemukan atau sudah ditutup.']);
        }

        $lines = [];
        foreach ($request->entries as $entry) {
            if (($entry['debit'] ?? 0) > 0 || ($entry['credit'] ?? 0) > 0) {
                $lines[] = [
                    'account_id' => $entry['account_id'],
                    'debit' => $entry['debit'] ?? 0,
                    'credit' => $entry['credit'] ?? 0,
                    'description' => $entry['description'] ?? null,
                ];
            }
        }

        try {
            $this->journalEngine->post($lines, null, [
                'transaction_date' => $request->transaction_date,
                'description' => $request->description,
                'branch_id' => $request->branch_id,
                'fiscal_period_id' => $period->id,
                'currency_id' => $request->currency_id,
                'exchange_rate' => 1, // simplified
                'created_by' => Auth::id()
            ]);

            return redirect()->route('journals.index')->with('success', 'Jurnal berhasil diposting.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
