<?php

namespace App\Services\ToolsMigration;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class ToolsDatabaseComparator
{
    public const NEW_CONNECTION = 'mysql';
    public const OLD_CONNECTION = 'old_tools';
    public const ROW_LIMIT = 250;

    public function connectionStatus(): array
    {
        return [
            'new' => $this->safeConnectionStatus(self::NEW_CONNECTION),
            'old' => $this->safeConnectionStatus(self::OLD_CONNECTION),
        ];
    }

    public function tableComparison(): array
    {
        $newTables = $this->tables(self::NEW_CONNECTION);
        $oldTables = $this->tables(self::OLD_CONNECTION);
        $allTables = array_values(array_unique(array_merge($newTables, $oldTables)));
        sort($allTables, SORT_NATURAL | SORT_FLAG_CASE);

        return array_map(function (string $table) use ($newTables, $oldTables) {
            $inNew = in_array($table, $newTables, true);
            $inOld = in_array($table, $oldTables, true);
            $structure = $this->structureStatus($table, $inNew, $inOld);

            return [
                'name' => $table,
                'new' => $inNew ? $table : null,
                'old' => $inOld ? $table : null,
                'status' => $this->tableStatus($inNew, $inOld),
                'structure' => $structure,
                'new_rows' => $inNew ? $this->estimatedRows(self::NEW_CONNECTION, $table) : null,
                'old_rows' => $inOld ? $this->estimatedRows(self::OLD_CONNECTION, $table) : null,
                'can_verify' => $inNew || $inOld,
                'can_replace' => $this->canReplaceTable($table, $inNew, $inOld, $structure),
            ];
        }, $allTables);
    }

    public function tableDetails(string $table): array
    {
        $this->assertKnownTable($table);

        $newColumns = $this->columnsIfExists(self::NEW_CONNECTION, $table);
        $oldColumns = $this->columnsIfExists(self::OLD_CONNECTION, $table);
        $newPrimaryKey = $this->singlePrimaryKey($newColumns);
        $oldPrimaryKey = $this->singlePrimaryKey($oldColumns);
        $primaryKey = $newPrimaryKey ?: $oldPrimaryKey;
        $structure = $this->structureStatus($table, (bool) $newColumns, (bool) $oldColumns);

        $ids = [];
        if ($primaryKey) {
            if ($newColumns && $this->columnExists($newColumns, $primaryKey)) {
                $ids = array_merge($ids, $this->primaryKeyValues(self::NEW_CONNECTION, $table, $primaryKey));
            }
            if ($oldColumns && $this->columnExists($oldColumns, $primaryKey)) {
                $ids = array_merge($ids, $this->primaryKeyValues(self::OLD_CONNECTION, $table, $primaryKey));
            }
        }

        $ids = array_values(array_unique(array_map('strval', $ids)));
        sort($ids, SORT_NATURAL | SORT_FLAG_CASE);
        $ids = array_slice($ids, 0, self::ROW_LIMIT);

        return [
            'table' => $table,
            'new_exists' => (bool) $newColumns,
            'old_exists' => (bool) $oldColumns,
            'new_count' => $newColumns ? $this->countRows(self::NEW_CONNECTION, $table) : null,
            'old_count' => $oldColumns ? $this->countRows(self::OLD_CONNECTION, $table) : null,
            'new_primary_key' => $newPrimaryKey,
            'old_primary_key' => $oldPrimaryKey,
            'primary_key' => $primaryKey,
            'has_comparable_key' => (bool) $primaryKey,
            'structure' => $structure,
            'can_replace' => (bool) $newColumns && (bool) $oldColumns && (bool) $primaryKey && $structure['same'],
            'rows' => array_map(function (string $id) use ($table, $primaryKey, $newColumns, $oldColumns) {
                return [
                    'id' => $id,
                    'new' => $newColumns && $primaryKey ? $this->rowExists(self::NEW_CONNECTION, $table, $primaryKey, $id) : false,
                    'old' => $oldColumns && $primaryKey ? $this->rowExists(self::OLD_CONNECTION, $table, $primaryKey, $id) : false,
                ];
            }, $ids),
            'limit' => self::ROW_LIMIT,
        ];
    }

    public function rowDiff(string $table, string $id): array
    {
        $details = $this->tableDetails($table);
        $primaryKey = $details['primary_key'];

        if (!$primaryKey) {
            throw new InvalidArgumentException('This table does not have a single primary key to compare records.');
        }

        $newColumns = $this->columnsIfExists(self::NEW_CONNECTION, $table);
        $oldColumns = $this->columnsIfExists(self::OLD_CONNECTION, $table);
        $newRow = $newColumns ? $this->findRow(self::NEW_CONNECTION, $table, $primaryKey, $id) : null;
        $oldRow = $oldColumns ? $this->findRow(self::OLD_CONNECTION, $table, $primaryKey, $id) : null;

        $columnNames = array_values(array_unique(array_merge(
            array_keys($newColumns),
            array_keys($oldColumns)
        )));

        usort($columnNames, function (string $left, string $right) use ($newColumns, $oldColumns) {
            $leftOrder = $newColumns[$left]['position'] ?? $oldColumns[$left]['position'] ?? 9999;
            $rightOrder = $newColumns[$right]['position'] ?? $oldColumns[$right]['position'] ?? 9999;
            return $leftOrder <=> $rightOrder ?: strcmp($left, $right);
        });

        return [
            'table' => $table,
            'id' => $id,
            'primary_key' => $primaryKey,
            'new_exists' => $newRow !== null,
            'old_exists' => $oldRow !== null,
            'columns' => array_map(function (string $column) use ($newRow, $oldRow) {
                $newValue = $newRow ? ($newRow[$column] ?? null) : null;
                $oldValue = $oldRow ? ($oldRow[$column] ?? null) : null;

                return [
                    'name' => $column,
                    'new' => $newValue,
                    'old' => $oldValue,
                    'equal' => $this->normalizeValue($newValue) === $this->normalizeValue($oldValue),
                ];
            }, $columnNames),
        ];
    }

    public function replaceTableFromOldToNew(string $table): array
    {
        [$newColumns, $oldColumns, $primaryKey] = $this->validatedReplaceContext($table);

        $columns = array_keys($newColumns);
        $processed = 0;

        DB::connection(self::NEW_CONNECTION)->transaction(function () use ($table, $primaryKey, $columns, &$processed) {
            DB::connection(self::NEW_CONNECTION)->table($table)->delete();

            DB::connection(self::OLD_CONNECTION)
                ->table($table)
                ->select($columns)
                ->orderBy($primaryKey)
                ->chunk(500, function ($rows) use ($table, &$processed) {
                    $insertRows = [];

                    foreach ($rows as $row) {
                        $insertRows[] = (array) $row;
                        $processed++;
                    }

                    if ($insertRows) {
                        DB::connection(self::NEW_CONNECTION)->table($table)->insert($insertRows);
                    }
                });
        });

        return [
            'table' => $table,
            'processed' => $processed,
        ];
    }

    public function replaceRowFromOldToNew(string $table, string $id): array
    {
        [$newColumns, $oldColumns, $primaryKey] = $this->validatedReplaceContext($table);

        $oldRow = DB::connection(self::OLD_CONNECTION)
            ->table($table)
            ->select(array_keys($newColumns))
            ->where($primaryKey, $id)
            ->first();

        if (!$oldRow) {
            throw new InvalidArgumentException('The record must exist in the old database before replace.');
        }

        DB::connection(self::NEW_CONNECTION)->transaction(function () use ($table, $primaryKey, $id, $oldRow) {
            DB::connection(self::NEW_CONNECTION)
                ->table($table)
                ->where($primaryKey, $id)
                ->delete();

            DB::connection(self::NEW_CONNECTION)
                ->table($table)
                ->insert((array) $oldRow);
        });

        return [
            'table' => $table,
            'id' => $id,
        ];
    }

    public function clearNewTable(string $table): array
    {
        $this->assertKnownTable($table);

        if (!$this->columnsIfExists(self::NEW_CONNECTION, $table)) {
            throw new InvalidArgumentException('The table must exist in the new database before clear.');
        }

        $deleted = 0;

        DB::connection(self::NEW_CONNECTION)->transaction(function () use ($table, &$deleted) {
            $deleted = DB::connection(self::NEW_CONNECTION)->table($table)->count();
            DB::connection(self::NEW_CONNECTION)->table($table)->delete();
        });

        return [
            'table' => $table,
            'deleted' => $deleted,
        ];
    }

    private function validatedReplaceContext(string $table): array
    {
        $this->assertKnownTable($table);

        $newColumns = $this->columnsIfExists(self::NEW_CONNECTION, $table);
        $oldColumns = $this->columnsIfExists(self::OLD_CONNECTION, $table);
        $structure = $this->structureStatus($table, (bool) $newColumns, (bool) $oldColumns);
        $primaryKey = $this->singlePrimaryKey($newColumns);

        if (!$newColumns || !$oldColumns) {
            throw new InvalidArgumentException('The table must exist in both databases before replace.');
        }

        if (!$structure['same']) {
            throw new InvalidArgumentException('The table structure must be exactly the same before replace.');
        }

        if (!$primaryKey) {
            throw new InvalidArgumentException('The table must have a single primary key before replace.');
        }

        return [$newColumns, $oldColumns, $primaryKey];
    }

    private function safeConnectionStatus(string $connection): array
    {
        try {
            DB::connection($connection)->select('select 1');

            return [
                'ok' => true,
                'database' => DB::connection($connection)->getDatabaseName(),
                'message' => null,
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'database' => config('database.connections.' . $connection . '.database'),
                'message' => $exception->getMessage(),
            ];
        }
    }

    private function tables(string $connection): array
    {
        try {
            $rows = DB::connection($connection)->select(
                "SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME"
            );
        } catch (Throwable $exception) {
            return [];
        }

        return array_map(fn ($row) => $row->name, $rows);
    }

    private function columnsIfExists(string $connection, string $table): array
    {
        if (!in_array($table, $this->tables($connection), true)) {
            return [];
        }

        $rows = DB::connection($connection)->select(
            "SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA, ORDINAL_POSITION
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
             ORDER BY ORDINAL_POSITION",
            [$table]
        );

        $columns = [];
        foreach ($rows as $row) {
            $columns[$row->COLUMN_NAME] = [
                'name' => $row->COLUMN_NAME,
                'type' => $row->COLUMN_TYPE,
                'nullable' => $row->IS_NULLABLE,
                'key' => $row->COLUMN_KEY,
                'default' => $row->COLUMN_DEFAULT,
                'extra' => $row->EXTRA,
                'position' => (int) $row->ORDINAL_POSITION,
            ];
        }

        return $columns;
    }

    private function assertKnownTable(string $table): void
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            throw new InvalidArgumentException('Invalid table name.');
        }

        $known = array_merge($this->tables(self::NEW_CONNECTION), $this->tables(self::OLD_CONNECTION));
        if (!in_array($table, $known, true)) {
            throw new InvalidArgumentException('Table not found.');
        }
    }

    private function singlePrimaryKey(array $columns): ?string
    {
        $primaryKeys = array_values(array_filter($columns, fn (array $column) => $column['key'] === 'PRI'));

        return count($primaryKeys) === 1 ? $primaryKeys[0]['name'] : null;
    }

    private function columnExists(array $columns, string $column): bool
    {
        return array_key_exists($column, $columns);
    }

    private function primaryKeyValues(string $connection, string $table, string $primaryKey): array
    {
        return DB::connection($connection)
            ->table($table)
            ->select($primaryKey)
            ->orderBy($primaryKey)
            ->limit(self::ROW_LIMIT)
            ->pluck($primaryKey)
            ->all();
    }

    private function rowExists(string $connection, string $table, string $primaryKey, string $id): bool
    {
        return DB::connection($connection)
            ->table($table)
            ->where($primaryKey, $id)
            ->exists();
    }

    private function findRow(string $connection, string $table, string $primaryKey, string $id): ?array
    {
        $row = DB::connection($connection)
            ->table($table)
            ->where($primaryKey, $id)
            ->first();

        return $row ? (array) $row : null;
    }

    private function countRows(string $connection, string $table): ?int
    {
        try {
            return DB::connection($connection)->table($table)->count();
        } catch (Throwable $exception) {
            return null;
        }
    }

    private function estimatedRows(string $connection, string $table): ?int
    {
        try {
            $row = DB::connection($connection)->selectOne(
                "SELECT TABLE_ROWS AS rows_count FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
                [$table]
            );

            return $row ? (int) $row->rows_count : null;
        } catch (Throwable $exception) {
            return null;
        }
    }

    private function tableStatus(bool $inNew, bool $inOld): string
    {
        if ($inNew && $inOld) {
            return 'matched';
        }

        return $inNew ? 'new_only' : 'old_only';
    }

    private function structureStatus(string $table, bool $inNew, bool $inOld): array
    {
        if (!$inNew || !$inOld) {
            return [
                'same' => false,
                'label' => 'Not comparable',
                'differences' => ['Table only exists on one side.'],
            ];
        }

        $newColumns = $this->columnsIfExists(self::NEW_CONNECTION, $table);
        $oldColumns = $this->columnsIfExists(self::OLD_CONNECTION, $table);
        $columnNames = array_values(array_unique(array_merge(array_keys($newColumns), array_keys($oldColumns))));
        $differences = [];

        usort($columnNames, function (string $left, string $right) use ($newColumns, $oldColumns) {
            $leftOrder = $newColumns[$left]['position'] ?? $oldColumns[$left]['position'] ?? 9999;
            $rightOrder = $newColumns[$right]['position'] ?? $oldColumns[$right]['position'] ?? 9999;
            return $leftOrder <=> $rightOrder ?: strcmp($left, $right);
        });

        foreach ($columnNames as $columnName) {
            if (!isset($newColumns[$columnName])) {
                $differences[] = $columnName . ' missing in new tools';
                continue;
            }

            if (!isset($oldColumns[$columnName])) {
                $differences[] = $columnName . ' missing in old tools';
                continue;
            }

            foreach (['type', 'nullable', 'key', 'default', 'extra', 'position'] as $attribute) {
                if ((string) ($newColumns[$columnName][$attribute] ?? '') !== (string) ($oldColumns[$columnName][$attribute] ?? '')) {
                    $differences[] = $columnName . ' has different ' . $attribute;
                }
            }
        }

        return [
            'same' => count($differences) === 0,
            'label' => count($differences) === 0 ? 'Same' : count($differences) . ' differences',
            'differences' => array_slice($differences, 0, 8),
        ];
    }

    private function canReplaceTable(string $table, bool $inNew, bool $inOld, array $structure): bool
    {
        if (!$inNew || !$inOld) {
            return false;
        }

        $newColumns = $this->columnsIfExists(self::NEW_CONNECTION, $table);

        return $structure['same'] && (bool) $this->singlePrimaryKey($newColumns);
    }

    private function normalizeValue($value): string
    {
        if ($value === null) {
            return '__NULL__';
        }

        return (string) $value;
    }
}
