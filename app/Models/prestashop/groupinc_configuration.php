<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\prestashop\PrestashopModel;
use Illuminate\Support\Facades\DB;

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

        if (self::hasPrestashopTable(self::tableName('groupinc_configuration'))) {
            $bd_data = self::select('name', 'percentage', 'active')
                ->orderBy('active', 'DESC')
                ->get();

            foreach($bd_data AS $item) $data[] = ['name' => $item->name, 'percentage' => $item->percentage, 'active' => $item->active, 'shop' => 'ALL', 'group' => ''];
        } elseif (self::hasPrestashopTable(self::tableName('specific_price_rule'))) {
            $now = now()->format('Y-m-d H:i:s');
            $specificPriceRuleTable = self::tableName('specific_price_rule');

            $bd_data = DB::connection('mysql2')
                ->table($specificPriceRuleTable)
                ->select('name', 'reduction', 'reduction_type', 'id_shop', 'id_group', 'from', 'to')
                ->where(function ($query) use ($now) {
                    $query->whereNull('from')
                        ->orWhere('from', '0000-00-00 00:00:00')
                        ->orWhere('from', '<=', $now);
                })
                ->where(function ($query) use ($now) {
                    $query->whereNull('to')
                        ->orWhere('to', '0000-00-00 00:00:00')
                        ->orWhere('to', '>=', $now);
                })
                ->orderBy('id_shop')
                ->orderBy('name')
                ->get();

            foreach($bd_data AS $item) {
                $data[] = [
                    'name' => $item->name,
                    'percentage' => $item->reduction_type === 'percentage' ? $item->reduction : $item->reduction,
                    'active' => 1,
                    'shop' => ((int) $item->id_shop === 3) ? 'ASD' : (((int) $item->id_shop === 2) ? 'ASM' : 'ALL'),
                    'group' => $item->id_group,
                ];
            }
        }
        
        return [
            'name'              => trans("dashboard.Global discounts"),
            'col'               => 4,
            'item_id'           => $type . '_global_discounts',
            'columns'           => ['name', 'percentage', 'active', 'shop', 'group'],
            'counter'           => count($data),
            'info'              => true,
            'data'              => $data
        ];        
    }
}
