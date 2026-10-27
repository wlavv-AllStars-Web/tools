<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auto_orders_purchase_list', function (Blueprint $table) {
            $table->boolean('is_automatic')->default(false)->after('quantity');
            $table->unsignedInteger('automatic_expected_quantity')->nullable()->after('is_automatic');
            $table->timestamp('automatic_calculated_at')->nullable()->after('automatic_expected_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('auto_orders_purchase_list', function (Blueprint $table) {
            $table->dropColumn([
                'is_automatic',
                'automatic_expected_quantity',
                'automatic_calculated_at',
            ]);
        });
    }
};
