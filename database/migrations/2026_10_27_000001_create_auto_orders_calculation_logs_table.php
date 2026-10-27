<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auto_orders_calculation_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('auto_orders_candidate_id')->nullable()->index();
            $table->unsignedInteger('id_order')->nullable()->index();
            $table->unsignedInteger('id_order_detail')->nullable()->index();
            $table->unsignedInteger('id_product')->index();
            $table->unsignedInteger('id_product_attribute')->default(0);
            $table->unsignedInteger('id_supplier')->nullable()->index();
            $table->string('supplier', 255)->nullable();
            $table->string('reference', 128)->index();
            $table->unsignedInteger('sales_last_12_months')->default(0);
            $table->integer('physical_stock')->default(0);
            $table->integer('stock_arrive')->default(0);
            $table->unsignedInteger('target_stock')->default(0);
            $table->unsignedInteger('quantity_before')->default(0);
            $table->unsignedInteger('quantity_calculated')->default(0);
            $table->unsignedInteger('quantity_after')->default(0);
            $table->string('action', 64)->index();
            $table->string('reason', 128)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['id_product', 'id_product_attribute'], 'auto_orders_calc_product_attribute_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_orders_calculation_logs');
    }
};
