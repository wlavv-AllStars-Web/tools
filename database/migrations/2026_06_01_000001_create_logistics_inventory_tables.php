<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logistics_inventory_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('inventory_date');
            $table->string('cell', 64);
            $table->boolean('preparation_done')->default(false);
            $table->foreignId('preparation_operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('preparation_done_at')->nullable();
            $table->boolean('inventory_done')->default(false);
            $table->foreignId('inventory_operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('inventory_done_at')->nullable();
            $table->timestamps();

            $table->unique(['inventory_date', 'cell'], 'logistics_inventory_schedule_unique');
            $table->index(['inventory_date', 'inventory_done'], 'li_sched_date_done_idx');
        });

        Schema::create('logistics_inventory_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('logistics_inventory_schedules')->cascadeOnDelete();
            $table->unsignedBigInteger('id_stock_available');
            $table->unsignedBigInteger('id_product');
            $table->unsignedBigInteger('id_product_attribute')->default(0);
            $table->string('reference', 128)->nullable();
            $table->string('location', 128)->nullable();
            $table->integer('current_quantity')->default(0);
            $table->integer('counted_quantity')->nullable();
            $table->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('counted_at')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'id_stock_available'], 'logistics_inventory_count_unique');
            $table->index(['schedule_id', 'counted_quantity'], 'li_counts_schedule_counted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_inventory_counts');
        Schema::dropIfExists('logistics_inventory_schedules');
    }
};
