<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ps_custom_daily_sales')) {
            return;
        }

        Schema::create('ps_custom_daily_sales', function (Blueprint $table) {
            $table->date('sale_date')->primary();
            $table->decimal('total_tax_excl', 20, 6);
            $table->string('source', 16)->default('PS16');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ps_custom_daily_sales');
    }
};