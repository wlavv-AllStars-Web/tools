<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_link_requests', function (Blueprint $table) {
            $table->id();
            $table->string('order_id');
            $table->string('description', 30);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('customer_email');
            $table->string('request_hash', 64)->unique();
            $table->string('sha_sign', 40);
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedBigInteger('requested_by');
            $table->timestamp('requested_at');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('email_sent_by')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'approved_at']);
            $table->index('requested_by');
            $table->index('approved_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_link_requests');
    }
};
