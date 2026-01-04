<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recurring_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('direction', ['income', 'expense', 'transfer']);
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('USD');
            $table->foreignId('source_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('target_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->enum('frequency', ['weekly', 'biweekly', 'semimonthly', 'monthly']);
            $table->unsignedInteger('interval')->default(1);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedInteger('occurrences_total')->nullable();
            $table->unsignedInteger('occurrences_remaining')->nullable();
            $table->date('next_run_on');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_rules');
    }
};
