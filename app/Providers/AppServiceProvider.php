<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\Accounting\Transaction;
use App\Models\AR\Invoice;
use App\Models\AP\SupplierInvoice;
use App\Models\CashBank\CashBankTransaction;
use App\Observers\AuditObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        
        Transaction::observe(AuditObserver::class);
        Invoice::observe(AuditObserver::class);
        SupplierInvoice::observe(AuditObserver::class);
        CashBankTransaction::observe(AuditObserver::class);
    }
}
