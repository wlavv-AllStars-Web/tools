<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('payment_link_requests', 'worldline_request_payload')) {
            return;
        }

        Schema::table('payment_link_requests', function (Blueprint $table) {
            $table->json('worldline_request_payload')->nullable()->after('worldline_idempotency_key');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('payment_link_requests', 'worldline_request_payload')) {
            return;
        }

        Schema::table('payment_link_requests', function (Blueprint $table) {
            $table->dropColumn('worldline_request_payload');
        });
    }
};
