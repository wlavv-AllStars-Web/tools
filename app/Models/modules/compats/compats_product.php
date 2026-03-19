<?php

namespace App\Models\modules\compats;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\modules\compats\compats;
use App\Models\prestashop\product;
use App\Models\prestashop\product_shop;

class compats_product extends Model
{
    use HasFactory;
    protected $table = "compats_product";
    public $primaryKey = 'id_compat_product';
    
    public static function getProductIds($data){
        
        $id_compat = self::existCompat( $data->id_brand, $data->id_model, $data->id_type, $data->id_version, $data->store );

        return (object)[
            'image' => "https://webtools.all-stars-motorsport.com/uploads/compats/compat/" . $id_compat . ".png",
            'products' => compats_product::select('reference')->join(env('DB2_DB_prefix')."product", 'compats_product.id_product', '=', env('DB2_DB_prefix')."product.id_product")->where('id_compat', $id_compat)->where('store', $data->store)->pluck('reference')->toArray()
        ];
    }

    public static function existCompat($id_brand, $id_model, $id_type, $id_version, $store = 0){
        $compat = compats::where('store', $store)->where('id_brand', $id_brand)->where('id_model', $id_model)->where('id_type', $id_type)->where('id_version', $id_version)->first();
        return (isset($compat->id_compat)) ? $compat->id_compat : 0;
    }

    public static function insertNestedCompat($data){
        
        $id_compat = self::existCompat( $data->id_brand, $data->id_model, $data->id_type, $data->id_version, $data->store );

        if($id_compat == 0) $id_compat = compats::createCompat( $data->id_brand, $data->id_model, $data->id_type, $data->id_version, $data->store );

        $products_data = $data->products;

        if (strpos($products_data, ';') !== false) {
            $products = explode(';', $products_data);
        } else {
            $products[] = $products_data;
        }

        foreach($products AS $product){
            $product_info = product::select('id_product')->where('reference', $product)->first();
            if(isset( $product_info->id_product )) self::insertSimpleCompat($id_compat, $product, $data->store);
        }
        
        return 1;
    }
    
    public static function insertSimpleCompat($id_compat, $reference, $store = 0){
        
        $products = product::select('id_product')->where('reference', $reference)->get();
        
        foreach( $products AS $product){
            
            $product_store = product_shop::select('id_shop')->where('id_product', $product->id_product)->where('id_shop', $store )->first();

            if(isset( $product_store->id_shop )){

                $exists = compats_product::where('id_product', $product->id_product)->where('id_compat', $id_compat)->where('store', $store)->count();
    
                if($exists < 1){
                    $new = new compats_product();
                    $new->id_compat = $id_compat;
                    $new->id_product = $product->id_product;
                    $new->store = $store;
                    $new->save();
                }
                
            }
        }
        
        return 1;
    }

    /** API Functions **/
    public static function getProducts($id_compat, $store=0){
        return compats_product::select('id_product')->where('id_compat', $id_compat)->where('store', $store)->pluck('id_product')->toArray();
    }
    
    public static function getCompats($id_product, $store=0){

        $compats = compats_product::select('id_compat')->where('id_product', $id_product)->where('store', $store)->get();
        
        $compat_array = array();
        
        foreach($compats AS $compat){
            
            $compat_info = compats::with('brand', 'model', 'type', 'version')->where('id_compat', $compat->id_compat)->where('store', $store)->first();

            $compat_array[] = (object)[
                'id_compat'  => $compat_info->id_compat,
                'brand'  => $compat_info->brand->name,
                'model'  => $compat_info->model->name,
                'type'   => $compat_info->type->name,
                'version'=> $compat_info->version->name,
            ];
        }
        
        return $compat_array;
    }
    
    public static function createCompat($brand, $model, $type, $version, $product, $store){
        
        $compats = compats::getAllCompatsFromFilter($brand, $model, $type, $version, $store);

        foreach($compats AS $compat){
            
            $already_exist_compat = compats_product::where('id_compat', $compat->id_compat)->where('id_product', $product)->where('store', $store)->first();
            
            if( ( isset($compat->id_compat) ) && ( !isset( $already_exist_compat->id_product ) ) ){
                $new = new compats_product();
                $new->id_compat = $compat->id_compat;
                $new->id_product = $product;
                $new->store = $store;
                $new->save();
            }
        
        }
        
        return 1;
    }
    
    public static function removeCompat($id_compat, $store){
        compats_product::where('id_compat', $id_compat)->where('store', $store)->delete();
        return 1;
    }
    
    /** API Functions **/
}