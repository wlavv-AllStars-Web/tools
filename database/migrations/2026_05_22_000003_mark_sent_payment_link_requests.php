<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payment_link_requests')
            ->whereNotNull('email_sent_at')
            ->update(['status' => 'sent']);
    }

    public function down(): void
    {
        DB::table('payment_link_requests')
            ->where('status', 'sent')
            ->update(['status' => 'approved']);
    }
};
