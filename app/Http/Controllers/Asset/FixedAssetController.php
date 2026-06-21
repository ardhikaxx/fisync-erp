<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset\FixedAsset;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Branch;
use Illuminate\Support\Facades\Auth;

class FixedAssetController extends Controller
{
    public function index()
    {
        $assets = FixedAsset::orderBy('acquisition_date', 'desc')->paginate(15);
        return view('asset.index', compact('assets'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        // Asset accounts (e.g. 1-3000 series for Fixed Assets)
        $assetAccounts = ChartOfAccount::where('account_type', 'asset')->where('is_postable', true)->get();
        // Expense accounts for depreciation (e.g. 6-3000 series)
        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')->where('is_postable', true)->get();

        return view('asset.create', compact('branches', 'assetAccounts', 'expenseAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_code' => 'required|string|unique:fixed_assets,asset_code',
            'asset_name' => 'required|string',
            'acquisition_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1',
            'salvage_value' => 'required|numeric|min:0',
            'depreciation_method' => 'required|in:straight_line,declining_balance',
            'asset_account_id' => 'required|exists:chart_of_accounts,id',
            'accumulated_depreciation_account_id' => 'required|exists:chart_of_accounts,id',
            'depreciation_expense_account_id' => 'required|exists:chart_of_accounts,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $asset = new FixedAsset();
        $asset->asset_code = $request->asset_code;
        $asset->asset_name = $request->asset_name;
        $asset->category = 'General'; // Default category
        $asset->acquisition_date = $request->acquisition_date;
        $asset->cost_basis = $request->acquisition_cost;
        $asset->salvage_value = $request->salvage_value;
        $asset->useful_life_months = $request->useful_life_years * 12;
        $asset->asset_account_id = $request->asset_account_id;
        $asset->accum_depreciation_account_id = $request->accumulated_depreciation_account_id;
        $asset->expense_account_id = $request->depreciation_expense_account_id;
        $asset->branch_id = $request->branch_id;
        $asset->status = 'active';
        $asset->save();

        return redirect()->route('assets.index')->with('success', 'Data Aset Tetap berhasil disimpan.');
    }
}
