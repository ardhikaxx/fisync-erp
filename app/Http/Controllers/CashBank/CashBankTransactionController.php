<?php

namespace App\Http\Controllers\CashBank;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashBank\CashBankTransaction;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalPeriod;
use App\Models\CashBank\Bank;
use App\Models\CashBank\TransactionCategory;
use App\Models\Accounting\Branch;
use App\Models\Accounting\Currency;
use App\Services\Accounting\JournalEngineService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashBankTransactionController extends Controller
{
    protected $journalEngine;

    public function __construct(JournalEngineService $journalEngine)
    {
        $this->journalEngine = $journalEngine;
    }

    public function index()
    {
        $transactions = CashBankTransaction::with(['bankAccount', 'transactionCategory'])
            ->orderBy('transaction_date', 'desc')
            ->paginate(15);

        return view('cashbank.index', compact('transactions'));
    }

    public function create()
    {
        // For simplicity, we directly select from COA mapped as Cash/Bank (e.g. 1-11 series)
        $cashAccounts = ChartOfAccount::where('account_code', 'like', '1-11%')
            ->where('is_postable', true)->get();
            
        $offsetAccounts = ChartOfAccount::where('is_postable', true)->get(); // Account for the other side of transaction
        
        $categories = TransactionCategory::all(); // Assuming we seeded some, or empty is fine
        $branches = Branch::where('is_active', true)->get();
        $currencies = Currency::all();

        return view('cashbank.create', compact('cashAccounts', 'offsetAccounts', 'categories', 'branches', 'currencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_type' => 'required|in:in,out',
            'transaction_date' => 'required|date',
            'cash_account_id' => 'required|exists:chart_of_accounts,id',
            'offset_account_id' => 'required|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $date = $request->transaction_date;
        $period = FiscalPeriod::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if (!$period || $period->status == 'closed') {
            return back()->withInput()->withErrors(['transaction_date' => 'Periode fiskal untuk tanggal ini tidak ditemukan atau sudah ditutup.']);
        }

        DB::beginTransaction();
        try {
            // Generate Transaction Number for CashBank
            $trxNumber = 'CB-' . date('Ym', strtotime($date)) . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $cashBankTrx = new CashBankTransaction();
            $cashBankTrx->transaction_number = $trxNumber;
            $cashBankTrx->transaction_type = $request->transaction_type;
            $cashBankTrx->transaction_date = $request->transaction_date;
            $cashBankTrx->bank_account_id = null; // simplified, we use COA directly here
            $cashBankTrx->transaction_category_id = $request->category_id ?? null;
            $cashBankTrx->amount = $request->amount;
            $cashBankTrx->reference_number = $request->reference_number;
            $cashBankTrx->description = $request->description;
            $cashBankTrx->status = 'completed';
            $cashBankTrx->created_by = Auth::id();
            $cashBankTrx->save();

            // Prepare Journal Lines
            $lines = [];
            
            if ($request->transaction_type == 'in') {
                // Kas Masuk: Debit Kas, Kredit Offset
                $lines[] = [
                    'account_id' => $request->cash_account_id,
                    'debit' => $request->amount,
                    'credit' => 0,
                    'description' => $request->description,
                ];
                $lines[] = [
                    'account_id' => $request->offset_account_id,
                    'debit' => 0,
                    'credit' => $request->amount,
                    'description' => $request->description,
                ];
            } else {
                // Kas Keluar: Debit Offset, Kredit Kas
                $lines[] = [
                    'account_id' => $request->offset_account_id,
                    'debit' => $request->amount,
                    'credit' => 0,
                    'description' => $request->description,
                ];
                $lines[] = [
                    'account_id' => $request->cash_account_id,
                    'debit' => 0,
                    'credit' => $request->amount,
                    'description' => $request->description,
                ];
            }

            // Post to Journal
            $currencyId = Currency::where('is_base_currency', true)->first()->id;

            $this->journalEngine->post($lines, $cashBankTrx, [
                'transaction_date' => $request->transaction_date,
                'description' => 'Kas/Bank: ' . $request->description,
                'branch_id' => $request->branch_id,
                'fiscal_period_id' => $period->id,
                'currency_id' => $currencyId,
                'exchange_rate' => 1,
                'created_by' => Auth::id()
            ]);

            DB::commit();
            return redirect()->route('cashbank.index')->with('success', 'Transaksi Kas & Bank berhasil dicatat dan dijurnal.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
