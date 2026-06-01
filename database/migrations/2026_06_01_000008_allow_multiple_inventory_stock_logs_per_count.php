<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logistics_inventory_stock_logs', function (Blueprint $table) {
            $table->index(['count_id', 'reason'], 'li_stock_logs_count_reason_idx');
            $table->dropUnique('li_stock_logs_count_reason_unique');
        });
    }

    public function down(): void
    {
        Schema::table('logistics_inventory_stock_logs', function (Blueprint $table) {
            $table->unique(['count_id', 'reason'], 'li_stock_logs_count_reason_unique');
            $table->dropIndex('li_stock_logs_count_reason_idx');
        });
    }
};
