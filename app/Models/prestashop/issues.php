<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Config;

class issues extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."asm_tables";
    }
    
    public static function saveReport($id_type, $title, $message, $id_product, $id_product_attribute, $reference)
    {    
        issues::insert(
            [
                'id_type' => $id_type,
                'asm_year' => date('Y'),
                'asm_month' => date('m'),
                'done' => 0,
                'approved' => 0,
                'field_1' => $title,
                'field_2' => $message,
                'field_3' => $id_product,
                'field_4' => $id_product_attribute,
                'field_5' => $reference
            ]
        );
    }
    
    public static function saveWarranty($id_order, $reference, $description)
    {    
        issues::insert(
            [
                'id_type' => 13,
                'asm_year' => date('Y'),
                'asm_month' => date('m'),
                'done' => 0,
                'approved' => 0,
                'field_1' => date('d/m/Y'),
                'field_2' => $id_order,
                'field_3' => '',
                'field_4' => $reference,
                'field_5' => 'AUTO WARRANTIES | WARRANTY OF ' . $reference . ' FROM ORDER: ' . $id_order,
                'field_6' => '',
            ]
        );
    }
    
    public static function saveReturn($id_order, $reference, $quantity, $shipping_date, $auto_code, $reason)
    {    
        issues::insert(
            [
                'id_type' => 17,
                'asm_year' => date('Y'),
                'asm_month' => date('m'),
                'done' => 0,
                'approved' => 0,
                'field_1' => date('d/m/Y'),
                'field_3' => $id_order,
                'field_4' => $reference,
                'field_5' => $quantity,
                'field_6' => 'AUTO Returns',
                'field_7' => $shipping_date,
                'field_8' => $auto_code,
                'field_9' => '',
                'field_10' => $reason,
                'field_12' => 'Devolução',
            ]
        );
    }

    public static function dashboard_warranties($type){

        $data = array();
        $bd_data = self::where('id_type', 13)->where('done', 0)->get();

        foreach($bd_data AS $item) $data[] = ['date' => $item->field_1, 'brand' => $item->field_3, 'reference' => $item->field_4, 'action' => $item->field_6, 'type' => $item->id_type];
        
        return [
            'name'          => trans('dashboard.WARRANTIES'),
            'col'           => 4,
            'item_id'       => $type . '_warranties',
            'prestashop'    => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminWmModuleTable, 'controller' => 'AdminWmModuleTable', 'element' => 'type', 'extraParameters' => '' ] : [],
            'columns'       => ($type == 'panel') ? ['date', 'brand', 'reference', 'action'] : ['reference', 'action'],
            'counter'       => count($data),
            'data'          => $data
        ];        
    }

    public static function dashboard_returns($type){

        $data = array();
        $bd_data = self::where('id_type', 17)->where('done', 0)->where('field_12', '<>', "Garantia")->get();

        foreach($bd_data AS $item) $data[] = ['date' => $item->field_1, 'order' => $item->field_3, 'status' => $item->field_9, 'operation' => $item->field_12, 'type' => $item->id_type];
        
        return [
            'name'          => trans('dashboard.RETURNS'),
            'col'           => 4,
            'item_id'       => $type . '_RETURNS',
            'prestashop'    => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminWmModuleTable, 'controller' => 'AdminWmModuleTable', 'element' => 'type', 'extraParameters' => '' ] : [],
            'columns'       => ($type == 'panel') ? ['date', 'order', 'status', 'operation'] : ['order', 'operation'],
            'counter'       => count($data),
            'data'          => $data
        ];        
    }

    public static function saveCurrencyRate($request){
        
        $rate = self::where('id_type', 99)->first();
        $rate->asm_year=date('Y');
        $rate->asm_month=date('m');
        $rate->done=date('d');
        $rate->field_1=str_replace(",", ".", $request->yuan);
        $rate->field_2=str_replace(",", ".", $request->pound);
        $rate->field_3=str_replace(",", ".", $request->dollar);
        $rate->field_4=str_replace(",", ".", $request->yen);
        $rate->save();

        return 1;
        
    }

    public static function getCurrencyRates(){
        return self::where('id_type', 99)->first();
    }
    
}