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
        $budgets = Budget::with(['account', 'costCenter'])
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

        $fy = FiscalYear::find($request->fiscal_year_id);

        $budget = new Budget();
        $budget->fiscal_year = $fy->year;
        $budget->period_type = 'annual';
        $budget->account_id = $request->account_id;
        $budget->cost_center_id = $request->cost_center_id;
        $budget->budgeted_amount = $request->annual_budget;
        
        $budget->save();

        return redirect()->route('budgets.index')->with('success', 'Anggaran berhasil dibuat.');
    }
}
