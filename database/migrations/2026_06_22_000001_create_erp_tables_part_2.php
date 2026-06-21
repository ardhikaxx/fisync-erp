<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('category', 100)->nullable();
            $table->string('unit', 50)->default('pcs');
            $table->decimal('default_price', 18, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // AR additions
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->string('description')->nullable();
            $table->decimal('qty', 18, 2);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('line_total', 18, 2);
            $table->decimal('cogs_amount', 18, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 100)->unique();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->date('payment_date');
            $table->decimal('amount', 18, 2);
            $table->string('payment_method', 50);
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Inventory
        Schema::create('inventory_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('purchase_date');
            $table->decimal('qty_in', 18, 2);
            $table->decimal('qty_remaining', 18, 2);
            $table->decimal('unit_cost', 18, 6);
            $table->foreignId('source_transaction_id')->nullable()->constrained('transactions');
            $table->timestamps();
        });

        // AP additions
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('qty_ordered', 18, 2);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('line_total', 18, 2);
            $table->timestamps();
        });

        Schema::create('goods_receipt_notes', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number', 100)->unique();
            $table->foreignId('po_id')->constrained('purchase_orders');
            $table->date('received_date');
            $table->foreignId('received_by')->constrained('users');
            $table->foreignId('branch_id')->constrained('branches');
            $table->enum('status', ['draft', 'verified'])->default('draft');
            $table->timestamps();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grn_id')->constrained('goods_receipt_notes')->onDelete('cascade');
            $table->foreignId('po_item_id')->constrained('purchase_order_items');
            $table->decimal('qty_received', 18, 2);
            $table->timestamps();
        });

        Schema::create('ap_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 100)->unique();
            $table->foreignId('supplier_invoice_id')->constrained('supplier_invoices');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->date('payment_date');
            $table->decimal('amount', 18, 2);
            $table->foreignId('bank_account_id')->constrained('bank_accounts');
            $table->unsignedBigInteger('withholding_tax_id')->nullable(); // created below
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // General Ledger manual journals
        Schema::create('manual_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions');
            $table->enum('journal_type', ['general', 'adjusting', 'correction']);
            $table->string('description', 255);
            $table->foreignId('fiscal_period_id')->constrained('fiscal_periods');
            $table->boolean('requires_approval')->default(false);
            $table->enum('status', ['draft', 'pending', 'posted'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // Budgets
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_center_id')->constrained('cost_centers');
            $table->integer('fiscal_year');
            $table->enum('period_type', ['annual', 'monthly'])->default('monthly');
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->decimal('budgeted_amount', 18, 2);
            $table->integer('period_month')->nullable(); // 1-12
            $table->timestamps();
        });

        // Fixed Assets
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 50)->unique();
            $table->string('asset_name', 150);
            $table->string('category', 100);
            $table->decimal('cost_basis', 18, 2);
            $table->decimal('salvage_value', 18, 2)->default(0);
            $table->integer('useful_life_months');
            $table->date('acquisition_date');
            $table->foreignId('asset_account_id')->constrained('chart_of_accounts');
            $table->foreignId('accum_depreciation_account_id')->constrained('chart_of_accounts');
            $table->foreignId('expense_account_id')->constrained('chart_of_accounts');
            $table->foreignId('branch_id')->constrained('branches');
            $table->enum('status', ['active', 'disposed', 'fully_depreciated'])->default('active');
            $table->timestamps();
        });

        Schema::create('asset_depreciation_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('fixed_assets')->onDelete('cascade');
            $table->integer('period_month');
            $table->integer('period_year');
            $table->decimal('depreciation_amount', 18, 2);
            $table->decimal('accumulated_amount', 18, 2);
            $table->decimal('book_value', 18, 2);
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->boolean('is_posted')->default(false);
            $table->timestamps();
        });

        // Taxation
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('tax_type', ['ppn', 'pph21', 'pph23', 'pph4_2']);
            $table->decimal('rate_percentage', 5, 2);
            $table->boolean('is_active')->default(true);
            $table->date('effective_date');
            $table->timestamps();
        });

        Schema::create('tax_invoice_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('nsfp_number', 50)->unique();
            $table->string('allocated_block_start', 50)->nullable();
            $table->string('allocated_block_end', 50)->nullable();
            $table->integer('used_count')->default(0);
            $table->enum('status', ['available', 'used'])->default('available');
            $table->timestamps();
        });

        Schema::create('withholding_tax_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_invoice_id')->nullable()->constrained('supplier_invoices');
            $table->enum('tax_type', ['pph23', 'pph4_2', 'pph21']);
            $table->decimal('dpp_amount', 18, 2);
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('tax_amount', 18, 2);
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->enum('ebupot_status', ['pending', 'submitted'])->default('pending');
            $table->timestamps();
        });

        // Add the FK that was deferred
        Schema::table('ap_payments', function (Blueprint $table) {
            $table->foreign('withholding_tax_id')->references('id')->on('withholding_tax_transactions');
        });

        // Approval Workflow
        Schema::create('approval_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_name');
            $table->enum('transaction_type', ['ap_payment', 'manual_journal', 'budget_override', 'po_approval']);
            $table->decimal('min_amount', 18, 2)->default(0);
            $table->decimal('max_amount', 18, 2)->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('approval_rule_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_rule_id')->constrained('approval_rules')->onDelete('cascade');
            $table->integer('level_order');
            $table->foreignId('approver_role_id')->constrained('roles'); // references Spatie roles table
            $table->boolean('is_final_level')->default(false);
            $table->timestamps();
        });

        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_reference_type');
            $table->unsignedBigInteger('transaction_reference_id');
            $table->foreignId('approval_rule_id')->constrained('approval_rules');
            $table->integer('current_level')->default(1);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('submitted_by')->constrained('users');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_id')->constrained('approvals')->onDelete('cascade');
            $table->integer('level_order');
            $table->foreignId('approver_user_id')->constrained('users');
            $table->enum('action', ['approved', 'rejected']);
            $table->text('notes')->nullable();
            $table->timestamp('acted_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_logs');
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('approval_rule_levels');
        Schema::dropIfExists('approval_rules');
        
        Schema::table('ap_payments', function (Blueprint $table) {
            $table->dropForeign(['withholding_tax_id']);
        });

        Schema::dropIfExists('withholding_tax_transactions');
        Schema::dropIfExists('tax_invoice_numbers');
        Schema::dropIfExists('tax_settings');
        Schema::dropIfExists('asset_depreciation_schedules');
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('manual_journals');
        Schema::dropIfExists('ap_payments');
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipt_notes');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('inventory_layers');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('products');
    }
};
