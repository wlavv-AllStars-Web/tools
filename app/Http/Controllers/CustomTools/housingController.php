<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

use App\Http\Controllers\Controller;

use App\Models\prestashop\orders;
use App\Models\prestashop\order_details;

use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;

class housingController extends Controller
{
    public function __construct(){

    }

    public function index() {
        return View::make('customTools/housing/index')->with( [ 'hideHeader' => true ] );
    }

    public function saveData(Request $request) {
        
        product::where('ean13', $request->barcode)->update(['housing' => $request->stand, 'location' => $request->stand]);
        product::where('reference', $request->barcode)->update(['housing' => $request->stand, 'location' => $request->stand]);
        product_attribute::where('ean13', $request->barcode)->update(['location' => $request->stand]);
        product_attribute::where('reference', $request->barcode)->update(['location' => $request->stand]);
        return 1;
    }

    public function editLocation(Request $request) {

        if( $request->id_product_attribute > 0){
            product_attribute::where('ean13', $request->barcode)->update(['location' => $request->stand]);
            product_attribute::where('reference', $request->barcode)->update(['location' => $request->stand]);            
        }else{
            product::where('ean13', $request->barcode)->update(['housing' => $request->stand, 'location' => $request->stand]);
            product::where('reference', $request->barcode)->update(['housing' => $request->stand, 'location' => $request->stand]);            
        }
        return 1;
    }

    public function editMeasures(Request $request) {
        
        $barcode = $request->barcode;
        
        if( $request->id_product_attribute > 0){
            $product = product::where('id_product', $request->id_product)->first();
            $barcode = $product->ean13;
        }
        
        //if( $request->id_product_attribute == 0){
            product::where('ean13', $barcode)->update([ 'weight' => $request->weight, 'height' => $request->height, 'width' => $request->width, 'depth' => $request->depth ]);
            product::where('reference', $barcode)->update([ 'weight' => $request->weight, 'height' => $request->height, 'width' => $request->width, 'depth' => $request->depth ]);            
        //}
        return 1;
    }

    public function requestData(Request $request) {
        
        $empty_order = [ 'progress' => 0, 'partial' => 0, 'backorder' => 0, 'warranty' => 0 ];
        
        $barcode = $request->barcode;
        
        $product = self::getForHousingInfo($barcode);
        
        if(isset($product->reference)){
            $order_ASM = self::getOrderOf($product->reference);
            $order_ASD = self::getOrderByAPI($product->ean13);
        }else{
            $order_ASM = $empty_order;
            $order_ASD = $empty_order;
        }
        return view('customTools/housing/info', compact('product', 'order_ASM', 'order_ASD', 'barcode'))->render();
    }

    private function getOrderOf($reference) {
        
        $progress  = orders::leftJoin('ps_order_detail', 'ps_order_detail.id_order', 'ps_orders.id_order')->where('product_reference', $reference)->where('current_state', 3)->count();
        $partial   = orders::leftJoin('ps_order_detail', 'ps_order_detail.id_order', 'ps_orders.id_order')->where('product_reference', $reference)->where('current_state', 28)->count();
        $backorder = orders::leftJoin('ps_order_detail', 'ps_order_detail.id_order', 'ps_orders.id_order')->where('product_reference', $reference)->where('current_state', 15)->count();
        $warranty  = orders::leftJoin('ps_order_detail', 'ps_order_detail.id_order', 'ps_orders.id_order')->where('product_reference', $reference)->where('current_state', 29)->count();
        return [ 'progress' => $progress, 'partial' => $partial, 'backorder' => $backorder, 'warranty' => $warranty ];
    }

    private function getOrderByAPI($ean) {
        
        $ch = curl_init( "https://www.all-stars-distribution.com/custom/front/findProduct.php?ean=" . $ean);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $server_output = curl_exec($ch);
        curl_close($ch);
        
        $asd_data = json_decode($server_output);
        return [ 'progress' => $asd_data->statuses->preparation, 'partial' => $asd_data->statuses->partial, 'backorder' => $asd_data->statuses->backorder, 'warranty' => $asd_data->statuses->warranty ];
    }
    
    private function getForHousingInfo($barcode) {

        /** ATTRIBUTE BY EAN13 **/
        $attr = product_attribute::with('product', 'stock')->where('ean13', $barcode)->first();
        if(isset($attr->id_product)) return $attr;
        
        /** ATTRIBUTE BY REFERENCE **/
        $attr = product_attribute::with('product', 'stock')->where('reference', $barcode)->first();
        if(isset($attr->id_product)) return $attr;
        
        /** PRODUCT BY EAN13 **/
        $product = product::with('stock')->where('ean13', $barcode)->first();
        if(isset($product->id_product)) return $product;
        
        /** PRODUCT BY REFERENCE **/
        $product = product::with('stock')->where('reference', $barcode)->first();
        if(isset($product->id_product)) return $product;

        return null;
    }
}