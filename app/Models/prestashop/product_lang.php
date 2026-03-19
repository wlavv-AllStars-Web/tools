<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class product_lang extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."product_lang";
    }

    public static function getProductName($id_product, $id_lang){
        return self::where('id_product', $id_product)->where('id_lang', $id_lang)->value('name');
    }
    
    public static function dashboard_no_availability_text($type){

        $data = array();

        $bd_data = self::select('ps_product.id_product', 'ps_product.reference', 'ps_product_lang.name')
            ->leftJoin('ps_product', 'ps_product_lang.id_product', '=', 'ps_product.id_product')
            ->where( 'ps_product_lang.available_now', '=', '' )
            ->orWhere( 'ps_product_lang.available_later', '=', '' )
            ->orWhere( 'ps_product_lang.available_soon_text', '=', '' )
            ->groupBy('ps_product_lang.id_product')
            ->get();

        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'name' => $item->name];
        
        return [
            'name'              => trans('dashboard.No availability text'),
            'col'               => 4,
            'item_id'           => $type . '_no_availability_text',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'name'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }
    
    public static function dashboard_double_spaces($type){

        $data = array();

        $bd_data = self::select('ps_product.id_product', 'ps_product.reference', 'ps_product_lang.name')
            ->leftJoin('ps_product', 'ps_product_lang.id_product', '=', 'ps_product.id_product')
            ->where( 'ps_product_lang.name', 'LIKE', '%  %' )
            ->groupBy('ps_product_lang.id_product')
            ->get();

        foreach($bd_data AS $item) $data[] = ['id_product' => $item->id_product, 'reference' => $item->reference, 'name' => $item->name];
        
        return [
            'name'              => trans('dashboard.PRODUCTS TITLE DOUBLE 2 SPACES CHARACTER'),
            'col'               => 4,
            'item_id'           => $type . '_titles_double_spaces',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['id_product', 'reference', 'name'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }
}
