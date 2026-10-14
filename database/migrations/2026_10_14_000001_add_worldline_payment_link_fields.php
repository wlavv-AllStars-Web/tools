<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('payment_link_requests', 'worldline_payment_link_id')) {
            return;
        }

        Schema::table('payment_link_requests', function (Blueprint $table) {
            $table->unsignedSmallInteger('expiration_hours')->default(168)->after('currency');
            $table->string('worldline_payment_link_id')->nullable()->unique();
            $table->text('worldline_payment_url')->nullable();
            $table->uuid('worldline_idempotency_key')->nullable()->unique();
            $table->timestamp('worldline_expires_at')->nullable()->index();
            $table->timestamp('worldline_created_at')->nullable();
            $table->text('worldline_error')->nullable();
        });

        Schema::table('payment_link_requests', function (Blueprint $table) {
            $table->string('sha_sign', 40)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('payment_link_requests', 'worldline_payment_link_id')) {
            return;
        }

        Schema::table('payment_link_requests', function (Blueprint $table) {
            $table->dropUnique(['worldline_payment_link_id']);
            $table->dropUnique(['worldline_idempotency_key']);
            $table->dropIndex(['worldline_expires_at']);
            $table->dropColumn([
                'expiration_hours',
                'worldline_payment_link_id',
                'worldline_payment_url',
                'worldline_idempotency_key',
                'worldline_expires_at',
                'worldline_created_at',
                'worldline_error',
            ]);
        });
    }
};
