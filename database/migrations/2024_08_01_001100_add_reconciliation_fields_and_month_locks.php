<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('external_id', 100)->nullable()->after('scheduled_item_id');
            $table->string('source', 50)->nullable()->after('external_id');
            $table->timestamp('imported_at')->nullable()->after('source');
            $table->timestamp('reconciled_at')->nullable()->after('imported_at');
            $table->boolean('is_reconciled')->default(false)->after('reconciled_at');
            $table->string('hash', 64)->nullable()->after('is_reconciled');

            $table->index(['user_id', 'is_reconciled']);
            $table->index(['user_id', 'hash']);
            $table->unique(['user_id', 'external_id']);
        });

        Schema::create('month_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('month', 7);
            $table->timestamp('locked_at');
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'external_id']);
            $table->dropIndex(['user_id', 'hash']);
            $table->dropIndex(['user_id', 'is_reconciled']);

            $table->dropColumn(['external_id', 'source', 'imported_at', 'reconciled_at', 'is_reconciled', 'hash']);
        });

        Schema::dropIfExists('month_locks');
    }
};
