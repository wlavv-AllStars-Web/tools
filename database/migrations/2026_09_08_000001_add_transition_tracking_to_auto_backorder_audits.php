<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auto_backorder_audits', function (Blueprint $table) {
            $table->timestamp('state_change_attempted_at')->nullable()->after('state_changed');
            $table->timestamp('state_changed_at')->nullable()->after('state_change_attempted_at');
            $table->text('state_change_error')->nullable()->after('state_changed_at');
        });
    }

    public function down(): void
    {
        Schema::table('auto_backorder_audits', function (Blueprint $table) {
            $table->dropColumn([
                'state_change_attempted_at',
                'state_changed_at',
                'state_change_error',
            ]);
        });
    }
};
