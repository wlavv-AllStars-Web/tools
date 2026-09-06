<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auto_backorder_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_order');
            $table->string('order_reference', 64)->nullable();
            $table->unsignedInteger('original_state');
            $table->unsignedInteger('target_state');
            $table->date('audit_date');
            $table->timestamp('detected_at');
            $table->text('reason');
            $table->json('unpicked_products');
            $table->boolean('state_changed')->default(false);
            $table->timestamps();

            $table->unique(['id_order', 'audit_date'], 'auto_backorder_audit_order_day_unique');
            $table->index(['audit_date', 'detected_at'], 'auto_backorder_audit_date_detected_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_backorder_audits');
    }
};
