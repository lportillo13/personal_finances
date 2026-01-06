<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('type', 20);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('USD');
            $table->foreignId('from_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('to_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('scheduled_item_id')->nullable()->constrained('scheduled_items')->nullOnDelete();
            $table->string('memo', 255)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'scheduled_item_id']);
            $table->index(['user_id', 'date']);
            $table->index('from_account_id');
            $table->index('to_account_id');
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
