<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset\FixedAsset;
use App\Models\Accounting\Transaction;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\Currency;
use App\Services\Accounting\JournalEngineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DepreciationController extends Controller
{
    protected $journalEngine;

    public function __construct(JournalEngineService $journalEngine)
    {
        $this->journalEngine = $journalEngine;
    }

    public function run(Request $request)
    {
        $date = Carbon::parse($request->input('date', date('Y-m-t'))); // Default to end of current month
        $period = FiscalPeriod::where('start_date', '<=', $date->format('Y-m-d'))
                              ->where('end_date', '>=', $date->format('Y-m-d'))
                              ->first();

        if (!$period) {
            return back()->withErrors(['gagal' => 'Periode fiskal untuk tanggal ini tidak ditemukan.']);
        }
        if ($period->status === 'closed') {
            return back()->withErrors(['gagal' => 'Periode fiskal sudah ditutup.']);
        }

        $assets = FixedAsset::where('status', 'active')->get();
        $baseCurrency = Currency::where('is_base_currency', true)->first();

        if ($assets->isEmpty()) {
            return back()->with('success', 'Tidak ada aset aktif yang perlu disusutkan.');
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'transaction_number' => 'DEP-' . $date->format('Ym') . '-' . rand(100, 999),
                'transaction_date' => $date->format('Y-m-d'),
                'description' => 'Penyusutan Aset Tetap Bulan ' . $date->translatedFormat('F Y'),
                'branch_id' => auth()->user()->branch_id ?? 1,
                'fiscal_period_id' => $period->id,
                'currency_id' => $baseCurrency->id,
                'status' => 'posted',
                'created_by' => auth()->id() ?? 1,
                'posted_by' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            $entries = [];
            $totalDepreciation = 0;

            foreach ($assets as $asset) {
                // Simplified Straight Line calculation
                $depreciationPerMonth = ($asset->cost_basis - $asset->salvage_value) / $asset->useful_life_months;
                
                // Add Expense Entry (Debit)
                $entries[] = [
                    'account_id' => $asset->expense_account_id,
                    'debit' => $depreciationPerMonth,
                    'credit' => 0,
                    'description' => 'Beban Penyusutan: ' . $asset->asset_name
                ];

                // Add Accum Depreciation Entry (Credit)
                $entries[] = [
                    'account_id' => $asset->accum_depreciation_account_id,
                    'debit' => 0,
                    'credit' => $depreciationPerMonth,
                    'description' => 'Akm. Penyusutan: ' . $asset->asset_name
                ];
                
                $totalDepreciation += $depreciationPerMonth;
            }

            $this->journalEngine->post($entries, $transaction, [
                'transaction_date' => $transaction->transaction_date,
                'description' => $transaction->description,
                'branch_id' => $transaction->branch_id,
                'fiscal_period_id' => $period->id,
                'currency_id' => $baseCurrency->id,
                'created_by' => auth()->id() ?? 1,
            ]);

            DB::commit();
            return back()->with('success', 'Penyusutan aset bulan ' . $date->translatedFormat('F Y') . ' berhasil diposting. Total Beban: Rp ' . number_format($totalDepreciation, 2, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['gagal' => 'Gagal melakukan penyusutan: ' . $e->getMessage()]);
        }
    }
}
