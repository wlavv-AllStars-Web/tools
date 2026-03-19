<?php

namespace App\Models\modules\auto_orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\prestashop\Orders;

class cross_auto_orders extends Model
{
    use HasFactory;
    
    public static function getAllBrands(){

        $data = new cross_auto_orders();
        $brands = $data->select('id_manufacturer', 'manufacturer')->groupBy('manufacturer')->orderBy('manufacturer')->get();

        $dataRows = array();

        foreach($brands AS $brand){

            $brand_rows = new cross_auto_orders();
            
            $data_brand_Rows = $brand_rows->select('*', DB::raw('SUM(quantity) as quantity'))->where('manufacturer', '=', $brand->manufacturer)->groupBy('reference')->orderBy('reference')->get();

            $dataRows[$brand->manufacturer] = [
                'id_manufacturer' => $brand->id_manufacturer,
                'export' => '/admin/download/' . $brand->id_manufacturer . '_' . str_replace(' ', '_', $brand->manufacturer) . '_' . date('ymd') . '.csv',
                'name' => $brand->manufacturer,
                'products' => $data_brand_Rows,
                'counter'  => count($data_brand_Rows)
            ];
        }

        return $dataRows;
    }

    public static function insert($data, $origin){

        foreach($data AS $row){
  
            $exist = cross_auto_orders::where('reference', $row['reference'])->count();

            if( $exist > 0 ){

                $element = cross_auto_orders::where('reference', $row['reference'])->first();
                $element->quantity += $row['qtd_item'];
                $element->update();

            }else{

                $insert = new cross_auto_orders();
                $insert->origin = $origin;
                $insert->id_manufacturer = $row['id_manufacturer'];
                $insert->manufacturer = $row['name'];
                $insert->id_order = $row['id_order'];
                $insert->id_order_detail = $row['id_order_detail'];
                $insert->quantity = $row['qtd_item'];
                $insert->id_product = $row['id_product'];
                $insert->id_product_attribute = $row['id_product_attribute'];
                $insert->creation_date = date('Y-m-d');
                $insert->reference = '' . $row['reference'];
                $insert->attr_reference = '' . $row['attr_reference'];
                $insert->name = $row['product_name'];
                $insert->end_of_life = $row['end_of_life'] + 0;

                $insert->stock_arrive = $row['stock_arrive'] + 0;
                $insert->stock_arrivepa = $row['stock_arrivepa'] + 0;
                $insert->qtd_in_stock = $row['qtd_in_stock'] + 0;

                $insert->save();

            }
            
        }

    }

    public static function cleanAutoOrders($id, $url = 'local'){

        if($url == 'online'){

            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', 'all-stars-distribution.com/custom/api/autoOrders/cleanAutoOrders.php?id=' . $id);

        }else{
            auto_orders::where('id', '<' , ( $id + 1 ) )->update(['ordered' => 1]);
        }

    }
}