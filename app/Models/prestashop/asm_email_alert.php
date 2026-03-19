<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use App\Models\prestashop\product_attribute_combination;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\pack;

class asm_email_alert extends Model{
    
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."asm_email_alert";
    }

    public static function dashboard_product_request($type){
        
        $data = array();

        $clients_request = self::select('*', DB::RAW('ps_product.reference AS product_reference'), DB::RAW('ps_product_attribute.reference AS attr_reference'), DB::RAW('ps_asm_email_alert.date_add AS alert_date_add'), 'ps_product.cache_is_pack')
            ->leftJoin('ps_product', 'ps_asm_email_alert.id_product', 'ps_product.id_product')
            ->leftJoin('ps_product_attribute', 'ps_asm_email_alert.id_combination', 'ps_product_attribute.id_product_attribute')
            ->leftJoin('ps_stock_available', function($join){
                $join->on('ps_asm_email_alert.id_product', '=', 'ps_stock_available.id_product');
                $join->on('ps_asm_email_alert.id_combination', '=', 'ps_stock_available.id_product_attribute');
            })
            ->orderBy('ps_stock_available.quantity', 'DESC')
            ->get();


        //$clients_request = self::get();
        
        foreach($clients_request AS $request){

            $combination = '';
            
            $reference = (is_null($request->attr_reference)) ? $request->product_reference : $request->attr_reference;
            
            if($request->id_combination > 0){

                $combination = '';
                $combination_data = product_attribute_combination::with('attribute_lang')->where('id_product_attribute', $request->id_combination)->orderBy('id_product_attribute', 'DESC')->get();

                foreach($combination_data AS $attr) $combination.= $attr->attribute_lang->name . ' | ';
    
                $combination = substr($combination, 0, -3);
            }

            $pack = pack::select('quantity', 'id_product_item', 'id_product_attribute_item')->where('id_product_pack', $request->id_product)->first();
            
            if( $request->cache_is_pack == 1){
                $stock = stock_available::select('quantity')->where('id_product', $pack->id_product_item)->where('id_product_attribute', $pack->id_product_attribute_item)->first();
            }else{
                $stock = stock_available::select('quantity')->where('id_product', $request->id_product)->where('id_product_attribute', $request->id_combination)->first();
            }
            
            
            $date=date_create($request->alert_date_add);
                
            $combination = (strlen($combination)) ? ' - <span style="color: red;">' . $combination . '</span>' : '';
            $product = [
                'delete' => $request['id'],
                'id_product' => $request->id_product,
                'reference' => $reference . $combination,
                'cache_is_pack' => $request->cache_is_pack,
                'stock' => ( $request->cache_is_pack == 1 ) ? $request->quantity . ' <span style="color: red;">( ' . $stock->quantity . ' )</span> ' : $stock->quantity,
                'pack_quantity' => (isset($pack->quantity)) ? $pack->quantity : 0,
                'date' => date_format($date,"Y-m-d"),
                'send_email' => $request['id'],
                'email' => $request['email']
            ];
            
            $data[] = $product;
            
        }
        
        return [
            'name'              => trans('dashboard.Products requested'),
            'col'               => 4,
            'item_id'           => $type . '_products_requested',
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminProducts, 'controller' => 'AdminProducts', 'element' => 'id_product', 'extraParameters' => '&updateproduct' ] : [],
            'columns'           => ['delete', 'reference', 'stock', 'cache_is_pack', 'pack_quantity', 'email', 'send_email'],
            'table'             => 'asm_email_alert',
            'counter'           => count($data),
            'data'              => $data
        ];  
        
    }
}
