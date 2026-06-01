<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logistics_inventory_schedules', function (Blueprint $table) {
            $table->boolean('verification_done')->default(false)->after('inventory_done');
            $table->foreignId('verification_operator_id')->nullable()->after('inventory_done_at')->constrained('users')->nullOnDelete();
            $table->timestamp('verification_done_at')->nullable()->after('verification_operator_id');
            $table->index(['inventory_date', 'verification_done'], 'li_sched_date_verif_idx');
        });
    }

    public function down(): void
    {
        Schema::table('logistics_inventory_schedules', function (Blueprint $table) {
            $table->dropIndex('li_sched_date_verif_idx');
            $table->dropConstrainedForeignId('verification_operator_id');
            $table->dropColumn(['verification_done', 'verification_done_at']);
        });
    }
};
