<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;

use App\Models\prestashop\orders;
use App\Models\prestashop\orders_details;
use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;

use App\Services\oms\OmsLegacyProcurementService;

use Milon\Barcode\DNS2D;

class barcodeController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct(){
        $this->breadcrumbs[] = [ 'name' =>  trans('messages.backorders'), 'url' => route('backorders.index')];
    }
    
    public function generateProductBarcode($id_product, $id_product_attribute=0){

        $number = (int)($id_product.$id_product_attribute);
        $ean13  = self::generateEAN($number);
        
        self::saveProductBarcode($ean13, $id_product, $id_product_attribute);

        return view('customTools/barcode/saveEAN13')->render();
    }

    private function saveProductBarcode($barcode, $id_product, $id_product_attribute){

        if($id_product_attribute == 0){
            product::where('id_product',$id_product)->update( [ 'ean13'=>$barcode ] );
            orders_details::where('product_id',$id_product)->where('product_attribute_id',0)->update( [ 'product_ean13'=>$barcode ] );
        }else{
            product_attribute::where('id_product',$id_product)->where('id_product_attribute', $id_product_attribute)->update( [ 'ean13'=>$barcode ] );
            orders_details::where('product_id',$id_product)->where('product_attribute_id',$id_product_attribute)->update( [ 'product_ean13'=>$barcode ] );
        }
    }
    
    public function printProductBarcode($id_product, $id_product_attribute=0, $repeat = 1){

        if($id_product_attribute == 0){
            $type = 'product';
            $item = product::where('id_product', $id_product)->first();
        }else{
            $type = 'attribute';
            $item = product_attribute::where('id_product_attribute', $id_product_attribute)->first();
        }

        $html = '';
        
        for($i=0; $i < $repeat; $i++) $html .= self::generate1DImage('ean13', $type, $item->reference);

        $data = (object)[ 'html' => $html ];

        return view('customTools/barcode/print', compact('data'))->render();
    }
    
    public function printProductStand($id_product, $id_product_attribute=0){
        
        $type = 'stand';
        if($id_product_attribute == 0){
            $item = product::where('id_product', $id_product)->first();
            $type = 'stand';
        }else{
            $item = product_attribute::where('id_product_attribute', $id_product_attribute)->first();
            $type = 'stand_attr';
        }

        $html = self::generate1DImage('ean13', $type, $item->reference);

        $data = (object)[ 'html' => $html ];

        return view('customTools/barcode/print', compact('data'))->render();
    }
    
    public function printProductStandString($id_product, $id_product_attribute=0){
        
        $type = 'stand';
        if($id_product_attribute == 0){
            $item = product::where('id_product', $id_product)->first();
            $type = 'stand';
        }else{
            $item = product_attribute::where('id_product_attribute', $id_product_attribute)->first();
            $type = 'stand_attr';
        }

        $html = self::generate1DImage('C128', $type, $item->reference);

        $data = (object)[ 'html' => $html ];

        return view('customTools/barcode/print', compact('data'))->render();
    }
    
    public function printProductStandCell($tag){
        $html = self::generate1DImage('C128', 'standCell', $tag);
        $data = (object)[ 'html' => $html ];
        return view('customTools/barcode/print', compact('data'))->render();
    }
    
    public function printERPOrderBarcode($id_order){
        
        $html = '';
        
        $products = OmsLegacyProcurementService::linesForOrders([(int) $id_order]);
        
        foreach($products AS $product){
            
            if( ($product->qty_wmfaturado - $product->qty_received) > 0){
        
                if($product->product_attribute_id == 0){
                    $type = 'product';
                    $item = product::where('id_product', $product->product_id)->first();
                }else{
                    $type = 'attribute';
                    $item = product_attribute::where('id_product_attribute', $product->product_attribute_id)->first();
                }
        
                $repeat = $product->qty_wmfaturado - $product->qty_received;
                for($i=0; $i < $repeat; $i++) $html .= self::generate1DImage('ean13', $type, $item->reference);

            }
        }

        $data = (object)[ 'html' => $html ];

        return view('customTools/barcode/print', compact('data'))->render();
    }
    
    private function generateEAN($number){
        
        $code = '20' . str_pad($number, 10, '0');
        $weightflag = true;
        $sum = 0;
        
        for ($i = strlen($code) - 1; $i >= 0; $i--){
            $sum += (int)$code[$i] * ($weightflag?3:1);
            $weightflag = !$weightflag;
        }
        
        $code .= (10 - ($sum % 10)) % 10;
        
        return $code;
    }
    
    public function generate1DImage($codeType, $elementType, $code, $id = 0){
        
        $valid = true;
        $housing = 'N/D';
        $product = null;
        $item = null;
        $reference ='N/D';
        $id_order ='N/D';
            
        $codeType = 'PDF417';
        
        $image_code = '';

        if($valid){

            if($elementType == 'standCell'){
                $item = (object) [
                    'ean13' => $code,
                    'reference' => $code,
                ];
                $image_code = 'stand_cell_' . md5($code);
            }
            
            if($elementType == 'order'){
                $item = orders::where('id_order', $id)->first();
                $image_code = 'o_'.$id;
            }
            
            if($elementType == 'product'){
                $item = product::where('ean13', $code)->orWhere('reference', $code)->first();
                $housing = (isset($item->housing)) ? $item->housing : 'N/D';
                $image_code = 'p_'.$item->id_product;
            }
            if($elementType == 'attribute'){
                $item = product_attribute::with('product')->where('ean13', $code)->orWhere('reference', $code)->first();
                $housing = (isset($item->location)) ? $item->location : 'N/D';
                if( isset( $item->id_product ) ){
                    $image_code = 'a_'.$item->id_product_attribute;
                }
            }
            
            if($elementType == 'stand'){
                $product = $item = product::where('reference', $code)->first();
                $housing = (isset($item->housing)) ? $item->housing : 'N/D';
                $elementType = 'stand';
                $image_code = 'p_'.$item->id_product;
            }
            
            if($elementType == 'stand_attr'){
                $item = product_attribute::with('product')->where('reference', $code)->first();
                $product = product::where('id_product', $item->id_product)->first();
                $housing = (isset($item->location)) ? $item->location : 'N/D';
                $elementType = 'stand_attr';
                $image_code = 'a_'.$item->id_product_attribute;
            }
            
            $reference = (isset($item->reference)) ? $item->reference : 'N/D';
            $id_order = (isset($item->id_order)) ? $item->id_order : 'N/D';

            $barcode = new DNS2D();
            
            Storage::disk('public_uploads')->put('logistics/barcode/' . $image_code . '.png', base64_decode( $barcode->getBarcodePNG($code, $codeType)));

        }
        
        $data = (object)[ 
            'code' => $item->ean13 ?? $code,
            'item' => $item,
            'product' => $product,
            'reference'=> $reference,
            'housing'=> $housing,
            'id_order'=> $id_order,
            'image_code' => $image_code
        ];

        return view('customTools/barcode/' . $elementType, compact('data'))->render();
        
    }
}
