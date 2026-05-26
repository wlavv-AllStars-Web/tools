<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\Prestashop\PrestashopAdminLinkService;

class asm_youtube extends PrestashopModel
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'youtube_broken_links';
    protected $fillable = ['name'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function dashboard_broken_link($type)
    {
        $data = [];

        if (!\Illuminate\Support\Facades\Schema::connection('mysql')->hasTable('youtube_broken_links')) {
            return [
                'name' => trans('dashboard.Youtube - Broken links'),
                'col' => 4,
                'item_id' => $type . '_youtube_broken_links',
                'columns' => ['id_product', 'youtube_code'],
                'counter' => 0,
                'data' => $data
            ];
        }

        $bd_data = self::get();

        foreach ($bd_data as $item) {
            $data[] = [
                'id_product' => $item->id_product,
                'youtube_code' => $item->youtube_code,
                'url' => PrestashopAdminLinkService::dashboardProductAdminUrl((int) $item->id_product, 'ASM'),
            ];
        }

        return [
            'name' => trans('dashboard.Youtube - Broken links'),
            'col' => 4,
            'item_id' => $type . '_youtube_broken_links',
            'prestashop' => PrestashopAdminLinkService::dashboardProductLink('id_product', 'ASM'),
            'columns' => ['id_product', 'youtube_code'],
            'counter' => count($data),
            'data' => $data
        ];
    }
}
