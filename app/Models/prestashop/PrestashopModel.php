<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PrestashopModelLogger;
use App\Models\Concerns\BuildsDashboardPanels;
use App\Services\Prestashop\PrestashopAdminLinkService;
use App\Models\prestashop\asm_dashboard;
use Illuminate\Support\Facades\Schema;

class PrestashopModel extends Model{
    
    use PrestashopModelLogger;
    use BuildsDashboardPanels;

    protected $connection = 'mysql2';
    public $timestamps = false;

    protected static function prefix(): string{
        return env('DB2_DB_prefix', 'ps_');
    }

    protected static function tableName(string $table): string{
        return static::prefix() . $table;
    }

    protected static function unqualifiedTableName(string $table): string
    {
        return str_contains($table, '.') ? substr($table, strrpos($table, '.') + 1) : $table;
    }

    protected static function hasPrestashopTable(string $table): bool
    {
        return Schema::connection('mysql2')->hasTable(static::unqualifiedTableName($table));
    }

    protected static function hasPrestashopColumn(string $table, string $column): bool
    {
        $table = static::unqualifiedTableName($table);

        return static::hasPrestashopTable($table) && Schema::connection('mysql2')->hasColumn($table, $column);
    }

    protected static function adminProductLink(string $store = 'ASM'): array
    {
        return PrestashopAdminLinkService::dashboardProductLink('id_product', $store);
    }

    protected static function dashboardExceptions(string $board, string $column = 'id_product'): array
    {
        return asm_dashboard::getExceptions($board)
            ->pluck($column)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->values()
            ->all();
    }

    protected static function adminOrderLink(string $store = 'ASM'): array
    {
        return PrestashopAdminLinkService::dashboardOrderLink('id_order', $store);
    }

    protected static function adminCustomerLink(string $store = 'ASM'): array
    {
        return PrestashopAdminLinkService::dashboardCustomerLink('id_customer', $store);
    }

    protected static function adminLinkFromLegacyController(string $controller, string $element, string $extraParameters = '', string $store = 'ASM'): array
    {
        return PrestashopAdminLinkService::fromLegacyController($controller, $element, $extraParameters, $store);
    }
}
