<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\Models\modules\backorders\customers_backorders;

use App\Models\prestashop\orders;
use App\Models\prestashop\orders_details;
use App\Models\prestashop\stock_available;

use App\Models\prestashop\product;

class backordersController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = ['name' => 'sales', 'url' => route('sales.index')];
        $this->breadcrumbs[] = ['name' => 'Backorders', 'url' => route('backorders.index'), 'no_translation' => 1];
    }

    public function index()
    {

        $asm_backorders = orders::getAllOrdersOf(15, 2);

        customers_backorders::verifyOrdersStatus();
        
        $asd_backorders = orders::getAllOrdersOf(15, 3);
        
        $asd_backorders = $asd_backorders ?? [];
        
        foreach($asm_backorders AS $backorder){

            foreach(($backorder->extraDataField ?? []) AS $backorder_item){
                
                $data = [
                        'id_order'              => $backorder->id_order,
                        'id_product'            => $backorder_item['id_product'],
                        'id_product_attribute'  => $backorder_item['id_product_attribute'],
                        'reference'             => $backorder_item['reference'],
                        'pack_reference'        => ( isset($backorder_item['pack_reference']) ) ? $backorder_item['pack_reference'] : '',
                        'supplier'              => $backorder_item['supplier'],
                        'brand'                 => $backorder_item['brand'],
                        'sold'                  => $backorder_item['sold'],
                        'store'                 => $backorder_item['store'],
                        'stock'                 => $backorder_item['stock'],
                        'payment'               => $backorder->module,
                        'type'                  => 'backorder',
                        'date_add'              => $backorder->date_add
                    ];
                    
                customers_backorders::insertBackorder($data);
            }
        }
        
        foreach($asd_backorders AS $backorder){

            foreach(($backorder->extraDataField ?? []) AS $backorder_item){
                
                $data = [
                        'id_order'              => $backorder->id_order,
                        'id_product'            => $backorder_item['id_product'],
                        'id_product_attribute'  => $backorder_item['id_product_attribute'],
                        'reference'             => $backorder_item['reference'],
                        'pack_reference'        => ( isset($backorder_item['pack_reference']) ) ? $backorder_item['pack_reference'] : '',
                        'supplier'              => $backorder_item['supplier'],
                        'brand'                 => $backorder_item['brand'],
                        'sold'                  => $backorder_item['sold'],
                        'store'                 => $backorder_item['store'],
                        'stock'                 => $backorder_item['stock'],
                        'payment'               => $backorder->module,
                        'type'                  => 'backorder',
                        'date_add'              => $backorder->date_add
                    ];
                    
                customers_backorders::insertBackorder($data);
            }
        }

        $backorders = customers_backorders::getAll();
        
        $counters = customers_backorders::getCounters();

        $data = [
            'backorders' => $backorders,
            'counters'   => $counters,
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs
        ];
        
        return View::make('customTools/backorders/index')->with($data);
    }
    
    public function updateInfo(Request $request){
        return customers_backorders::updateInfo($request);
    }
    
    public function getProductInfo(Request $request){
        
        $father = product::with('erp_invoiced', 'erp_expected', 'stock', 'attribute.stock', 'attribute.erp_invoiced', 'attribute.erp_expected')->where('id_product', $request->id_product)->first();

        if (!$father) {
            return response()->json(['html' => '<div class="alert alert-warning">PRODUCT NOT FOUND</div>']);
        }
        
        $data = (object)[ 
            'father' => $father,
            'id_product_attribute' => (int) $request->id_product_attribute
        ];

        $html = view('customTools/backorders/model_content', compact('data'))->render();

        return response()->json(['html' => $html]);
    }
    
    public function setRowColor(Request $request){
        customers_backorders::where('id_order', $request->id_order)->where('id_product', $request->id_product)->where('id_product_attribute', $request->id_product_attribute)->update(['rowColor' => $request->color]);
        return 1;
    }
    
    public function getOrderDetails(Request $request){
        
        $backorderInfo = customers_backorders::getBackorderDetail( $request->id_order );

        if (!$backorderInfo) {
            return response()->json(['html' => '<div class="alert alert-warning">ORDER NOT FOUND</div>']);
        }

        $orderDetail = orders_details::where('id_order', $request->id_order)->get();

        if ($orderDetail->isEmpty()) {
            return response()->json(['html' => '<div class="alert alert-warning">NO ORDER DETAILS FOUND</div>']);
        }

        $prestashopPrefix = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
        $idShop = $backorderInfo->store === 'ASD' ? 3 : 2;

        $html = '<table class="table table-striped text-center" style="width: 100%;">';
            $html .= '<thead><tr>';
                $html .= '<td>REFERENCE</td>';    
                $html .= '<td>ORDERED</td>';    
                $html .= '<td>STOCK</td>';    
                $html .= '<td>WIDTH</td>';    
                $html .= '<td>HEIGHT</td>';    
                $html .= '<td>DEPTH</td>';    
                $html .= '<td>WEIGHT</td>';    
                $html .= '<td>STANDARD</td>';    
            $html .= '</tr></thead><tbody>';
        
            foreach($orderDetail AS $detail){
            
                $stock = stock_available::where('id_product', $detail->product_id)
                    ->where('id_product_attribute', $detail->product_attribute_id)
                    ->where('id_shop', $idShop)
                    ->value('quantity');
                $product = $detail->product;
                $customProduct = DB::connection('mysql2')
                    ->table($prestashopPrefix . 'custom_product')
                    ->where('id_product', $detail->product_id)
                    ->first();

                $real_photo = '';
                $style = '';
                $fn = '<div style="background-color: green; width: 10px; height: 10px; border-radius: 40px;margin: 5px auto;" ></div>';
                
                if( ((int) ($customProduct->real_photos ?? 0)) == 0){ 
                    $real_photo = '<div style="background-color: pink; width: 10px; height: 10px; border-radius: 40px;margin: 5px;float: left;" ></div>';
                }
                
                if( ((int) ($customProduct->wmdeprecated ?? 0)) == 1){
                    $style = ' style="background-color: #f8d7da;" ';
                }
                
                $fora_normas = $product
                    ? product::getProductMeasures($product->id_product, '')
                    : ['volumetric' => 0, 'weight' => 0];
                    
                if( ( $fora_normas['volumetric'] > 299 ) || ( $fora_normas['weight'] > 31.49 ) ) $fn = '<div style="background-color: red; width: 10px; height: 10px; border-radius: 40px;margin: 0 auto;" ></div>';

                $html .= '<tr>';
                    $html .= '<td '.$style.'>' . $real_photo . '<span>' . e($detail->product_reference) . '</span></td>';    
                    $html .= '<td '.$style.'>' . (int) $detail->product_quantity . '</td>';    
                    $html .= '<td '.$style.'>' . (is_null($stock) ? 'N/D' : $stock) . '</td>';    
                    $html .= '<td '.$style.'>' . number_format((float) ($product->width ?? 0), 2, ',', '.') . '</td>';    
                    $html .= '<td '.$style.'>' . number_format((float) ($product->height ?? 0), 2, ',', '.') . '</td>';    
                    $html .= '<td '.$style.'>' . number_format((float) ($product->depth ?? 0), 2, ',', '.') . '</td>';    
                    $html .= '<td '.$style.'>' . number_format((float) ($product->weight ?? 0), 2, ',', '.') . '</td>';    
                    $html .= '<td '.$style.'>' . $fn . '<span style="margin: 0 5px;">' . $fora_normas['volumetric'] . ' ( cm )</span></td>';    
                $html .= '</tr>';
                
            }
            
        $html .= '</tbody></table>';
        
        return response()->json(['html' => $html]);
    }
    
}
