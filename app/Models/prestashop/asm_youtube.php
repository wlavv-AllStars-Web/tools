<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Config;

class asm_youtube extends Model{
    
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct(){
        $this->table = env('DB2_prefix')."asm_youtube";
    }

    public static function dashboard_broken_link($type){

        $data = array();

        $bd_data = self::get();

        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'youtube_code' => $item->youtube_code];
        
        return [
            'name'              => trans('dashboard.Youtube - Broken links'),
            'col'               => 4,
            'item_id'           => $type . '_youtube_broken_links',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'youtube_code'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }
}
