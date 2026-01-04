<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('kind', ['income', 'expense', 'transfer']);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('source_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('target_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->enum('frequency', ['weekly', 'biweekly', 'semimonthly', 'monthly']);
            $table->integer('interval')->default(1);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('occurrences_total')->nullable();
            $table->integer('occurrences_remaining')->nullable();
            $table->date('next_run_on');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('monthly_day')->nullable();
            $table->tinyInteger('semimonthly_day_1')->nullable();
            $table->tinyInteger('semimonthly_day_2')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_rules');
    }
};
