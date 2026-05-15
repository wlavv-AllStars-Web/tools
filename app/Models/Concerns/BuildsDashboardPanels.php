<?php

namespace App\Models\Concerns;

trait BuildsDashboardPanels
{
    protected static function dashboardPanel(
        $name,
        $type,
        $suffix,
        array $columns,
        $data,
        array $extra = [],
        $prestashop = null,
        int $col = 4
    ): array {
        $data = is_array($data) ? $data : $data->toArray();

        return array_merge([
            'name' => $name,
            'col' => $col,
            'item_id' => $type . '_' . $suffix,
            'prestashop' => $prestashop ?? [],
            'columns' => $columns,
            'counter' => count($data),
            'data' => $data,
        ], $extra);
    }
}
