<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;

class asm_youtube extends PrestashopModel
{
    use HasFactory;

    protected $fillable = ['name'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('asm_youtube');
    }

    public static function dashboard_broken_link($type)
    {
        $data = [];

        if (!self::hasPrestashopTable(self::tableName('asm_youtube'))) {
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
            ];
        }

        return [
            'name' => trans('dashboard.Youtube - Broken links'),
            'col' => 4,
            'item_id' => $type . '_youtube_broken_links',
            'prestashop' => (isset(Config::get('token')->AdminProducts))
                ? [
                    'token' => Config::get('token')->AdminProducts,
                    'controller' => 'AdminProducts',
                    'element' => 'id_product',
                    'extraParameters' => '&updateproduct'
                ]
                : [],
            'columns' => ['id_product', 'youtube_code'],
            'counter' => count($data),
            'data' => $data
        ];
    }
}
