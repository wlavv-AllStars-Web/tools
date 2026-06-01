<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logistics_inventory_counts', function (Blueprint $table) {
            $table->string('ean13', 64)->nullable()->after('reference');
            $table->index(['schedule_id', 'ean13'], 'li_counts_schedule_ean_idx');
            $table->index(['schedule_id', 'reference'], 'li_counts_schedule_ref_idx');
        });
    }

    public function down(): void
    {
        Schema::table('logistics_inventory_counts', function (Blueprint $table) {
            $table->dropIndex('li_counts_schedule_ean_idx');
            $table->dropIndex('li_counts_schedule_ref_idx');
            $table->dropColumn('ean13');
        });
    }
};
