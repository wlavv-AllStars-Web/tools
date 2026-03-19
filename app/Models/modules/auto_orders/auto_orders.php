<?php

namespace App\Models\modules\auto_orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\modules\auto_orders\cross_auto_orders;
use App\Models\modules\auto_orders\auto_orders_purchase_list;

use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;
use App\Models\prestashop\stock_available;


class auto_orders extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;
    private static $onlinePath = 'https://www.all-stars-distribution.com';

    public function __construct(){
        $this->table = env('DB2_prefix')."asm_orders";
    }

    public function product(){
        return $this->hasOne(product::class, "id_product", 'id_product');
    }

    public function attribute(){
        return $this->hasOne(product_attribute::class, "id_product_attribute", 'id_product_attribute');
    }
    
    public function stockAvailableProduct(){   
        return $this->hasOne(stock_available::class, "id_product", 'id_product');
    }
    
    public function stockAvailableAttribute (){
        return $this->hasOne(stock_available::class, "id_product_attribute", 'id_product_attribute');
    }

    /** WHILE MULTI STORE NOT READY **/
    public STATIC function checkAutoOrders(){

        $local = new cross_auto_orders();

        $data = self::getExternalDataByGET('/custom/api/autoOrders/list.php');

        if(isset($data) && (count($data) > 0)){
            $full_ASD = array();
            $id_auto_order = 0;
            foreach($data AS $brand){
                if($brand['id_manufacturer'] != 0){
                    foreach($brand['products'] AS $item){

                        $position = array();
                        
                        $position['id_order'] = $item['product']['id_order'];
                        $position['id_order_detail'] = $item['product']['id_order_detail'];
                        $position['id_manufacturer'] = $brand['id_manufacturer'];
                        $position['name'] = $brand['name'];
                        $position['reference'] = $item['product']['reference'];
                        $position['stock_arrive'] = $item['product']['stock_arrive'];
                        $position['id_product'] = $item['product']['id_product'];
                        $position['product_name'] = $item['product']['name'];
                        $position['attr_reference'] = $item['product']['attr_reference'];
                        $position['stock_arrivepa'] = $item['product']['stock_arrivepa'];
                        $position['id_product_attribute'] = $item['product']['id_product_attribute'];
                        $position['qtd_in_stock'] = $item['product']['qtd_in_stock'];
                        $position['qtd_item'] = $item['product']['qtd_item'];
                        $position['end_of_life'] = 0;
                        $full_ASD[] = $position;
                    }

                    $id_auto_order = ($item['product']['id'] > $id_auto_order) ? $item['product']['id'] : $id_auto_order;
                }
            }
            $local->insert($full_ASD, 'ASD');
            $local->cleanAutoOrders($id_auto_order, 'online');
        }

        $local_auto_orders = auto_orders::with('product', 'attribute', 'stockAvailableProduct', 'stockAvailableAttribute')->where('ordered', '0')->where('id_manufacturer', '>', '0')->get();
        
        if(count($local_auto_orders) > 0){
            $full_ASM = array();
            $id_auto_order = 0;
            foreach($local_auto_orders AS $brand){

                if( ($brand->id_manufacturer > 0 ) && (isset( $brand->product->reference ))){
                    $position = array();
                    $combination = null;

                    $position['id_manufacturer'] = $brand->id_manufacturer;
                    $position['name'] = $brand->manufacturer;
                    $position['reference'] = $brand->product->reference;
                    $position['end_of_life'] = $brand->product->wm_deprecated;
                    $position['id_product'] = $brand->product->id_product;
                    $position['product_name'] = $brand->product->lang->name;
                    $position['id_order'] = $brand->id_order;
                    $position['id_order_detail'] = $brand->id_order_detail;

                    if(isset($brand->attribute)){
                        $position['attr_reference'] = $brand->attribute->reference;
                        $position['stock_arrive'] = 0;
                        $position['stock_arrivepa'] = $brand->attribute->stock_arrivepa;
                        $position['id_product_attribute'] = $brand->attribute->id_product_attribute;
                        $position['qtd_in_stock'] = $brand->stockAvailableAttribute->quantity;    
                    }else{
                        $position['stock_arrive'] = $brand->product->stock_arrive;
                        $position['attr_reference'] = '';
                        $position['stock_arrivepa'] = 0;
                        $position['id_product_attribute'] = 0; 
                        $position['qtd_in_stock'] = $brand->stockAvailableProduct->quantity;                   
                    }
                    $position['qtd_item'] = $brand->quantity;

                    $full_ASM[] = $position;
                }
                $id_auto_order = $brand->id;
            }
            $local->insert($full_ASM, 'ASM');
            $local->cleanAutoOrders($id_auto_order);
        }

        return $local->getAllBrands();
    }

    public static function getExternalDataByGET($url){

        $data = [];
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', self::$onlinePath . $url);
        
        if($response->getStatusCode() == 200) $data = json_decode($response->getBody(), true);

        return $data;

    }

    public static function getExternalDataByPOST($url, $params){

        $data = [];
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', self::$onlinePath . $url, [ 
            'headers' => [
                    'User-Agent' => 'Firefox/1.0',
                    'Accept' => 'application/json', 
                    'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => $params
        ]);

        if($response->getStatusCode() == 200) $data = json_decode($response->getBody()->getContents(), true);

        return $data;

    }

}
