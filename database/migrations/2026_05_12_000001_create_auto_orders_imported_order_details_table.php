<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('mysql')->hasTable('auto_orders_imported_order_details')) {
            return;
        }

        Schema::connection('mysql')->create('auto_orders_imported_order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_order');
            $table->unsignedInteger('id_order_detail');
            $table->unsignedInteger('id_product');
            $table->unsignedInteger('id_product_attribute')->default(0);
            $table->string('origin', 10)->nullable();
            $table->timestamp('imported_at')->useCurrent();
            $table->timestamps();

            $table->unique(['id_order_detail', 'id_product', 'id_product_attribute'], 'auto_orders_import_source_unique');
            $table->index(['id_order', 'id_product', 'id_product_attribute'], 'auto_orders_import_order_product_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('auto_orders_imported_order_details');
    }
};
