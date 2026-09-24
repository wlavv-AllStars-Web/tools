<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_audit_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('path', 255);
            $table->unsignedInteger('items_count');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->timestamp('captured_at')->index();
            $table->timestamps();
        });
        Schema::create('stock_audit_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_product');
            $table->unsignedBigInteger('id_product_attribute')->default(0);
            $table->unsignedBigInteger('id_order')->nullable();
            $table->string('reference', 128)->nullable()->index();
            $table->string('source', 32)->index();
            $table->string('operation', 64);
            $table->integer('quantity_before')->nullable();
            $table->integer('quantity_after')->nullable();
            $table->integer('quantity_delta')->nullable();
            $table->integer('stock_arrive_before')->nullable();
            $table->integer('stock_arrive_after')->nullable();
            $table->integer('stock_arrive_delta')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 120)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
            $table->index(['id_product', 'id_product_attribute', 'occurred_at'], 'stock_audit_product_date_idx');
        });
    }
    public function down(): void {
        Schema::dropIfExists('stock_audit_movements');
        Schema::dropIfExists('stock_audit_snapshots');
    }
};