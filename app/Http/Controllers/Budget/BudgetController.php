<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Budget\Budget;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Budget\CostCenter;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index()
    {
        $budgets = Budget::with(['fiscalYear', 'account', 'costCenter'])
            ->orderBy('id', 'desc')
            ->paginate(15);
        return view('budget.index', compact('budgets'));
    }

    public function create()
    {
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get();
        $accounts = ChartOfAccount::where('account_type', 'expense')->where('is_postable', true)->get();
        $costCenters = CostCenter::where('is_active', true)->get();

        return view('budget.create', compact('fiscalYears', 'accounts', 'costCenters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'cost_center_id' => 'required|exists:cost_centers,id',
            'annual_budget' => 'required|numeric|min:0',
        ]);

        $budget = new Budget();
        $budget->fill($request->all());
        
        // Auto-distribute evenly to months for simplicity
        $monthly = $request->annual_budget / 12;
        for ($i=1; $i<=12; $i++) {
            $budget->{"month_{$i}_budget"} = $monthly;
        }
        
        $budget->created_by = Auth::id();
        $budget->save();

        return redirect()->route('budgets.index')->with('success', 'Anggaran berhasil dibuat.');
    }
}
