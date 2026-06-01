<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logistics_inventory_counts', function (Blueprint $table) {
            $table->boolean('recount_requested')->default(false)->after('counted_at');
            $table->index(['schedule_id', 'recount_requested'], 'li_counts_schedule_recount_idx');
        });
    }

    public function down(): void
    {
        Schema::table('logistics_inventory_counts', function (Blueprint $table) {
            $table->dropIndex('li_counts_schedule_recount_idx');
            $table->dropColumn('recount_requested');
        });
    }
};
