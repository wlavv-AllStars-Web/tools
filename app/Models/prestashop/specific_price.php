<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\prestashop\country;
use App\Models\prestashop\product;

class specific_price extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."specific_price";
    }

    public static function dashboard_same_sku_diff_discount($type){

        $data = array();
        
        $array_exceptions = asm_dashboard::getExceptions('same_sku_diff_discount');

        $bd_product = product::select('reference', DB::RAW('count(*) AS counter'))->where('active', 1)->whereNotIn('id_product', $array_exceptions)->groupBy('reference')->having('counter', '>', 1)->get();
        
        foreach($bd_product AS $item){

            $array_products = product::select('id_product')->where('reference', $item->reference)->get();

            $array = [];
            foreach($array_products AS $product){ $array[] = $product->id_product; }

            $distinct = self::select('reduction')
            ->distinct()
            ->whereIn('id_product',$array)
            ->where('id_cart', 0)
            ->where('id_customer', 0)
            ->get();

            if( count($distinct) > 1) $data[] = ['reference' => $item->reference ];
            
        }
        
        foreach($bd_product AS $item){

            $array_products = product::select('id_product')->where('reference', $item->reference)->get();

            $array = [];
            foreach($array_products AS $product) $array[] = $product->id_product;
            
            $products = product::whereIn('id_product',$array)->count();
            $specific_price = self::whereIn('id_product',$array)->count();

            if( ( $products != $specific_price ) && ( $specific_price > 0 ) ) $data[] = ['reference' => $item->reference ];
        }

        return [
            'name'              => trans("dashboard.Same SKU diff discount"),
            'col'               => 4,
            'item_id'           => $type . '_same_sku_diff_discount',
            'columns'           => ['reference'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }
    
}
