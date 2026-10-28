<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency_price_update_batches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('currency_id')->index();
            $table->string('currency_iso', 8)->index();
            $table->decimal('old_purchase_conversion_rate', 18, 8);
            $table->decimal('new_purchase_conversion_rate', 18, 8);
            $table->decimal('old_sale_conversion_rate', 18, 8);
            $table->decimal('new_sale_conversion_rate', 18, 8);
            $table->string('status', 32)->default('applied')->index();
            $table->unsignedBigInteger('applied_by')->nullable()->index();
            $table->string('applied_by_name', 255)->nullable();
            $table->timestamp('reverted_at')->nullable();
            $table->unsignedBigInteger('reverted_by')->nullable()->index();
            $table->string('reverted_by_name', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('currency_price_update_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('batch_id')->index();
            $table->unsignedInteger('manufacturer_id')->index();
            $table->string('manufacturer', 255);
            $table->unsignedInteger('product_id')->index();
            $table->unsignedInteger('product_attribute_id')->default(0);
            $table->string('reference', 128)->index();
            $table->string('line_type', 16);
            $table->decimal('purchase_base_currency', 20, 6)->nullable();
            $table->decimal('sale_base_currency', 20, 6)->nullable();
            $table->decimal('old_purchase_eur', 20, 6)->nullable();
            $table->decimal('new_purchase_eur', 20, 6)->nullable();
            $table->decimal('old_sale_eur', 20, 6)->nullable();
            $table->decimal('new_sale_eur', 20, 6)->nullable();
            $table->boolean('purchase_updated')->default(false);
            $table->boolean('sale_updated')->default(false);
            $table->string('reason', 128)->nullable();
            $table->json('old_snapshot')->nullable();
            $table->json('new_snapshot')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'product_attribute_id'], 'currency_price_update_line_product_attribute_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_price_update_lines');
        Schema::dropIfExists('currency_price_update_batches');
    }
};
