<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('picking', 'is_new')) {
            return;
        }

        $customProductTable = (string) env('DB2_DB_prefix', 'ps_') . 'custom_product';

        if (count(DB::connection('mysql2')->select("SHOW COLUMNS FROM " . $this->quoteTable($customProductTable) . " LIKE 'is_new'")) === 0) {
            DB::table('picking')->update(['is_new' => false]);
            return;
        }

        $newProductIds = DB::connection('mysql2')
            ->table($customProductTable)
            ->where('is_new', 1)
            ->pluck('id_product')
            ->map(fn ($id) => (int) $id)
            ->all();

        DB::table('picking')->update(['is_new' => false]);

        if (!empty($newProductIds)) {
            DB::table('picking')
                ->whereIn('id_product', $newProductIds)
                ->update(['is_new' => true]);
        }
    }

    public function down(): void
    {
        //
    }

    private function quoteTable(string $table): string
    {
        return collect(explode('.', $table))
            ->map(fn ($part) => '`' . str_replace('`', '``', $part) . '`')
            ->implode('.');
    }
};
