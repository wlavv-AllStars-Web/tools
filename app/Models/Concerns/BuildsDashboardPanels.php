<?php

namespace App\Models\Concerns;

use App\Support\Dashboard\DashboardPanelBuilder;

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
        return DashboardPanelBuilder::panel(
            $name,
            $type,
            $suffix,
            $columns,
            $data,
            $extra,
            $prestashop ?? [],
            $col
        );
    }
}
