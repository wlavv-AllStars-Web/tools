<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('mysql')->hasTable('quantity_sold_by_month')) {
            return;
        }

        Schema::connection('mysql')->create('quantity_sold_by_month', function (Blueprint $table) {
            $table->unsignedInteger('id_product');
            $table->unsignedInteger('id_product_attribute')->default(0);
            $table->string('reference', 191)->nullable();
            $table->string('attribute_reference', 191)->nullable();
            $table->date('month');
            $table->unsignedInteger('quantity_sold')->default(0);
            $table->timestamp('calculated_at')->useCurrent();

            $table->unique(
                ['id_product', 'id_product_attribute', 'month'],
                'quantity_sold_product_attribute_month_unique'
            );
            $table->index(['month', 'quantity_sold'], 'quantity_sold_month_quantity_idx');
            $table->index('reference', 'quantity_sold_reference_idx');
            $table->index('attribute_reference', 'quantity_sold_attribute_reference_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('quantity_sold_by_month');
    }
};
