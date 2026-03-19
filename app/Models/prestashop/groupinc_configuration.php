<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\country;

class groupinc_configuration extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."groupinc_configuration";
    }

    public static function dashboard_global_discounts($type){

        $data = array();

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
