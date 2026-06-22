<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function generalLedger(Request $request)
    {
        $accounts = ChartOfAccount::orderBy('account_code')->get();
        
        $selectedAccountId = $request->input('account_id');
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));

        $entries = collect();
        $openingBalance = 0;
        $selectedAccount = null;

        if ($selectedAccountId) {
            $selectedAccount = ChartOfAccount::find($selectedAccountId);
            
            // Calculate Opening Balance (before start date)
            $openingEntries = JournalEntry::where('account_id', $selectedAccountId)
                ->whereHas('transaction', function($q) use ($startDate) {
                    $q->where('transaction_date', '<', $startDate);
                })->get();
                
            $openingDebit = $openingEntries->sum('debit_base');
            $openingCredit = $openingEntries->sum('credit_base');
            
            if ($selectedAccount->normal_balance == 'debit') {
                $openingBalance = $openingDebit - $openingCredit;
            } else {
                $openingBalance = $openingCredit - $openingDebit;
            }

            // Get entries within date range
            $entries = JournalEntry::with(['transaction', 'transaction.currency'])
                ->where('account_id', $selectedAccountId)
                ->whereHas('transaction', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('transaction_date', [$startDate, $endDate]);
                })
                ->orderBy(function ($query) {
                    $query->select('transaction_date')
                        ->from('transactions')
                        ->whereColumn('transactions.id', 'journal_entries.transaction_id');
                })
                ->get();
        }

        return view('accounting.reports.general_ledger', compact(
            'accounts', 'entries', 'openingBalance', 'selectedAccount', 'startDate', 'endDate'
        ));
    }

    public function incomeStatement(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));

        // Revenue
        $revenues = ChartOfAccount::where('account_type', 'revenue')->orderBy('account_code')->get();
        $revenueData = [];
        $totalRevenue = 0;

        foreach ($revenues as $acc) {
            $sumCredit = JournalEntry::where('account_id', $acc->id)
                ->whereHas('transaction', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('transaction_date', [$startDate, $endDate]);
                })->sum('credit_base');
                
            $sumDebit = JournalEntry::where('account_id', $acc->id)
                ->whereHas('transaction', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('transaction_date', [$startDate, $endDate]);
                })->sum('debit_base');
                
            $net = $sumCredit - $sumDebit; // Normal balance credit
            if ($net != 0) {
                $revenueData[] = ['account' => $acc, 'balance' => $net];
                $totalRevenue += $net;
            }
        }

        // Expenses
        $expenses = ChartOfAccount::where('account_type', 'expense')->orderBy('account_code')->get();
        $expenseData = [];
        $totalExpense = 0;

        foreach ($expenses as $acc) {
            $sumDebit = JournalEntry::where('account_id', $acc->id)
                ->whereHas('transaction', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('transaction_date', [$startDate, $endDate]);
                })->sum('debit_base');
                
            $sumCredit = JournalEntry::where('account_id', $acc->id)
                ->whereHas('transaction', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('transaction_date', [$startDate, $endDate]);
                })->sum('credit_base');
                
            $net = $sumDebit - $sumCredit; // Normal balance debit
            if ($net != 0) {
                $expenseData[] = ['account' => $acc, 'balance' => $net];
                $totalExpense += $net;
            }
        }

        $netIncome = $totalRevenue - $totalExpense;

        return view('accounting.reports.income_statement', compact(
            'startDate', 'endDate', 'revenueData', 'totalRevenue', 'expenseData', 'totalExpense', 'netIncome'
        ));
    }

    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->input('as_of_date', date('Y-m-t'));

        // Function to calculate balance up to a date
        $getBalance = function($accountId, $normalBalance) use ($asOfDate) {
            $sumDebit = JournalEntry::where('account_id', $accountId)
                ->whereHas('transaction', function($q) use ($asOfDate) {
                    $q->where('transaction_date', '<=', $asOfDate);
                })->sum('debit_base');
                
            $sumCredit = JournalEntry::where('account_id', $accountId)
                ->whereHas('transaction', function($q) use ($asOfDate) {
                    $q->where('transaction_date', '<=', $asOfDate);
                })->sum('credit_base');

            return $normalBalance == 'debit' ? ($sumDebit - $sumCredit) : ($sumCredit - $sumDebit);
        };

        // Assets
        $assets = ChartOfAccount::where('account_type', 'asset')->orderBy('account_code')->get();
        $assetData = [];
        $totalAssets = 0;
        foreach ($assets as $acc) {
            $bal = $getBalance($acc->id, 'debit');
            if ($bal != 0) {
                $assetData[] = ['account' => $acc, 'balance' => $bal];
                $totalAssets += $bal;
            }
        }

        // Liabilities
        $liabilities = ChartOfAccount::where('account_type', 'liability')->orderBy('account_code')->get();
        $liabilityData = [];
        $totalLiabilities = 0;
        foreach ($liabilities as $acc) {
            $bal = $getBalance($acc->id, 'credit');
            if ($bal != 0) {
                $liabilityData[] = ['account' => $acc, 'balance' => $bal];
                $totalLiabilities += $bal;
            }
        }

        // Equity
        $equities = ChartOfAccount::where('account_type', 'equity')->orderBy('account_code')->get();
        $equityData = [];
        $totalEquity = 0;
        foreach ($equities as $acc) {
            $bal = $getBalance($acc->id, 'credit');
            if ($bal != 0) {
                $equityData[] = ['account' => $acc, 'balance' => $bal];
                $totalEquity += $bal;
            }
        }

        // Current Year Net Income (Revenue - Expenses up to asOfDate)
        $totalRev = 0;
        $totalExp = 0;
        
        $revenues = ChartOfAccount::where('account_type', 'revenue')->get();
        foreach ($revenues as $acc) {
            $totalRev += $getBalance($acc->id, 'credit');
        }
        
        $expenses = ChartOfAccount::where('account_type', 'expense')->get();
        foreach ($expenses as $acc) {
            $totalExp += $getBalance($acc->id, 'debit');
        }
        
        $currentNetIncome = $totalRev - $totalExp;
        $totalEquity += $currentNetIncome; // Adding net income to equity

        return view('accounting.reports.balance_sheet', compact(
            'asOfDate', 'assetData', 'totalAssets', 'liabilityData', 'totalLiabilities', 
            'equityData', 'totalEquity', 'currentNetIncome'
        ));
    }
}
