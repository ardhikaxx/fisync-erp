<?php

namespace App\Http\Controllers\AP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AP\SupplierInvoice;
use App\Models\AP\Supplier;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\Branch;
use App\Models\Accounting\Currency;
use App\Services\Accounting\JournalEngineService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    protected $journalEngine;

    public function __construct(JournalEngineService $journalEngine)
    {
        $this->journalEngine = $journalEngine;
    }

    public function index()
    {
        $invoices = SupplierInvoice::with('supplier')
            ->orderBy('invoice_date', 'desc')
            ->paginate(15);
        return view('ap.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $branches = Branch::where('is_active', true)->get();
        // Beban/Inventory accounts (e.g. 5-0000 or 6-0000 series, for simplicity we fetch all expenses and assets)
        $expenseAccounts = ChartOfAccount::whereIn('account_type', ['expense', 'asset'])->where('is_postable', true)->get();
        
        return view('ap.invoices.create', compact('suppliers', 'branches', 'expenseAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:100', // Nomor invoice dari supplier
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'amount' => 'required|numeric|min:1',
            'expense_account_id' => 'required|exists:chart_of_accounts,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $date = $request->invoice_date;
        $period = FiscalPeriod::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if (!$period || $period->status == 'closed') {
            return back()->withInput()->withErrors(['invoice_date' => 'Periode fiskal untuk tanggal ini tidak ditemukan atau sudah ditutup.']);
        }

        DB::beginTransaction();
        try {
            $invoice = new SupplierInvoice();
            $invoice->invoice_number = $request->invoice_number;
            $invoice->supplier_id = $request->supplier_id;
            $invoice->invoice_date = $request->invoice_date;
            $invoice->due_date = $request->due_date;
            $invoice->total_amount = $request->amount;
            $invoice->status = 'approved'; // Langsung approved untuk simplicity
            $invoice->branch_id = $request->branch_id;
            $invoice->created_by = Auth::id();
            $invoice->save();

            // Journal Entry
            // Debit: Beban/Persediaan
            // Kredit: Hutang Usaha (2-1100)
            $apAccount = ChartOfAccount::where('account_code', '2-1100')->firstOrFail();

            $lines = [
                [
                    'account_id' => $request->expense_account_id,
                    'debit' => $request->amount,
                    'credit' => 0,
                    'description' => 'Pembelian dari Supplier (Inv: ' . $request->invoice_number . ')',
                ],
                [
                    'account_id' => $apAccount->id,
                    'debit' => 0,
                    'credit' => $request->amount,
                    'description' => 'Hutang Usaha (Inv: ' . $request->invoice_number . ')',
                ]
            ];

            $currencyId = Currency::where('is_base_currency', true)->first()->id;

            $transaction = $this->journalEngine->post($lines, $invoice, [
                'transaction_date' => $request->invoice_date,
                'description' => 'Pembelian ke Supplier (Invoice: ' . $request->invoice_number . ')',
                'branch_id' => $request->branch_id,
                'fiscal_period_id' => $period->id,
                'currency_id' => $currencyId,
                'exchange_rate' => 1,
                'created_by' => Auth::id()
            ]);

            $invoice->transaction_id = $transaction->id;
            $invoice->save();

            DB::commit();
            return redirect()->route('ap.invoices.index')->with('success', 'Invoice Hutang berhasil dicatat dan dijurnal.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
