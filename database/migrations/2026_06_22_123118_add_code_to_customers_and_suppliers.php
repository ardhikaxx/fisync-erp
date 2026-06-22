<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->after('id');
            $table->boolean('is_active')->default(true)->after('pkp_status');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->after('id');
            $table->boolean('is_active')->default(true)->after('pkp_status');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['code', 'is_active']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['code', 'is_active']);
        });
    }
};
