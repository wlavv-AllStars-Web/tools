<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::connection('mysql')->hasTable('cross_auto_orders')
            && !Schema::connection('mysql')->hasTable('auto_orders_cross')
        ) {
            Schema::connection('mysql')->rename('cross_auto_orders', 'auto_orders_cross');
        }
    }

    public function down(): void
    {
        if (
            Schema::connection('mysql')->hasTable('auto_orders_cross')
            && !Schema::connection('mysql')->hasTable('cross_auto_orders')
        ) {
            Schema::connection('mysql')->rename('auto_orders_cross', 'cross_auto_orders');
        }
    }
};
