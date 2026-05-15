<?php

namespace App\Support\Dashboard;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use App\Models\prestashop\asm_dashboard;

class DashboardPanelBuilder
{
    public static function panel(
        string $name,
        string $type,
        string $suffix,
        array $columns,
        $data,
        array $extra = [],
        $prestashop = null,
        int $col = 4
    ): array {
        $rows = self::normalizeData($data);

        return array_merge([
            'name' => $name,
            'col' => $extra['col'] ?? $col,
            'item_id' => $type . '_' . $suffix,
            'prestashop' => array_key_exists('prestashop', $extra) ? $extra['prestashop'] : $prestashop,
            'columns' => $columns,
            'counter' => count($rows),
            'data' => $rows,
        ], self::withoutReservedKeys($extra));
    }

    public static function fromQuery(array $config): array
    {
        $query = $config['query'];

        if (!empty($config['exception_board'])) {
            self::applyExceptions(
                $query,
                $config['exception_board'],
                $config['exception_column'] ?? 'id_product',
                $config['exception_storage_field'] ?? 'id_product'
            );
        }

        $rows = ($query instanceof EloquentBuilder || $query instanceof QueryBuilder)
            ? $query->get()
            : collect($query);

        $data = $rows->map($config['map'])->values()->all();

        $extra = $config['extra'] ?? [];

        if (!empty($config['exception_fields'])) {
            $extra['exception_fields'] = $config['exception_fields'];
        }

        return self::panel(
            $config['name'],
            $config['type'],
            $config['suffix'],
            $config['columns'],
            $data,
            $extra,
            $config['prestashop'] ?? null,
            $config['col'] ?? 4
        );
    }

    public static function prestashopLink(string $controller, string $element, string $extraParameters = ''): array
    {
        $tokens = Config::get('token');

        if (!$tokens || !isset($tokens->{$controller})) {
            return [];
        }

        return [
            'token' => $tokens->{$controller},
            'controller' => $controller,
            'element' => $element,
            'extraParameters' => $extraParameters,
        ];
    }

    public static function exceptions(string $board, string $field = 'id_product'): array
    {
        return asm_dashboard::getExceptions($board)
            ->pluck($field)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->values()
            ->toArray();
    }

    public static function applyExceptions($query, string $board, string $queryColumn, string $storageField = 'id_product'): void
    {
        $exceptions = self::exceptions($board, $storageField);

        if (!empty($exceptions)) {
            $query->whereNotIn($queryColumn, $exceptions);
        }
    }

    public static function normalizeData($data): array
    {
        if ($data instanceof Collection) {
            return $data->values()->toArray();
        }

        if ($data instanceof EloquentBuilder || $data instanceof QueryBuilder) {
            return $data->get()->values()->toArray();
        }

        if (is_array($data)) {
            return array_values($data);
        }

        if ($data instanceof \Traversable) {
            return iterator_to_array($data, false);
        }

        return [];
    }

    protected static function withoutReservedKeys(array $extra): array
    {
        unset($extra['col'], $extra['prestashop']);

        return $extra;
    }
}
