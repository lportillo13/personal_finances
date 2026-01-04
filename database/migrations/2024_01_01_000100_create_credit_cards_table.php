<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('issuer_name')->nullable();
            $table->string('last4', 4)->nullable();
            $table->tinyInteger('due_day');
            $table->tinyInteger('statement_close_day')->nullable();
            $table->boolean('autopay_enabled')->default(false);
            $table->foreignId('autopay_pay_from_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_cards');
    }
};
