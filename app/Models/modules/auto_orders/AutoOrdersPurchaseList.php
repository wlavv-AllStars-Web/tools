<?php

namespace App\Models\modules\auto_orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\prestashop\product;
use App\Models\prestashop\orders_details;
use App\Models\modules\supplier_warranty_issues\supplier_warranty_issues;

class AutoOrdersPurchaseList extends Model
{
    use HasFactory;
    protected $table = 'auto_orders_purchase_list';

    public static function getAll($id_supplier){
        
        $data = array();
        
        $suppliers = AutoOrdersPurchaseList::select('id_supplier', 'supplier')->groupBy('id_supplier')->orderBy('supplier')->get();
        $data_stream = [];
        
        foreach($suppliers AS $supplier){

            $products = array();
            $products_data = AutoOrdersPurchaseList::where('id_supplier', $supplier->id_supplier)->get();

            if($id_supplier != $supplier->id_supplier){
 
                $data[$supplier->id_supplier] = [
                    'id_supplier'   => $supplier->id_supplier,
                    'supplier'      => $supplier->supplier,
                    'rows'          => count($products_data),
                    'only_counters' => true
                ];
            }else{
                
                foreach($products_data AS $product){
    
                    $productInfo = self::getPrestashopProductInfo($product['reference']);

                    if(is_null($productInfo)){
                        $stock = 0;
                        $qtd_arrive = 0;
                        $notes = '';
                        $sold = null;
                    }else{
                        $stock = $productInfo->quantity;
                        $qtd_arrive = $productInfo->stock_arrive;
                        $notes = $productInfo->notes;
                        $sold = orders_details::getSoldByRefOf( $product['reference'] );
                    }
                    if(isset($data_stream[$product['reference']])) $sold += $data_stream[$product['reference']];
                    
                    $products[] = [
                        'reference' => $product['reference'],
                        'name' => $product['name'],
                        'warranty' => $product['warranty'],
                        'quantity' => $product['quantity'],
                        'stock' => $stock,
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

    protected static function getPrestashopProductInfo(string $reference): ?object
    {
        $prefix = env('DB2_DB_prefix', 'ps_');

        return DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->leftJoin($prefix . 'product_attribute as pa', function ($join) use ($reference) {
                $join->on('pa.id_product', '=', 'p.id_product')
                    ->where('pa.reference', $reference);
            })
            ->leftJoin($prefix . 'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->leftJoin($prefix . 'custom_product_attribute as cpa', function ($join) {
                $join->on('cpa.id_product', '=', 'p.id_product')
                    ->on('cpa.id_product_attribute', '=', 'pa.id_product_attribute');
            })
            ->leftJoin($prefix . 'stock_available as sa', function ($join) {
                $join->on('sa.id_product', '=', 'p.id_product')
                    ->whereRaw('sa.id_product_attribute = COALESCE(pa.id_product_attribute, 0)');
            })
            ->where(function ($query) use ($reference) {
                $query->where('p.reference', $reference)
                    ->orWhere('pa.reference', $reference);
            })
            ->select(
                DB::raw('p.id_product as id_product'),
                DB::raw('COALESCE(pa.id_product_attribute, 0) as id_product_attribute'),
                DB::raw('COALESCE(sa.quantity, 0) as quantity'),
                DB::raw('COALESCE(cpa.stock_arrive, cp.stock_arrive, 0) as stock_arrive'),
                DB::raw('COALESCE(cp.notes, "") as notes')
            )
            ->first();
    }

    public static function insertFromWarranty($id){

        $data_issue = supplier_warranty_issues::with('supplier')->where('id', $id)->first();
        
        $product = product::with('lang')->where('reference', $data_issue->reference )->first();
        
        if(isset($product->id_product)){
            $insert = new AutoOrdersPurchaseList();
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

