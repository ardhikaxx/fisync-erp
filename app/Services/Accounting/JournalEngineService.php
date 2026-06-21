<?php

namespace App\Services\Accounting;

use App\Models\Accounting\Transaction;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use Exception;

class JournalEngineService
{
    /**
     * Posting journal entries.
     * 
     * @param array $lines Array of ['account_id', 'cost_center_id', 'debit', 'credit', 'description']
     * @param mixed $sourceModel
     * @param array $metadata ['transaction_date', 'description', 'branch_id', 'fiscal_period_id', 'currency_id', 'exchange_rate', 'created_by']
     */
    public function post(array $lines, $sourceModel, array $metadata)
    {
        // 1. Validasi Balance
        $totalDebit = 0;
        $totalCredit = 0;
        
        foreach ($lines as $line) {
            $totalDebit += floatval($line['debit'] ?? 0);
            $totalCredit += floatval($line['credit'] ?? 0);
        }

        // Hindari floating point precision issues
        if (abs($totalDebit - $totalCredit) > 0.001) {
            throw new Exception("UnbalancedJournalException: Total Debit (Rp " . number_format($totalDebit, 2) . ") tidak sama dengan Total Kredit (Rp " . number_format($totalCredit, 2) . ")");
        }

        if ($totalDebit <= 0) {
            throw new Exception("InvalidJournalException: Jurnal harus memiliki nilai lebih dari nol.");
        }

        return DB::transaction(function () use ($lines, $sourceModel, $metadata) {
            // Generate unique transaction number
            $trxNumber = $this->generateTransactionNumber($metadata['transaction_date']);
            $exchangeRate = $metadata['exchange_rate'] ?? 1;

            $transaction = new Transaction();
            $transaction->transaction_number = $trxNumber;
            $transaction->transaction_date = $metadata['transaction_date'];
            $transaction->description = $metadata['description'];
            $transaction->source_type = $sourceModel ? get_class($sourceModel) : null;
            $transaction->source_id = $sourceModel ? $sourceModel->id : null;
            $transaction->branch_id = $metadata['branch_id'];
            $transaction->fiscal_period_id = $metadata['fiscal_period_id'];
            $transaction->currency_id = $metadata['currency_id'];
            $transaction->exchange_rate = $exchangeRate;
            $transaction->status = 'posted';
            $transaction->created_by = $metadata['created_by'];
            $transaction->posted_by = $metadata['created_by'];
            $transaction->posted_at = now();
            $transaction->save();

            foreach ($lines as $line) {
                // Verifikasi akun is_postable
                $account = ChartOfAccount::find($line['account_id']);
                if (!$account || !$account->is_postable) {
                    throw new Exception("InvalidAccountException: Akun dengan ID {$line['account_id']} tidak valid atau tidak dapat diposting (mungkin akun header).");
                }

                $entry = new JournalEntry();
                $entry->transaction_id = $transaction->id;
                $entry->account_id = $line['account_id'];
                $entry->cost_center_id = $line['cost_center_id'] ?? null;
                
                $debit = floatval($line['debit'] ?? 0);
                $credit = floatval($line['credit'] ?? 0);
                
                $entry->debit = $debit;
                $entry->credit = $credit;
                // Calculate base values
                $entry->debit_base = $debit * $exchangeRate;
                $entry->credit_base = $credit * $exchangeRate;
                $entry->description = $line['description'] ?? null;
                $entry->save();
            }

            return $transaction;
        });
    }

    /**
     * Reverse an existing posted transaction
     */
    public function reverse(int $transactionId, string $reason, int $userId)
    {
        return DB::transaction(function () use ($transactionId, $reason, $userId) {
            $originalTrx = Transaction::with('journalEntries')->findOrFail($transactionId);
            
            if ($originalTrx->status !== 'posted') {
                throw new Exception("TransactionStatusException: Hanya transaksi berstatus 'posted' yang dapat di-reverse.");
            }

            $reversalTrx = new Transaction();
            $reversalTrx->transaction_number = $this->generateTransactionNumber(now()->toDateString());
            $reversalTrx->transaction_date = now()->toDateString();
            $reversalTrx->description = "Reversal: " . $reason . " (Ref: " . $originalTrx->transaction_number . ")";
            $reversalTrx->source_type = $originalTrx->source_type;
            $reversalTrx->source_id = $originalTrx->source_id;
            $reversalTrx->branch_id = $originalTrx->branch_id;
            $reversalTrx->fiscal_period_id = $originalTrx->fiscal_period_id; // Seharusnya periode saat ini, asumsikan sama atau sesuaikan
            $reversalTrx->currency_id = $originalTrx->currency_id;
            $reversalTrx->exchange_rate = $originalTrx->exchange_rate;
            $reversalTrx->status = 'posted';
            $reversalTrx->created_by = $userId;
            $reversalTrx->posted_by = $userId;
            $reversalTrx->posted_at = now();
            $reversalTrx->reversal_of_id = $originalTrx->id;
            $reversalTrx->save();

            foreach ($originalTrx->journalEntries as $originalEntry) {
                $entry = new JournalEntry();
                $entry->transaction_id = $reversalTrx->id;
                $entry->account_id = $originalEntry->account_id;
                $entry->cost_center_id = $originalEntry->cost_center_id;
                
                // SWAP Debit dan Kredit
                $entry->debit = $originalEntry->credit;
                $entry->credit = $originalEntry->debit;
                $entry->debit_base = $originalEntry->credit_base;
                $entry->credit_base = $originalEntry->debit_base;
                $entry->description = "Reversal of " . ($originalEntry->description ?? 'entry');
                $entry->save();
            }

            $originalTrx->status = 'reversed';
            $originalTrx->save();

            return $reversalTrx;
        });
    }

    private function generateTransactionNumber($date)
    {
        $parsedDate = \Carbon\Carbon::parse($date);
        $prefix = "TRX-" . $parsedDate->format('Ym') . "-";
        
        $latest = Transaction::where('transaction_number', 'like', $prefix . '%')
            ->orderBy('transaction_number', 'desc')
            ->first();

        if (!$latest) {
            return $prefix . "00001";
        }

        $sequence = intval(substr($latest->transaction_number, -5));
        $next = str_pad($sequence + 1, 5, "0", STR_PAD_LEFT);
        
        return $prefix . $next;
    }
}
