<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Support\Facades\DB;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        // Get all accounts, ordered by code
        $accounts = ChartOfAccount::with('parent')
            ->orderBy('account_code')
            ->get();
            
        return view('accounting.coa.index', compact('accounts'));
    }

    public function create()
    {
        $parentAccounts = ChartOfAccount::where('is_postable', false)->get();
        return view('accounting.coa.create', compact('parentAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:50|unique:chart_of_accounts',
            'account_name' => 'required|string|max:150',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
            'is_postable' => 'boolean',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_active' => 'boolean'
        ]);

        $validated['is_postable'] = $request->has('is_postable');
        $validated['is_active'] = $request->has('is_active');
        $validated['level'] = 1;

        if ($validated['parent_id']) {
            $parent = ChartOfAccount::find($validated['parent_id']);
            $validated['level'] = $parent->level + 1;
        }

        ChartOfAccount::create($validated);

        return redirect()->route('coa.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    // Edit and Update methods can be added similarly
}
