<?php

namespace App\Models\modules\shipping_erp;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\modules\bms_procurement\bms_procurement_purchase_order_product;

class shipping_erp extends Model{
    
    use HasFactory;
    protected $table = "shipping_erp";
    public $primaryKey = 'id';
    public $timestamps = false;
    
    public static function saveERPRelation($id_shipping, $erp_data){
        
        $data = array();

        self::where('id_shipping', $id_shipping)->delete();
        
        foreach($erp_data AS $erp){
            
            if(strlen($erp)  > 0){
                $shipping_erp = new shipping_erp();
                $shipping_erp->id_shipping = $id_shipping;
                $shipping_erp->id_erp = $erp;
                $shipping_erp->save();
            }
            
        }

    }
    
    public static function getShipping_ERP($id_shipping, $id_erp){
        return self::where('id_shipping', $id_shipping)->where('id_erp', $id_erp)->count();
    }
    
    public static function getOrders($id_shipping){
        return self::where('id_shipping', $id_shipping)->get();
    }
    
    public static function getProductsOfERP($id_shipping){
        
        $products = array();
        
        $shipment = self::where('id_shipping', $id_shipping)->get();
        
        foreach($shipment AS $erp){
            
            $products[] = bms_procurement_purchase_order_product::getDetailProductsOfOrder($erp->id_erp);
            
        }
        
        return $products;

    }
    
}
