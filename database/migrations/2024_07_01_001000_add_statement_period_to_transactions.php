<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'statement_period_start')) {
                $table->date('statement_period_start')->nullable()->after('memo');
            }

            if (!Schema::hasColumn('transactions', 'statement_period_end')) {
                $table->date('statement_period_end')->nullable()->after('statement_period_start');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'statement_period_start')) {
                $table->dropColumn('statement_period_start');
            }
            if (Schema::hasColumn('transactions', 'statement_period_end')) {
                $table->dropColumn('statement_period_end');
            }
        });
    }
};
