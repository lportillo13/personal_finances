<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->decimal('current_amount', 12, 2)->nullable()->after('currency');
        });

        Schema::table('credit_cards', function (Blueprint $table) {
            $table->decimal('current_amount', 12, 2)->nullable()->after('minimum_payment');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('current_amount');
        });

        Schema::table('credit_cards', function (Blueprint $table) {
            $table->dropColumn('current_amount');
        });
    }
};
