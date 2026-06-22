<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\FiscalYear;

class PeriodController extends Controller
{
    public function index()
    {
        $years = FiscalYear::orderBy('year', 'desc')->get();
        $periods = FiscalPeriod::orderBy('start_date', 'desc')->paginate(12);
        return view('accounting.periods.index', compact('periods', 'years'));
    }

    public function closePeriod(FiscalPeriod $period)
    {
        // Add logic to check if all necessary transactions are done (like depreciation)
        $period->update([
            'status' => 'closed',
            'closed_by' => auth()->id() ?? 1,
            'closed_at' => now(),
        ]);

        return back()->with('success', 'Periode ' . $period->period_name . ' berhasil ditutup. Transaksi baru tidak dapat ditambahkan ke periode ini.');
    }

    public function openPeriod(FiscalPeriod $period)
    {
        // Opening a period should probably require special permission
        $period->update([
            'status' => 'open',
            'closed_by' => null,
            'closed_at' => null,
        ]);

        return back()->with('success', 'Periode ' . $period->period_name . ' berhasil dibuka kembali.');
    }
}
