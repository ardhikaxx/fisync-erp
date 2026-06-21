<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Chart of Accounts
    Route::get('/coa', [\App\Http\Controllers\Accounting\ChartOfAccountController::class, 'index'])->name('coa.index');
    Route::get('/coa/create', [\App\Http\Controllers\Accounting\ChartOfAccountController::class, 'create'])->name('coa.create');
    Route::post('/coa', [\App\Http\Controllers\Accounting\ChartOfAccountController::class, 'store'])->name('coa.store');
    
    // Manual Journals (General Ledger)
    Route::get('/journals', [\App\Http\Controllers\Accounting\ManualJournalController::class, 'index'])->name('journals.index');
    Route::get('/journals/create', [\App\Http\Controllers\Accounting\ManualJournalController::class, 'create'])->name('journals.create');
    Route::post('/journals', [\App\Http\Controllers\Accounting\ManualJournalController::class, 'store'])->name('journals.store');

    // Kas & Bank
    Route::get('/cashbank', [\App\Http\Controllers\CashBank\CashBankTransactionController::class, 'index'])->name('cashbank.index');
    Route::get('/cashbank/create', [\App\Http\Controllers\CashBank\CashBankTransactionController::class, 'create'])->name('cashbank.create');
    Route::post('/cashbank', [\App\Http\Controllers\CashBank\CashBankTransactionController::class, 'store'])->name('cashbank.store');

    // Piutang (AR)
    Route::get('/ar/invoices', [\App\Http\Controllers\AR\InvoiceController::class, 'index'])->name('ar.invoices.index');
    Route::get('/ar/invoices/create', [\App\Http\Controllers\AR\InvoiceController::class, 'create'])->name('ar.invoices.create');
    Route::post('/ar/invoices', [\App\Http\Controllers\AR\InvoiceController::class, 'store'])->name('ar.invoices.store');

    // Hutang (AP)
    Route::get('/ap/invoices', [\App\Http\Controllers\AP\PurchaseInvoiceController::class, 'index'])->name('ap.invoices.index');
    Route::get('/ap/invoices/create', [\App\Http\Controllers\AP\PurchaseInvoiceController::class, 'create'])->name('ap.invoices.create');
    Route::post('/ap/invoices', [\App\Http\Controllers\AP\PurchaseInvoiceController::class, 'store'])->name('ap.invoices.store');

    // Add more routes here later for etc.
});
