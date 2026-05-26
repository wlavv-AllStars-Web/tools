<?php

namespace App\Services\Prestashop;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class YoutubeBrokenLinkSyncService
{
    private const YOUTUBE_FIELDS = ['youtube_1', 'youtube_2', 'youtube'];

    public function sync(?int $limit = null): array
    {
        $this->ensureTable();

        $references = $this->youtubeReferences();

        if ($limit !== null && $limit > 0) {
            $references = $references->take($limit);
        }

        $checked = 0;
        $brokenCodes = [];
        $statusByCode = [];

        foreach ($references->groupBy('youtube_code') as $code => $rows) {
            $checked++;
            $status = $this->videoWorks((string) $code);
            $statusByCode[(string) $code] = $status;

            if (!$status) {
                $brokenCodes[(string) $code] = true;
            }
        }

        DB::connection('mysql2')->table($this->table('asm_youtube'))->delete();

        $now = now()->format('Y-m-d H:i:s');
        $insertRows = $references
            ->filter(fn ($row) => isset($brokenCodes[$row['youtube_code']]))
            ->map(function ($row) use ($now) {
                return [
                    'id_product' => $row['id_product'],
                    'youtube_code' => $row['youtube_code'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->values()
            ->all();

        foreach (array_chunk($insertRows, 500) as $chunk) {
            DB::connection('mysql2')->table($this->table('asm_youtube'))->insert($chunk);
        }

        return [
            'references' => $references->count(),
            'checked' => $checked,
            'working' => count(array_filter($statusByCode)),
            'broken_codes' => count($brokenCodes),
            'broken_rows' => count($insertRows),
        ];
    }

    private function youtubeReferences()
    {
        $customProductTable = $this->table('custom_product');
        $columns = collect(self::YOUTUBE_FIELDS)
            ->filter(fn ($column) => Schema::connection('mysql2')->hasColumn($customProductTable, $column))
            ->values();

        if ($columns->isEmpty()) {
            return collect();
        }

        return DB::connection('mysql2')
            ->table($customProductTable)
            ->select(array_merge(['id_product'], $columns->all()))
            ->orderBy('id_product')
            ->get()
            ->flatMap(function ($row) use ($columns) {
                return $columns
                    ->map(fn ($column) => $this->normalizeYoutubeCode((string) ($row->{$column} ?? '')))
                    ->filter()
                    ->unique()
                    ->map(fn ($code) => [
                        'id_product' => (int) $row->id_product,
                        'youtube_code' => $code,
                    ]);
            })
            ->unique(fn ($row) => $row['id_product'] . '|' . $row['youtube_code'])
            ->values();
    }

    private function videoWorks(string $code): bool
    {
        if ($code === '') {
            return false;
        }

        $response = Http::timeout(10)
            ->connectTimeout(5)
            ->acceptJson()
            ->get('https://www.youtube.com/oembed', [
                'url' => 'https://www.youtube.com/watch?v=' . $code,
                'format' => 'json',
            ]);

        if ($response->successful()) {
            return true;
        }

        if (in_array($response->status(), [401, 403, 404], true)) {
            return false;
        }

        throw new \RuntimeException('YouTube validation unavailable. HTTP status: ' . $response->status());
    }

    private function normalizeYoutubeCode(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,20})~', $value, $matches)) {
            return $matches[1];
        }

        if (preg_match('~^[A-Za-z0-9_-]{6,20}$~', $value)) {
            return $value;
        }

        return $value;
    }

    private function ensureTable(): void
    {
        $table = $this->table('asm_youtube');

        if (Schema::connection('mysql2')->hasTable($table)) {
            $this->ensureColumn($table, 'created_at', 'ALTER TABLE ' . $this->quotedTable('asm_youtube') . ' ADD `created_at` DATETIME NULL');
            $this->ensureColumn($table, 'updated_at', 'ALTER TABLE ' . $this->quotedTable('asm_youtube') . ' ADD `updated_at` DATETIME NULL');

            return;
        }

        DB::connection('mysql2')->statement(
            'CREATE TABLE ' . $this->quotedTable('asm_youtube') . ' (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_product` INT UNSIGNED NOT NULL,
                `youtube_code` VARCHAR(255) NOT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_id_product` (`id_product`),
                KEY `idx_youtube_code` (`youtube_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    private function ensureColumn(string $table, string $column, string $statement): void
    {
        if (!Schema::connection('mysql2')->hasColumn($table, $column)) {
            DB::connection('mysql2')->statement($statement);
        }
    }

    private function table(string $table): string
    {
        return env('DB2_DB_prefix', 'ps_') . $table;
    }

    private function quotedTable(string $table): string
    {
        return '`' . str_replace('`', '``', $this->table($table)) . '`';
    }
}
