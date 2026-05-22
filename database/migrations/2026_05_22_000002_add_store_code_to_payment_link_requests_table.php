<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_link_requests', function (Blueprint $table) {
            $table->string('store_code', 10)->default('ASM')->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('payment_link_requests', function (Blueprint $table) {
            $table->dropColumn('store_code');
        });
    }
};
