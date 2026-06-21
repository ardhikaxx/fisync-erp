<?php

namespace App\Http\Controllers\AR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AR\Invoice;
use App\Models\AR\Customer;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\Branch;
use App\Models\Accounting\Currency;
use App\Services\Accounting\JournalEngineService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    protected $journalEngine;

    public function __construct(JournalEngineService $journalEngine)
    {
        $this->journalEngine = $journalEngine;
    }

    public function index()
    {
        $invoices = Invoice::with('customer')
            ->orderBy('invoice_date', 'desc')
            ->paginate(15);
        return view('ar.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::all();
        $branches = Branch::where('is_active', true)->get();
        // Pendapatan accounts (e.g. 4-1000 series)
        $revenueAccounts = ChartOfAccount::where('account_type', 'revenue')->where('is_postable', true)->get();
        
        return view('ar.invoices.create', compact('customers', 'branches', 'revenueAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'amount' => 'required|numeric|min:1',
            'revenue_account_id' => 'required|exists:chart_of_accounts,id',
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
            $invNumber = 'INV-' . date('Ym', strtotime($date)) . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $invoice = new Invoice();
            $invoice->invoice_number = $invNumber;
            $invoice->customer_id = $request->customer_id;
            $invoice->invoice_date = $request->invoice_date;
            $invoice->due_date = $request->due_date;
            $invoice->subtotal = $request->amount;
            $invoice->total_amount = $request->amount;
            $invoice->balance_due = $request->amount;
            $invoice->status = 'posted';
            $invoice->branch_id = $request->branch_id;
            $invoice->created_by = Auth::id();
            $invoice->save();

            // Journal Entry
            // Debit: Piutang Usaha (1-1200)
            // Kredit: Pendapatan Usaha
            $arAccount = ChartOfAccount::where('account_code', '1-1200')->firstOrFail();

            $lines = [
                [
                    'account_id' => $arAccount->id,
                    'debit' => $request->amount,
                    'credit' => 0,
                    'description' => 'Piutang Invoice ' . $invNumber,
                ],
                [
                    'account_id' => $request->revenue_account_id,
                    'debit' => 0,
                    'credit' => $request->amount,
                    'description' => 'Pendapatan Invoice ' . $invNumber,
                ]
            ];

            $currencyId = Currency::where('is_base_currency', true)->first()->id;

            $transaction = $this->journalEngine->post($lines, $invoice, [
                'transaction_date' => $request->invoice_date,
                'description' => 'Penjualan ke Customer (Invoice: ' . $invNumber . ')',
                'branch_id' => $request->branch_id,
                'fiscal_period_id' => $period->id,
                'currency_id' => $currencyId,
                'exchange_rate' => 1,
                'created_by' => Auth::id()
            ]);

            $invoice->transaction_id = $transaction->id;
            $invoice->save();

            DB::commit();
            return redirect()->route('ar.invoices.index')->with('success', 'Invoice Piutang berhasil dibuat dan dijurnal.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
