<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = (string) env('DB2_DB_prefix', 'ps_');
        $oldTable = $prefix . 'distribution_shipping';
        $newTable = $prefix . 'custom_asd_shipping';

        DB::connection('mysql2')->statement('CREATE TABLE IF NOT EXISTS ' . $this->quoteTable($newTable) . ' LIKE ' . $this->quoteTable($oldTable));

        $exists = (int) DB::connection('mysql2')->table($newTable)->count();

        if ($exists === 0) {
            DB::connection('mysql2')->statement('INSERT INTO ' . $this->quoteTable($newTable) . ' SELECT * FROM ' . $this->quoteTable($oldTable));
        }
    }

    public function down(): void
    {
        $prefix = (string) env('DB2_DB_prefix', 'ps_');

        DB::connection('mysql2')->statement('DROP TABLE IF EXISTS ' . $this->quoteTable($prefix . 'custom_asd_shipping'));
    }

    private function quoteTable(string $table): string
    {
        return collect(explode('.', $table))
            ->map(fn ($part) => '`' . str_replace('`', '``', $part) . '`')
            ->implode('.');
    }
};
