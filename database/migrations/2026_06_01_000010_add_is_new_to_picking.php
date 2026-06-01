<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('picking', function (Blueprint $table) {
            $table->boolean('is_new')->default(false)->after('housing');
        });

        $prefix = (string) env('DB2_DB_prefix', 'ps_');
        $newProductIds = DB::connection('mysql2')
            ->table($prefix . 'product')
            ->where('date_add', '>=', now()->subDays(30)->toDateString())
            ->pluck('id_product')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (!empty($newProductIds)) {
            DB::table('picking')
                ->whereIn('id_product', $newProductIds)
                ->update(['is_new' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('picking', function (Blueprint $table) {
            $table->dropColumn('is_new');
        });
    }
};
