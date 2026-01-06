<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('income_scheduled_item_id')->constrained('scheduled_items')->cascadeOnDelete();
            $table->foreignId('expense_scheduled_item_id')->constrained('scheduled_items')->cascadeOnDelete();
            $table->decimal('allocated_amount', 12, 2);
            $table->timestamps();

            $table->unique(['user_id', 'expense_scheduled_item_id']);
            $table->index('income_scheduled_item_id');
            $table->index('expense_scheduled_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};
