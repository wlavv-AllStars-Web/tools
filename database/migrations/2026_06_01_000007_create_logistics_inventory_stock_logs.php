<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logistics_inventory_stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('logistics_inventory_schedules')->cascadeOnDelete();
            $table->foreignId('count_id')->constrained('logistics_inventory_counts')->cascadeOnDelete();
            $table->unsignedBigInteger('id_stock_available');
            $table->unsignedBigInteger('id_product');
            $table->unsignedBigInteger('id_product_attribute')->default(0);
            $table->string('reference', 128)->nullable();
            $table->integer('previous_quantity');
            $table->integer('new_quantity');
            $table->integer('quantity_delta');
            $table->string('reason', 64)->default('inventory');
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at');
            $table->timestamps();

            $table->unique(['count_id', 'reason'], 'li_stock_logs_count_reason_unique');
            $table->index(['schedule_id', 'validated_at'], 'li_stock_logs_schedule_date_idx');
            $table->index(['id_stock_available'], 'li_stock_logs_stock_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_inventory_stock_logs');
    }
};
