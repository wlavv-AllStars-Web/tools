<?php

namespace App\Models\modules\auto_orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;
use App\Models\prestashop\stock_available;


class auto_orders extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

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
    public STATIC function checkAutoOrders($data)
    {
        $full_array = array();

        foreach($data AS $brand){

            if($brand['id_manufacturer'] != 0){
                foreach($brand['products'] AS $item){

                    $position = array();

                    $position['id_manufacturer'] = $brand['id_manufacturer'];
                    $position['name'] = $brand['name'];
                    $position['id_category_default'] = $item['product']['id_category_default'];
                    $position['reference'] = $item['product']['reference'];
                    $position['stock_arrive'] = $item['product']['stock_arrive'];
                    $position['id_product'] = $item['product']['id_product'];
                    $position['product_name'] = $item['product']['name'];
                    $position['attr_reference'] = $item['product']['attr_reference'];
                    $position['stock_arrivepa'] = $item['product']['stock_arrivepa'];
                    $position['id_product_attribute'] = $item['product']['id_product_attribute'];
                    $position['qtd_in_stock'] = $item['product']['qtd_in_stock'];
                    $position['combination'] = $item['combination'];
                    $position['status'] = $item['product']['status'];
                    $position['order_status'] = $item['product']['order_status'];
                    $position['qtd_item'] = $item['product']['qtd_item'];

                    $full_array[] = $position;
                }
            }
        }

        $local_auto_orders = auto_orders::with('product', 'attribute', 'stockAvailableProduct', 'stockAvailableAttribute')->where('ordered', '0')->where('id', '>', 36249)->where('id_manufacturer', '>', '0')->get();

        foreach($local_auto_orders AS $brand){

            if($brand->id_manufacturer > 0){
                $position = array();
                $combination = null;

                $position['id_manufacturer'] = $brand->id_manufacturer;
                $position['name'] = $brand->manufacturer;
                $position['id_category_default'] = $brand->product->id_category_default;
                $position['reference'] = $brand->product->reference;
                $position['stock_arrive'] = $brand->product->stock_arrive;
                $position['id_product'] = $brand->product->id_product;
                $position['product_name'] = $brand->product->lang->name;

                if(isset($brand->attribute)){
                    $position['attr_reference'] = $brand->attribute->reference;
                    $position['stock_arrivepa'] = $brand->attribute->stock_arrivepa;
                    $position['id_product_attribute'] = $brand->attribute->id_product_attribute;
                    $position['qtd_in_stock'] = $brand->stockAvailableAttribute->quantity;    
                    $position['combination'] = product_attribute::getCombination($brand->attribute->id_product_attribute);
                }else{
                    $position['attr_reference'] = '';
                    $position['stock_arrivepa'] = 0;
                    $position['id_product_attribute'] = 0; 
                    $position['qtd_in_stock'] = $brand->stockAvailableProduct->quantity;                   
                    $position['combination'] = '';
                }

                $position['status'] = $brand->status;
                $position['order_status'] = $brand->order_status;
                $position['qtd_item'] = $brand->quantity;

                $full_array[] = $position;
            }

        }

        $array = collect($full_array)->sortBy('name')->toArray();
        $array_2 = $array;

        foreach($array AS $i => $first){
            foreach($array_2 AS $j => $second){
                if( isset($array[$i]) ){
                    if($j > $i){
                        if( ( $first['reference'] == $second['reference'] ) && ( $first['attr_reference'] == $second['attr_reference'] ) && ( $i != $j ) ){
                            $array[$i]['qtd_item'] += $second['qtd_item'];
                            unset($array[$j]);
                        }
                    }
                }
            }
        }

        $array = collect($array)->sortBy('name')->toArray();


        $final_array = array();
        $productsOfTheBrand = array();

        $initial_index = key($array);

        $name = $array[$initial_index]['name'];

        foreach($array AS $i => $detail){

            if($name == $detail['name']){
                $productsOfTheBrand[] = $detail;
            }else{

                self::createCSV($detail['id_manufacturer'], $name, $productsOfTheBrand);

                $final_array[$name] = [
                    'id_manufacturer' => $detail['id_manufacturer'],
                    'export' => 'export-'.$detail['id_manufacturer'],
                    'name' => $name,
                    'products' => $productsOfTheBrand,
                    'counter'  => count($productsOfTheBrand)
                ];

                $name = $detail['name'];

                $productsOfTheBrand = array();
                $productsOfTheBrand[] = $detail;
            }
        }
        
        self::createCSV($detail['id_manufacturer'], $name, $productsOfTheBrand);

        $final_array[$name] = [
            'id_manufacturer' => $detail['id_manufacturer'],
            'export' => 'export-'.$detail['id_manufacturer'],
            'name' => $name,
            'products' => $productsOfTheBrand,
            'counter'  => count($productsOfTheBrand)
        ];

        return $final_array;
    }

    public static function createCSV($id_manufacturer, $name, $array){

        $fileName = $id_manufacturer . '_' . $name . '_' . date('ymd') . '.csv';
        
        $filePath = asset('storage/' . $fileName);

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array(
            'SKU', 
            'Qtity'
        );

        $file = fopen(storage_path() . '/' . $fileName, 'w');
        fputcsv($file, $columns, ';');
        foreach ($array as $item){

            if($item['id_product_attribute'] == 0 ){
                fputcsv($file, [$item['reference'], $item['qtd_item']], ';');
            }else{
                fputcsv($file, [$item['attr_reference'], $item['qtd_item']], ';');
            }
        }

        fclose($file);

        return $filePath; 
    }

    /**
    public static function createZip($array){

        $fileName = 'All_' . date('ymd') . '.zip';
        
        $filePath = asset('storage/' . $fileName);

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array(
            'SKU', 
            'Qtity'
        );

        $file = fopen(storage_path() . '/' . $fileName, 'w');
        fputcsv($file, $columns, ';');
        foreach ($array as $item){

            if($item['id_product_attribute'] == 0 ){
                fputcsv($file, [$item['reference'], $item['qtd_item']], ';');
            }else{
                fputcsv($file, [$item['attr_reference'], $item['qtd_item']], ';');
            }
        }

        fclose($file);

        return $filePath; 
    }
    **/
}
