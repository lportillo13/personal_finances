<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('credit_cards', 'payment_due_day')) {
                $table->tinyInteger('payment_due_day')->nullable()->after('due_day');
            }

            if (!Schema::hasColumn('credit_cards', 'minimum_payment')) {
                $table->decimal('minimum_payment', 12, 2)->nullable()->after('payment_due_day');
            }

            if (!Schema::hasColumn('credit_cards', 'autopay_mode')) {
                $table->string('autopay_mode', 20)->nullable()->after('autopay_enabled');
            }

            if (!Schema::hasColumn('credit_cards', 'autopay_fixed_amount')) {
                $table->decimal('autopay_fixed_amount', 12, 2)->nullable()->after('autopay_mode');
            }

            if (!Schema::hasColumn('credit_cards', 'default_funding_account_id')) {
                $table->foreignId('default_funding_account_id')
                    ->nullable()
                    ->after('autopay_pay_from_account_id')
                    ->constrained('accounts')
                    ->nullOnDelete();
            }
        });

        if (Schema::hasColumn('credit_cards', 'due_day') && Schema::hasColumn('credit_cards', 'payment_due_day')) {
            DB::table('credit_cards')->whereNull('payment_due_day')->update([
                'payment_due_day' => DB::raw('due_day'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('credit_cards', function (Blueprint $table) {
            if (Schema::hasColumn('credit_cards', 'payment_due_day')) {
                $table->dropColumn('payment_due_day');
            }
            if (Schema::hasColumn('credit_cards', 'minimum_payment')) {
                $table->dropColumn('minimum_payment');
            }
            if (Schema::hasColumn('credit_cards', 'autopay_mode')) {
                $table->dropColumn('autopay_mode');
            }
            if (Schema::hasColumn('credit_cards', 'autopay_fixed_amount')) {
                $table->dropColumn('autopay_fixed_amount');
            }
            if (Schema::hasColumn('credit_cards', 'default_funding_account_id')) {
                $table->dropForeign(['default_funding_account_id']);
                $table->dropColumn('default_funding_account_id');
            }
        });
    }
};
