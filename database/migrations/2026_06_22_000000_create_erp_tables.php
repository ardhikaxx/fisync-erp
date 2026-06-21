<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Core Structure
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_code', 50)->unique();
            $table->string('branch_name', 150);
            $table->text('address')->nullable();
            $table->boolean('is_head_office')->default(false);
            $table->foreignId('parent_branch_id')->nullable()->constrained('branches');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('branch_id')->constrained('branches');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 10)->unique();
            $table->string('currency_name', 50);
            $table->string('symbol', 10);
            $table->boolean('is_base_currency')->default(false);
            $table->timestamps();
        });

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')->constrained('currencies');
            $table->date('rate_date');
            $table->decimal('rate_to_base', 18, 6);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('fiscal_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_name', 100);
            $table->integer('period_month');
            $table->integer('period_year');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts');
            $table->string('account_code', 50)->unique();
            $table->string('account_name', 150);
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->tinyInteger('level')->default(1);
            $table->boolean('is_postable')->default(true);
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('manager_user_id')->nullable()->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 100)->unique();
            $table->date('transaction_date');
            $table->text('description');
            $table->string('source_type', 150)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('fiscal_period_id')->constrained('fiscal_periods');
            $table->foreignId('currency_id')->constrained('currencies');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('posted_by')->nullable()->constrained('users');
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('reversal_of_id')->nullable()->constrained('transactions');
            $table->timestamps();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->foreignId('cost_center_id')->nullable()->constrained('cost_centers');
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->decimal('debit_base', 18, 2)->default(0);
            $table->decimal('credit_base', 18, 2)->default(0);
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        // 2. Customers & Suppliers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('npwp', 30)->nullable();
            $table->string('nik', 30)->nullable();
            $table->boolean('pkp_status')->default(false);
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('npwp', 30)->nullable();
            $table->string('nik', 30)->nullable();
            $table->boolean('pkp_status')->default(false);
            $table->timestamps();
        });

        // 3. Kas & Bank
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->constrained('banks');
            $table->string('account_number', 100);
            $table->string('account_name');
            $table->foreignId('currency_id')->constrained('currencies');
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->foreignId('branch_id')->constrained('branches');
            $table->timestamps();
        });

        Schema::create('transaction_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('default_expense_account_id')->nullable()->constrained('chart_of_accounts');
            $table->enum('type', ['in', 'out']);
            $table->timestamps();
        });

        Schema::create('cash_bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions');
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts');
            $table->enum('type', ['cash_in', 'cash_out', 'transfer', 'petty_cash']);
            $table->decimal('amount', 18, 2);
            $table->foreignId('category_id')->nullable()->constrained('transaction_categories');
            $table->string('attachment_path')->nullable();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // AR
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 100)->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('subtotal', 18, 2);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2);
            $table->decimal('paid_amount', 18, 2)->default(0);
            $table->decimal('balance_due', 18, 2);
            $table->enum('status', ['draft', 'posted', 'partial', 'paid', 'overdue', 'void'])->default('draft');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // AP
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 100)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->date('order_date');
            $table->enum('status', ['draft', 'approved', 'partially_received', 'closed', 'cancelled'])->default('draft');
            $table->decimal('total_amount', 18, 2);
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
        
        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 100);
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('po_id')->nullable()->constrained('purchase_orders');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('total_amount', 18, 2);
            $table->enum('status', ['pending_match', 'matched', 'discrepancy', 'approved', 'paid'])->default('pending_match');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Audit Trail
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->enum('action', ['create', 'update', 'delete', 'approve', 'reject', 'reverse', 'login', 'logout']);
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
        Schema::dropIfExists('supplier_invoices');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('cash_bank_transactions');
        Schema::dropIfExists('transaction_categories');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('banks');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('cost_centers');
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('fiscal_periods');
        Schema::dropIfExists('fiscal_years');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn(['branch_id', 'department_id', 'is_active', 'last_login_at']);
        });

        Schema::dropIfExists('departments');
        Schema::dropIfExists('branches');
    }
};
