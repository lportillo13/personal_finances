<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_items', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->decimal('actual_amount', 12, 2)->nullable()->after('amount');
            $table->string('note', 255)->nullable()->after('paid_at');

            $table->index(['user_id', 'date'], 'scheduled_items_user_date_index');
            $table->index(['user_id', 'status'], 'scheduled_items_user_status_index');
        });

        DB::table('scheduled_items')
            ->where('status', 'planned')
            ->update(['status' => 'pending']);

        DB::table('scheduled_items')
            ->where('status', 'posted')
            ->update(['status' => 'paid']);
    }

    public function down(): void
    {
        DB::table('scheduled_items')
            ->where('status', 'paid')
            ->update(['status' => 'posted']);

        DB::table('scheduled_items')
            ->where('status', 'pending')
            ->update(['status' => 'planned']);

        Schema::table('scheduled_items', function (Blueprint $table) {
            $table->enum('status', ['planned', 'posted', 'skipped'])->default('planned')->change();
            $table->dropColumn(['paid_at', 'actual_amount', 'note']);
            $table->dropIndex('scheduled_items_user_date_index');
            $table->dropIndex('scheduled_items_user_status_index');
        });
    }
};
