<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->unsignedTinyInteger('due_day');
            $table->unsignedTinyInteger('statement_close_day')->nullable();
            $table->boolean('autopay')->default(false);
            $table->foreignId('autopay_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_cards');
    }
};
