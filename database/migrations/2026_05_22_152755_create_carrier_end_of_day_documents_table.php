<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrier_end_of_day_documents', function (Blueprint $table) {
            $table->id();
            $table->date('document_date');
            $table->string('carrier_name');
            $table->unsignedInteger('shipments_count')->default(0);
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['document_date', 'carrier_name'], 'carrier_eod_date_carrier_unique');
        });

        Schema::create('carrier_end_of_day_document_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')
                ->constrained('carrier_end_of_day_documents')
                ->cascadeOnDelete();
            $table->unsignedInteger('source_order_carrier_id')->nullable();
            $table->unsignedInteger('order_id');
            $table->string('order_reference')->nullable();
            $table->string('country')->nullable();
            $table->decimal('weight', 20, 6)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('depth', 10, 2)->nullable();
            $table->text('tracking_number')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'order_id']);
            $table->index('source_order_carrier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrier_end_of_day_document_lines');
        Schema::dropIfExists('carrier_end_of_day_documents');
    }
};
