<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Config;

class cart_rules extends Model
{

    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct(){
        $this->table = env('DB2_prefix')."cart_rule";
    }

    public function country(){
        return $this->hasOne(country::class, "id_country", 'id_country');
    }
    
    public static function dashboard_inactive_cart_rules($type){

        $data = array();

        $prefix = env('DB2_DB_prefix');

        $array = asm_dashboard::getExceptions('inactive_cart_rules');
        
        $bd_data = self::where("active",  0)->whereNot("description", 'Bonus system')->whereNotIn('id_cart_rule', $array)->get();

        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_cart_rule, 'id_cart_rule' => $item->id_cart_rule, 'code' => $item->code, 'description' => $item->description];
        
        return [
            'name'              => trans('dashboard.Inactive cart rules'),
            'col'               => 4,
            'item_id'           => $type . '_inactive_cart_rules',
            'prestashop'        => ( isset ( Config::get('token')->AdminCartRules ) ) ? [ 'token' => Config::get('token')->AdminCartRules, 'controller' => 'AdminCartRules', 'element' => 'id_cart_rule', 'extraParameters' => '' ] : [],
            'columns'           => ['clean', 'id_cart_rule', 'code', 'description'],
            'counter'           => count($data),
            'exception_fields'  => ['inactive_cart_rules', 'id_cart_rule', 'code', 'description'],
            'data'              => $data
        ]; 
    }
}
