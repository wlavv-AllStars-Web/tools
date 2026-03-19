<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class product_comment extends Model{
    
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

    public function __construct(){
        $this->table = env('DB2_prefix')."product_comment";
    }

    public static function dashboard_reviews($type){

        $data = array();

        $bd_data = self::select( '*' )
            ->where( 'deleted', 0 )
            ->where( 'validate', 0 )
            ->count();
            
        $token = ( isset ( Config::get('token')->AdminProducts ) ) ? Config::get('token')->AdminModules : null;
        
        return [
            'name'              => trans('dashboard.Reviews'),
            'col'               => 4,
            'item_id'           => $type . '_reviews',
            'columns'           => [],
            'counter'           => $bd_data,
            'direct_link'       => 'https://www.all-stars-motorsport.com/admin77500/index.php?controller=AdminModules&token='. $token .'&configure=productcomments&tab_module=front_office_features&module_name=productcomments',
            'data'              => [$bd_data]
        ];        
    }

}