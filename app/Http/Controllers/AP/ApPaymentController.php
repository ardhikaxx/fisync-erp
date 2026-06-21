<?php

namespace App\Http\Controllers\AP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AP\SupplierInvoice;
use App\Models\AP\ApPayment;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\Currency;
use App\Services\Accounting\JournalEngineService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApPaymentController extends Controller
{
    protected $journalEngine;

    public function __construct(JournalEngineService $journalEngine)
    {
        $this->journalEngine = $journalEngine;
    }

    public function create(SupplierInvoice $invoice)
    {
        if ($invoice->status == 'paid') {
            return redirect()->route('ap.invoices.index')->with('error', 'Invoice supplier sudah lunas.');
        }

        $cashAccounts = ChartOfAccount::where('account_code', 'like', '1-11%')
            ->where('is_postable', true)->get();

        return view('ap.payments.create', compact('invoice', 'cashAccounts'));
    }

    public function store(Request $request, SupplierInvoice $invoice)
    {
        // For simplicity, we assume one full payment
        $request->validate([
            'payment_date' => 'required|date',
            'cash_account_id' => 'required|exists:chart_of_accounts,id',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $date = $request->payment_date;
        $period = FiscalPeriod::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if (!$period || $period->status == 'closed') {
            return back()->withInput()->withErrors(['payment_date' => 'Periode fiskal untuk tanggal ini tidak ditemukan atau sudah ditutup.']);
        }

        DB::beginTransaction();
        try {
            $paymentNumber = 'PAY-' . date('Ym', strtotime($date)) . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $amount = $invoice->total_amount; // simplified full payment

            $payment = new ApPayment();
            $payment->payment_number = $paymentNumber;
            $payment->supplier_invoice_id = $invoice->id;
            $payment->payment_date = $request->payment_date;
            $payment->amount = $amount;
            $payment->reference_number = $request->reference_number;
            $payment->created_by = Auth::id();
            $payment->save();

            // Update Invoice Status
            $invoice->status = 'paid';
            $invoice->save();

            // Journal Entry
            // Debit: Hutang Usaha (2-1100)
            // Kredit: Kas/Bank
            $apAccount = ChartOfAccount::where('account_code', '2-1100')->firstOrFail();

            $lines = [
                [
                    'account_id' => $apAccount->id,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'Pembayaran Hutang Inv ' . $invoice->invoice_number,
                ],
                [
                    'account_id' => $request->cash_account_id,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'Pelunasan Hutang Inv ' . $invoice->invoice_number,
                ]
            ];

            $currencyId = Currency::where('is_base_currency', true)->first()->id;

            $transaction = $this->journalEngine->post($lines, $payment, [
                'transaction_date' => $request->payment_date,
                'description' => 'Pembayaran Hutang Supplier (Ref: ' . $paymentNumber . ')',
                'branch_id' => $invoice->branch_id,
                'fiscal_period_id' => $period->id,
                'currency_id' => $currencyId,
                'exchange_rate' => 1,
                'created_by' => Auth::id()
            ]);

            $payment->transaction_id = $transaction->id;
            $payment->save();

            DB::commit();
            return redirect()->route('ap.invoices.index')->with('success', 'Pembayaran Hutang berhasil dicatat dan dijurnal.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
