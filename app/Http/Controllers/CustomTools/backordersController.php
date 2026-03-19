<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\modules\backorders\customers_backorders;

use App\Models\prestashop\orders;
use App\Models\prestashop\orders_details;
use App\Models\prestashop\stock_available;

use App\Models\prestashop\product;
use App\Models\prestashop\product_lang;
use App\Models\prestashop\product_attribute;

class backordersController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('Sales'), 'url' => route('sales.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('messages.backorders'), 'url' => route('backorders.index')];
    }

    public function index()
    {

        $backorders = array();
        $partials = array();
        
        $asm_backorders = orders::getAllOrdersOf(15);
        $asm_partials   = orders::getAllOrdersOf(28);

        customers_backorders::verifyOrdersStatus();
        
        $asd_backorders = orders::getASDbackorders();
        $asd_partials   = orders::getASDpartials();
        
        $asd_partials = $asd_partials ?? [];
        $asd_backorders = $asd_backorders ?? [];
        
        foreach($asm_backorders AS $backorder){

            foreach($backorder->extraDataField AS $backorder_item){
                
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
        
        foreach($asm_partials AS $partial){

            foreach($partial->extraDataField AS $partial_item){

                $data = [
                        'id_order'              => $partial->id_order,
                        'id_product'            => $partial_item['id_product'],
                        'id_product_attribute'  => $partial_item['id_product_attribute'],
                        'reference'             => $partial_item['reference'],
                        'pack_reference'        => ( isset($partial_item['pack_reference']) ) ? $partial_item['pack_reference'] : '',
                        'supplier'              => $partial_item['supplier'],
                        'brand'                 => $partial_item['brand'],
                        'sold'                  => $partial_item['sold'],
                        'store'                 => $partial_item['store'],
                        'stock'                 => $partial_item['stock'],
                        'payment'               => $partial->module,
                        'type'                  => 'partial',
                        'date_add'              => $partial->date_add
                    ];
                    
                customers_backorders::insertBackorder($data);
            
            }
        }
        
        foreach($asd_backorders AS $backorders){

            $data = [
                    'id_order'              => $backorders['id_order'],
                    'id_product'            => $backorders['product_id'],
                    'id_product_attribute'  => $backorders['product_attribute_id'],
                    'reference'             => $backorders['product_reference'],
                    'pack_reference'        => '',
                    'supplier'              => $backorders['supplier'],
                    'brand'                 => $backorders['brand'],
                    'sold'                  => $backorders['sold'],
                    'store'                 => $backorders['store'],
                    'stock'                 => $backorders['stock'],
                    'payment'               => $backorders['module'],
                    'type'                  => 'backorder',
                    'date_add'              => $backorders['date_add']
                ];
                
            customers_backorders::insertBackorder($data);
            
        }
        
        foreach($asd_partials AS $partial){

            $data = [
                    'id_order'              => $partial['id_order'],
                    'id_product'            => $partial['product_id'],
                    'id_product_attribute'  => $partial['product_attribute_id'],
                    'reference'             => $partial['product_reference'],
                    'pack_reference'        => '',
                    'supplier'              => $partial['supplier'],
                    'brand'                 => $partial['brand'],
                    'sold'                  => $partial['sold'],
                    'store'                 => $partial['store'],
                    'stock'                 => $partial['stock'],
                    'payment'               => $partial['module'],
                    'type'                  => 'partial',
                    'date_add'              => $partial['date_add']
                ];
                
            customers_backorders::insertBackorder($data);
            
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
        
        $data = (object)[ 
            'father' => $father, 'reference' => $request->reference
        ];

        $html = view('customTools/backorders/model_content', compact('data'))->render();

        return response()->json(['html' => $html]);
    }
    
    public function setRowColor(Request $request){
        customers_backorders::where('id_order', $request->id_order)->where('id_product', $request->id_product)->where('id_product_attribute', $request->id_product_attribute)->update(['rowColor' => $request->color]);
        return 1;
    }
    
    public function getOrderDetails(Request $request){
        
        $html = '';
        $backorderInfo = customers_backorders::getBackorderDetail( $request->id_order );
        
        if($backorderInfo->store == 'ASD'){
            
            $url = 'https://www.all-stars-distribution.com/custom/front/getOrderComposition.php?id_order=' . $request->id_order; // URL para onde vai o POST

            $ch = curl_init($url);
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( [ 'id_order' => $request->id_order ] ));
            $html = curl_exec($ch);
            
        }else{
            $orderDetail = orders_details::where('id_order', $request->id_order)->get();
            $html = 'ASM';

            $html = '<table class="table table-striped text-center" style="width: 100%;">';
                $html .= '<tr>';
                    $html .= '<td>REFERENCE</td>';    
                    $html .= '<td>ORDERED</td>';    
                    $html .= '<td>STOCK</td>';    
                    $html .= '<td>WIDTH</td>';    
                    $html .= '<td>HEIGHT</td>';    
                    $html .= '<td>DEPTH</td>';    
                    $html .= '<td>WEIGHT</td>';    
                    $html .= '<td>STANDARD</td>';    
                $html .= '</tr>';
            
                foreach($orderDetail AS $detail){
                
                    $stock = stock_available::getStock($detail->product_id, $detail->product_attribute_id);

                    $real_photo = '';
                    $style = '';
                    $fn = '<div style="background-color: green; width: 10px; height: 10px; border-radius: 40px;margin: 5px;float: left;" ></div>';
                    
                    if( $detail->product->real_photos == 0){ 
                        $real_photo = '<div style="background-color: pink; width: 10px; height: 10px; border-radius: 40px;margin: 5px;float: left;" ></div>';
                    }
                    
                    if( $detail->product->wmdeprecated == 1){
                        $style = ' style="background-color: #f8d7da;" ';
                    }
                    
                        $fora_normas = product::getProductMeasures($detail->product->id_product, $detail->product->reference);
                        
                        if( ( $fora_normas['volumetric'] > 299 ) || ( $fora_normas['weight'] > 31.49 ) ) $fn = '<div style="background-color: red; width: 10px; height: 10px; border-radius: 40px;margin: 0 auto;" ></div>';

                        $html .= '<td '.$style.'>' . $real_photo . '<span>' .$detail->product_reference . '</span></td>';    
                        $html .= '<td '.$style.'>' . $detail->product_quantity . '</td>';    
                        $html .= '<td '.$style.'>' . $stock . '</td>';    
                        $html .= '<td '.$style.'>' . number_format($detail->product->width, 2, ',', '.') . '</td>';    
                        $html .= '<td '.$style.'>' . number_format($detail->product->height, 2, ',', '.') . '</td>';    
                        $html .= '<td '.$style.'>' . number_format($detail->product->depth, 2, ',', '.') . '</td>';    
                        $html .= '<td '.$style.'>' . number_format($detail->product->weight, 2, ',', '.') . '</td>';    
                        $html .= '<td '.$style.'>' . $fn . '<span style="margin: 0 5px;">' . $fora_normas['volumetric'] . ' ( cm )</span></td>';    
                    $html .= '</tr>';
                    
                }
                
            $html .= '</table>';

        }
        
        return response()->json(['html' => $html]);
    }
    
}