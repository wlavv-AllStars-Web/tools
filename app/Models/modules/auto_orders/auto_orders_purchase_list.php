<?php

namespace App\Models\modules\auto_orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\orders_details;
use App\Models\modules\auto_orders\auto_orders;
use App\Models\modules\supplier_warranty_issues\supplier_warranty_issues;

class auto_orders_purchase_list extends Model
{
    use HasFactory;
    protected $table = "auto_orders_purchase_list";

    public static function getAll($id_supplier){
        
        $data = array();
        
        $suppliers = auto_orders_purchase_list::select('id_supplier', 'supplier')->groupBy('id_supplier')->orderBy('supplier')->get();
        $references = auto_orders_purchase_list::pluck('reference');

        $references_to_check = array();
        foreach($references AS $reference){
            $references_to_check[] = $reference;
        }
        
        $stream = auto_orders::getExternalDataByPOST('/custom/api/autoOrders/getSold.php', ['references' => $references_to_check] );

        $data_stream = $stream['data'];
        
        foreach($suppliers AS $supplier){

            $products = array();
            $products_data = auto_orders_purchase_list::where('id_supplier', $supplier->id_supplier)->get();

            if($id_supplier != $supplier->id_supplier){
 
                $data[$supplier->id_supplier] = [
                    'id_supplier'   => $supplier->id_supplier,
                    'supplier'      => $supplier->supplier,
                    'rows'          => count($products_data),
                    'only_counters' => true
                ];
            }else{
                
                foreach($products_data AS $product){
    
                    $counter = product::where('reference', $product['reference'])->count();
                    $notes = '';
                    if($counter > 0){
                        $arrive = product::select('id_product', 'stock_arrive', 'notes')->where('reference', $product['reference'])->first();
                        $arrive['id_product_attribute'] = 0;
                        $qtd_arrive = $arrive['stock_arrive'];
                        $notes = $arrive['notes'];
                    }else{
                        $arrive = product_attribute::select('id_product', 'id_product_attribute', 'stock_arrivepa')->where('reference', $product['reference'])->first();
                        
                        if(is_null($arrive)){
                            $qtd_arrive = 0;
                        }else{
                            $qtd_arrive = $arrive['stock_arrivepa'];
                        }
                    }
                    
                    if(is_null( $arrive )){
                        $stock_product = null;
                        $sold = null;
                    }else{
                        $stock_product = stock_available::select('quantity')->where('id_product', $arrive['id_product'])->where('id_product_attribute', $arrive['id_product_attribute'])->first();
                        $sold = orders_details::getSoldByRefOf( $product['reference'] );
                    }
                    if(isset($data_stream[$product['reference']])) $sold += $data_stream[$product['reference']];
                    
                    $products[] = [
                        'reference' => $product['reference'],
                        'name' => $product['name'],
                        'warranty' => $product['warranty'],
                        'quantity' => $product['quantity'],
                        'stock' => (is_null( $stock_product )) ? 0 : $stock_product['quantity'],
                        'arrive' => $qtd_arrive,
                        'sold' => (is_null( $sold )) ? 0 : $sold,
                        'notes'=> $notes
                        
                    ];
                    
                }

                $data[$supplier->id_supplier] = [
                    'id_supplier' => $supplier->id_supplier,
                    'supplier'    => $supplier->supplier,
                    'rows'        => $products
                ];
            
            }
        }
        
        return $data;

    }

    public static function insertFromWarranty($id){

        $data_issue = supplier_warranty_issues::with('supplier')->where('id', $id)->first();
        
        $product = product::with('lang')->where('reference', $data_issue->reference )->first();
        
        if(isset($product->id_product)){
            $insert = new auto_orders_purchase_list();
            $insert->name = $product->lang->name;
            $insert->id_supplier = $data_issue->id_supplier;
            $insert->supplier = $data_issue->supplier->name;
            $insert->reference = $data_issue->reference;
            $insert->quantity = 1;
            $insert->sold = 0;
            $insert->warranty = 1;
            $insert->save();
        }

    }
}
