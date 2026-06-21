<?php

namespace App\Http\Controllers\AR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AR\Invoice;
use App\Models\AR\Receipt;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\Currency;
use App\Services\Accounting\JournalEngineService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceiptController extends Controller
{
    protected $journalEngine;

    public function __construct(JournalEngineService $journalEngine)
    {
        $this->journalEngine = $journalEngine;
    }

    public function create(Invoice $invoice)
    {
        if ($invoice->status == 'paid') {
            return redirect()->route('ar.invoices.index')->with('error', 'Invoice sudah lunas.');
        }

        $cashAccounts = ChartOfAccount::where('account_code', 'like', '1-11%')
            ->where('is_postable', true)->get();

        return view('ar.receipts.create', compact('invoice', 'cashAccounts'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        $request->validate([
            'receipt_date' => 'required|date',
            'amount' => 'required|numeric|min:1|max:' . $invoice->balance_due,
            'cash_account_id' => 'required|exists:chart_of_accounts,id',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $date = $request->receipt_date;
        $period = FiscalPeriod::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if (!$period || $period->status == 'closed') {
            return back()->withInput()->withErrors(['receipt_date' => 'Periode fiskal untuk tanggal ini tidak ditemukan atau sudah ditutup.']);
        }

        DB::beginTransaction();
        try {
            $receiptNumber = 'RCP-' . date('Ym', strtotime($date)) . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $receipt = new Receipt();
            $receipt->receipt_number = $receiptNumber;
            $receipt->invoice_id = $invoice->id;
            $receipt->receipt_date = $request->receipt_date;
            $receipt->amount = $request->amount;
            $receipt->reference_number = $request->reference_number;
            $receipt->created_by = Auth::id();
            $receipt->save();

            // Update Invoice Balance
            $invoice->paid_amount += $request->amount;
            $invoice->balance_due -= $request->amount;
            if ($invoice->balance_due <= 0) {
                $invoice->status = 'paid';
            } else {
                $invoice->status = 'partial';
            }
            $invoice->save();

            // Journal Entry
            // Debit: Kas/Bank
            // Kredit: Piutang Usaha (1-1200)
            $arAccount = ChartOfAccount::where('account_code', '1-1200')->firstOrFail();

            $lines = [
                [
                    'account_id' => $request->cash_account_id,
                    'debit' => $request->amount,
                    'credit' => 0,
                    'description' => 'Penerimaan Piutang Inv ' . $invoice->invoice_number,
                ],
                [
                    'account_id' => $arAccount->id,
                    'debit' => 0,
                    'credit' => $request->amount,
                    'description' => 'Pembayaran Piutang Inv ' . $invoice->invoice_number,
                ]
            ];

            $currencyId = Currency::where('is_base_currency', true)->first()->id;

            $transaction = $this->journalEngine->post($lines, $receipt, [
                'transaction_date' => $request->receipt_date,
                'description' => 'Penerimaan Pembayaran Piutang (Ref: ' . $receiptNumber . ')',
                'branch_id' => $invoice->branch_id,
                'fiscal_period_id' => $period->id,
                'currency_id' => $currencyId,
                'exchange_rate' => 1,
                'created_by' => Auth::id()
            ]);

            $receipt->transaction_id = $transaction->id;
            $receipt->save();

            DB::commit();
            return redirect()->route('ar.invoices.index')->with('success', 'Pembayaran Piutang berhasil dicatat dan dijurnal.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
