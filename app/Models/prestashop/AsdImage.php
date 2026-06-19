<?php

namespace App\Models\prestashop;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AsdImage extends PrestashopModel
{
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_product',
        'id_product_attribute',
        'id_manufacturer',
        'reference',
        'image_name',
        'image_code',
        'manufacturer',
        'has_image',
        'verified',
        'image_path',
        'checked_at',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('custom_asd_images');
    }

    public static function ensureTable(): void
    {
        self::ensureProductImageCodeColumn();
        self::ensureProductAttributeImageCodeColumn();

        if (!self::hasCustomTable() && self::hasOldTable()) {
            DB::connection('mysql2')->statement(
                'RENAME TABLE ' . self::quotedTable('asd_images') . ' TO ' . self::quotedTable('custom_asd_images')
            );
        }

        if (self::hasCustomTable()) {
            self::ensureColumn('id_product_attribute', 'ALTER TABLE ' . self::quotedTable('custom_asd_images') . ' ADD `id_product_attribute` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `id_product`');
            self::ensureColumn('id_manufacturer', 'ALTER TABLE ' . self::quotedTable('custom_asd_images') . ' ADD `id_manufacturer` INT UNSIGNED NULL AFTER `id_product_attribute`');
            self::ensureNullableColumn('id_manufacturer', 'INT UNSIGNED NULL');
            self::ensureColumn('image_name', 'ALTER TABLE ' . self::quotedTable('custom_asd_images') . ' ADD `image_name` VARCHAR(255) NULL AFTER `reference`');
            self::ensureNullableColumn('image_name', 'VARCHAR(255) NULL');
            self::ensureColumn('image_code', 'ALTER TABLE ' . self::quotedTable('custom_asd_images') . ' ADD `image_code` VARCHAR(128) NULL AFTER `image_name`');
            self::ensureNullableColumn('image_code', 'VARCHAR(128) NULL');
            self::ensureColumn('manufacturer', 'ALTER TABLE ' . self::quotedTable('custom_asd_images') . ' ADD `manufacturer` VARCHAR(255) NULL AFTER `image_code`');
            self::ensureNullableColumn('manufacturer', 'VARCHAR(255) NULL');
            self::ensureColumn('has_image', 'ALTER TABLE ' . self::quotedTable('custom_asd_images') . ' ADD `has_image` TINYINT(1) NOT NULL DEFAULT 0 AFTER `manufacturer`');
            self::ensureColumn('verified', 'ALTER TABLE ' . self::quotedTable('custom_asd_images') . ' ADD `verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `has_image`');
            self::ensureColumn('image_path', 'ALTER TABLE ' . self::quotedTable('custom_asd_images') . ' ADD `image_path` VARCHAR(500) NULL AFTER `verified`');
            self::ensureNullableColumn('image_path', 'VARCHAR(500) NULL');
            self::ensureColumn('checked_at', 'ALTER TABLE ' . self::quotedTable('custom_asd_images') . ' ADD `checked_at` DATETIME NULL AFTER `image_path`');
            self::ensureNullableColumn('checked_at', 'DATETIME NULL');
            self::ensureColumn('created_at', 'ALTER TABLE ' . self::quotedTable('custom_asd_images') . ' ADD `created_at` DATETIME NULL AFTER `checked_at`');
            self::ensureNullableColumn('created_at', 'DATETIME NULL');
            self::ensureColumn('updated_at', 'ALTER TABLE ' . self::quotedTable('custom_asd_images') . ' ADD `updated_at` DATETIME NULL AFTER `created_at`');
            self::ensureNullableColumn('updated_at', 'DATETIME NULL');

            return;
        }

        DB::connection('mysql2')->statement(
            'CREATE TABLE ' . self::quotedTable('custom_asd_images') . ' (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_product` INT UNSIGNED NOT NULL,
                `id_product_attribute` INT UNSIGNED NOT NULL DEFAULT 0,
                `id_manufacturer` INT UNSIGNED NULL,
                `reference` VARCHAR(128) NOT NULL,
                `image_name` VARCHAR(255) NULL,
                `image_code` VARCHAR(128) NULL,
                `manufacturer` VARCHAR(255) NULL,
                `has_image` TINYINT(1) NOT NULL DEFAULT 0,
                `verified` TINYINT(1) NOT NULL DEFAULT 0,
                `image_path` VARCHAR(500) NULL,
                `checked_at` DATETIME NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_reference` (`reference`),
                KEY `idx_verified_has_image` (`verified`, `has_image`),
                KEY `idx_id_product` (`id_product`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public static function sync(?int $limit = null): array
    {
        self::ensureTable();

        $inserted = self::syncProductReferences();
        $removed = self::deleteStaleReferences();
        self::markMissingAsUnverified($limit);
        $verified = self::verifyPending($limit);

        return [
            'inserted' => $inserted,
            'removed' => $removed,
            'verified' => $verified['verified'],
            'missing' => $verified['missing'],
            'found' => $verified['found'],
        ];
    }

    public static function syncProductReferences(): int
    {
        self::ensureTable();

        $existing = self::query()
            ->pluck('reference')
            ->map(fn ($reference) => trim((string) $reference))
            ->filter()
            ->flip();

        $now = now()->format('Y-m-d H:i:s');
        $inserted = 0;

        foreach (self::asdReferences() as $row) {
            $reference = trim((string) $row->reference);

            if ($reference === '') {
                continue;
            }

            if ($existing->has($reference)) {
                self::updateExistingReference($reference, $row);
                continue;
            }

            self::query()->insert([
                'id_product' => (int) $row->id_product,
                'id_product_attribute' => (int) $row->id_product_attribute,
                'id_manufacturer' => $row->id_manufacturer ? (int) $row->id_manufacturer : null,
                'reference' => $reference,
                'image_name' => self::cleanImageName($row->image_name ?? null),
                'image_code' => self::cleanImageCode($row->image_code ?? null),
                'manufacturer' => $row->manufacturer ?: null,
                'has_image' => 0,
                'verified' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $existing->put($reference, true);
            $inserted++;
        }

        return $inserted;
    }

    public static function deleteStaleReferences(): int
    {
        self::ensureTable();

        $references = self::asdReferences()
            ->pluck('reference')
            ->map(fn ($reference) => trim((string) $reference))
            ->filter()
            ->values()
            ->all();

        if (empty($references)) {
            return 0;
        }

        return self::query()
            ->whereNotIn('reference', $references)
            ->delete();
    }

    public static function verifyPending(?int $limit = null): array
    {
        self::ensureTable();

        $query = self::query()
            ->where('verified', 0)
            ->orderBy('id');

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        $verified = 0;
        $missing = 0;
        $found = 0;
        $now = now()->format('Y-m-d H:i:s');

        foreach ($query->get() as $row) {
            $imagePath = self::imagePathFor(
                (int) $row->id_manufacturer,
                (int) $row->id_product_attribute,
                (string) $row->reference,
                (string) ($row->image_name ?? ''),
                (string) ($row->image_code ?? '')
            );
            $hasImage = $imagePath !== null;

            self::query()
                ->where('id', $row->id)
                ->update([
                    'has_image' => $hasImage ? 1 : 0,
                    'verified' => $hasImage ? 1 : 0,
                    'image_path' => $imagePath,
                    'checked_at' => $now,
                    'updated_at' => $now,
                ]);

            $verified++;
            $hasImage ? $found++ : $missing++;
        }

        return compact('verified', 'missing', 'found');
    }

    public static function markMissingAsUnverified(?int $limit = null): int
    {
        self::ensureTable();

        $query = self::query()
            ->where('verified', 1)
            ->where('has_image', 0)
            ->orderBy('id');

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        $ids = $query->pluck('id');

        if ($ids->isEmpty()) {
            return 0;
        }

        return self::query()
            ->whereIn('id', $ids)
            ->update([
                'verified' => 0,
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ]);
    }

    public static function missingRows(array $exceptions = [])
    {
        self::ensureTable();

        return self::query()
            ->where('verified', 0)
            ->where('has_image', 0)
            ->when(!empty($exceptions), fn ($query) => $query->whereNotIn('id_product', $exceptions))
            ->select(['id_product', 'id_product_attribute', 'reference', 'manufacturer'])
            ->orderBy('id_product')
            ->orderBy('reference')
            ->get();
    }

    public static function markManufacturerAsUnverified(int $idManufacturer): int
    {
        self::ensureTable();

        return self::query()
            ->where('id_manufacturer', $idManufacturer)
            ->update([
                'verified' => 0,
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ]);
    }

    private static function asdReferences()
    {
        $prefix = self::prefix();
        $shopId = (int) (
            config('allstars.stores.ASD.id_shop')
            ?: config('shops.ASD.id')
            ?: 3
        );

        $products = DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->join($prefix . 'product_shop as ps', function ($join) use ($shopId) {
                $join->on('ps.id_product', '=', 'p.id_product')
                    ->where('ps.id_shop', $shopId)
                    ->where('ps.active', 1);
            })
            ->leftJoin($prefix . 'manufacturer as m', 'm.id_manufacturer', '=', 'p.id_manufacturer')
            ->leftJoin($prefix . 'product_lang as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', 1);
            })
            ->leftJoin($prefix . 'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->select([
                'p.id_product',
                DB::raw('0 as id_product_attribute'),
                'p.id_manufacturer',
                'p.reference',
                DB::raw('COALESCE(pl.description_short, "") as image_name'),
                DB::raw('COALESCE(cp.image_code, "") as image_code'),
                DB::raw('COALESCE(m.name, "") as manufacturer'),
            ])
            ->whereNotNull('p.reference')
            ->where('p.reference', '<>', '');

        $attributes = DB::connection('mysql2')
            ->table($prefix . 'product_attribute as pa')
            ->join($prefix . 'product as p', 'p.id_product', '=', 'pa.id_product')
            ->join($prefix . 'product_shop as ps', function ($join) use ($shopId) {
                $join->on('ps.id_product', '=', 'p.id_product')
                    ->where('ps.id_shop', $shopId)
                    ->where('ps.active', 1);
            })
            ->leftJoin($prefix . 'manufacturer as m', 'm.id_manufacturer', '=', 'p.id_manufacturer')
            ->leftJoin($prefix . 'product_lang as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', 1);
            })
            ->leftJoin($prefix . 'custom_product_attribute as cpa', 'cpa.id_product_attribute', '=', 'pa.id_product_attribute')
            ->select([
                'p.id_product',
                'pa.id_product_attribute',
                'p.id_manufacturer',
                'pa.reference',
                DB::raw('COALESCE(pl.description_short, "") as image_name'),
                DB::raw('COALESCE(cpa.image_code, "") as image_code'),
                DB::raw('COALESCE(m.name, "") as manufacturer'),
            ])
            ->whereNotNull('pa.reference')
            ->where('pa.reference', '<>', '');

        return $products
            ->union($attributes)
            ->orderBy('reference')
            ->get()
            ->unique(fn ($row) => trim((string) $row->reference))
            ->values();
    }

    private static function updateExistingReference(string $reference, $row): void
    {
        $current = self::query()->where('reference', $reference)->first();

        if (!$current) {
            return;
        }

        $imageName = self::cleanImageName($row->image_name ?? null);
        $imageCode = self::cleanImageCode($row->image_code ?? null);
        $updates = [
            'id_product' => (int) $row->id_product,
            'id_product_attribute' => (int) $row->id_product_attribute,
            'id_manufacturer' => $row->id_manufacturer ? (int) $row->id_manufacturer : null,
            'image_name' => $imageName,
            'image_code' => $imageCode,
            'manufacturer' => $row->manufacturer ?: null,
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];

        if (
            (string) ($current->image_name ?? '') !== (string) $imageName
            || (string) ($current->image_code ?? '') !== (string) $imageCode
        ) {
            $updates['verified'] = 0;
        }

        self::query()->where('id', $current->id)->update($updates);
    }

    public static function resolveImagePathForRow(int $idManufacturer, int $idProductAttribute, string $reference, ?string $imageName, ?string $imageCode, ?string $lookupMode = null): ?string
    {
        return self::imagePathFor($idManufacturer, $idProductAttribute, $reference, (string) $imageName, (string) $imageCode);
    }

    private static function imagePathFor(int $idManufacturer, int $idProductAttribute, string $reference, string $imageName, string $imageCode): ?string
    {
        $reference = trim($reference);
        $imageCode = trim($imageCode);

        

            echo $id_manufacturer;
            echo '<br>' . $idProductAttribute;
            echo '<br>' . $reference;
            echo '<br>' . $imageName;
            echo '<br>' . $imageCode;

        if ($idManufacturer <= 0 || $reference === '') {
            dd(1);
            return null;
        }

        $lookupValue = $imageCode !== '' ? $imageCode : $reference;

        if ($lookupValue === '') {
            dd(2);
            return null;
        }

        $filenames = self::imageFilenameCandidates($lookupValue)
            ->map(fn ($filename) => trim((string) $filename))
            ->filter()
            ->unique()
            ->values();

        foreach (['thumb', '600'] as $size) {
            foreach ($filenames as $filename) {
                echo $path = env('RESOURCES_PRODUCTION') . '/asd/brands/' . $idManufacturer . '/' . $size . '/' . $filename . '.webp';

                if (file_exists(public_path($path))) {
                    dd(4);
                    return $path;
                }
            }
        }

        dd(3);
        return null;
    }

    private static function imageFilenameCandidates(string $value)
    {
        $value = trim(strip_tags(html_entity_decode($value)));
        $withoutExtension = preg_replace('/\.(webp|jpg|jpeg|png)$/i', '', $value) ?: $value;

        return collect([
            $value,
            $withoutExtension,
            Str::slug($value, '_'),
            Str::slug($withoutExtension, '_'),
        ]);
    }

    private static function cleanImageName(?string $value): ?string
    {
        $value = trim(strip_tags(html_entity_decode((string) $value)));

        return $value !== '' ? $value : null;
    }

    private static function cleanImageCode(?string $value): ?string
    {
        $value = trim(strip_tags(html_entity_decode((string) $value)));

        return $value !== '' ? $value : null;
    }

    private static function ensureProductAttributeImageCodeColumn(): void
    {
        $table = self::unqualifiedTableName(self::tableName('custom_product_attribute'));

        if (Schema::connection('mysql2')->hasColumn($table, 'image_code')) {
            return;
        }

        DB::connection('mysql2')->statement(
            'ALTER TABLE ' . self::quotedTable('custom_product_attribute') . ' ADD `image_code` VARCHAR(128) NULL AFTER `id_product_attribute`'
        );
    }

    private static function ensureProductImageCodeColumn(): void
    {
        $table = self::unqualifiedTableName(self::tableName('custom_product'));

        if (Schema::connection('mysql2')->hasColumn($table, 'image_code')) {
            return;
        }

        DB::connection('mysql2')->statement(
            'ALTER TABLE ' . self::quotedTable('custom_product') . ' ADD `image_code` VARCHAR(128) NULL AFTER `id_product`'
        );
    }

    private static function ensureColumn(string $column, string $statement): void
    {
        if (!Schema::connection('mysql2')->hasColumn(self::unqualifiedTableName(self::tableName('custom_asd_images')), $column)) {
            DB::connection('mysql2')->statement($statement);
        }
    }

    private static function ensureNullableColumn(string $column, string $definition): void
    {
        [$database, $table] = self::databaseAndTable(self::tableName('custom_asd_images'));

        $metadata = DB::connection('mysql2')->selectOne(
            'SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$database, $table, $column]
        );

        if (!$metadata || strtoupper((string) $metadata->IS_NULLABLE) === 'YES') {
            return;
        }

        DB::connection('mysql2')->statement(
            'ALTER TABLE ' . self::quotedTable('custom_asd_images') . ' MODIFY `' . str_replace('`', '``', $column) . '` ' . $definition
        );
    }

    private static function databaseAndTable(string $table): array
    {
        if (str_contains($table, '.')) {
            return explode('.', $table, 2);
        }

        return [DB::connection('mysql2')->getDatabaseName(), $table];
    }

    private static function hasCustomTable(): bool
    {
        return Schema::connection('mysql2')->hasTable(self::unqualifiedTableName(self::tableName('custom_asd_images')));
    }

    private static function hasOldTable(): bool
    {
        return Schema::connection('mysql2')->hasTable(self::unqualifiedTableName(self::tableName('asd_images')));
    }

    private static function quotedTable(string $table): string
    {
        $fullTable = self::tableName($table);

        if (!str_contains($fullTable, '.')) {
            return '`' . str_replace('`', '``', $fullTable) . '`';
        }

        [$database, $tableName] = explode('.', $fullTable, 2);

        return '`' . str_replace('`', '``', $database) . '`.`' . str_replace('`', '``', $tableName) . '`';
    }
}
