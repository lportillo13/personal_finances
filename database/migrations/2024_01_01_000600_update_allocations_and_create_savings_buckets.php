<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->dropUnique('allocations_user_id_expense_scheduled_item_id_unique');
            $table->unique(['user_id', 'income_scheduled_item_id', 'expense_scheduled_item_id'], 'allocations_unique_pair');
        });

        Schema::create('savings_buckets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('income_scheduled_item_id')->constrained('scheduled_items')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'income_scheduled_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_buckets');

        Schema::table('allocations', function (Blueprint $table) {
            $table->dropUnique('allocations_unique_pair');
            $table->unique(['user_id', 'expense_scheduled_item_id']);
        });
    }
};
