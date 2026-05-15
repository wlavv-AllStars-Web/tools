<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\prestashop\PrestashopModel;

class groupinc_configuration extends PrestashopModel
{
    use HasFactory;
    protected $fillable = ['name'];
    protected $table = 'ps_groupinc_configuration';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('groupinc_configuration');
    }

    public static function dashboard_global_discounts($type){

        $data = array();

        if (!self::hasPrestashopTable(self::tableName('groupinc_configuration'))) {
            return [
                'name'              => trans("dashboard.Global discounts"),
                'col'               => 4,
                'item_id'           => $type . '_global_discounts',
                'columns'           => ['name', 'percentage', 'active'],
                'counter'           => 0,
                'info'              => true,
                'data'              => $data
            ];
        }

        $bd_data = self::select('name', 'percentage', 'active')
            ->orderBy('active', 'DESC')
            ->get();

        foreach($bd_data AS $item) $data[] = ['name' => $item->name, 'percentage' => $item->percentage, 'active' => $item->active];
        
        return [
            'name'              => trans("dashboard.Global discounts"),
            'col'               => 4,
            'item_id'           => $type . '_global_discounts',
            'columns'           => ['name', 'percentage', 'active'],
            'counter'           => count($data),
            'info'              => true,
            'data'              => $data
        ];        
    }
}
