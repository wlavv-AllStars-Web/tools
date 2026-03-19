<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Http;

use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;
use App\Models\prestashop\manufacturers;

use App\Models\modules\price_map\price_map;

class priceMapController extends Controller
{
    public $actions;
    public $breadcrumbs;
    private static $onlinePath = 'https://www.all-stars-distribution.com';

    public function index(){
        
        $this->breadcrumbs[] = [ 'name' =>  trans('Sales'), 'url' => route('sales.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('PRICE MAP'), 'url' => route('priceMap.index')];
        
        $brands = manufacturers::orderBy('name', 'ASC')->get();
        
        $data = [
            'actions'    => [],
            'breadcrumbs'=> $this->breadcrumbs,
            'brands'     => $brands
        ];
        
        return View::make('customTools/priceMap/index')->with($data);
    }
    
    public function getPriceMapOfBrand(Request $request){

        $asm_products = product::with('attribute', 'discount')->where('id_manufacturer', $request->id_manufacturer)->get();
        
        $products = array();
        
        foreach($asm_products AS $product){
            
            if( count($product->attribute) > 0){
                
                foreach($product->attribute AS $attribute){
                    
                    $wholesale = ($attribute->wholesale_price > 0) ? $attribute->wholesale_price : $product->wholesale_price;
                    $price = ($attribute->price > 0) ? ($product->price + $attribute->price) : $product->price;
                    
                    $racio = number_format( ( 1 - ( $wholesale / $price ) ) * 100, 2, '.', ' ');
                    
                    $products[$attribute->reference] = [
                        'active' => $product->active,
                        'reference' => $product->reference,
                        'attr_reference' => $attribute->reference,
                        'deprecated' => $product->wmdeprecated,
                        'price' => number_format($price, 2, '.', ' ') . ' €',
                        'wholesale_price' => number_format($attribute->wholesale_price, 2, '.', ' ') . ' €',
                        'discount' => ( isset($product->discount->reduction)) ? number_format($product->discount->reduction*100, 2, '.', ' ') . ' %' : 0 . ' %',
                        'racio' => $racio,
                        'width' => $product->width,
                        'height' => $product->height,
                        'depth' => $product->depth,
                        'weight' => $product->weight,
                        
                        'asd_active' => 2,
                        'asd_reference' => '',
                        'asd_attr_reference' => '',
                        'asd_deprecated' => 2,
                        'asd_price' => '',
                        'asd_wholesale_price' => '',
                        'asd_discount' => '',
                        'asd_racio' => '',
                        
                        'asd_supplier' => '',
                        'asd_manufacturer' => ''
                    ];
                }
            }else{
                $products[$product->reference] = [
                    'active' => $product->active,
                    'reference' => $product->reference,
                    'attr_reference' => '',
                    'deprecated' => $product->wmdeprecated,
                    'price' => number_format($product->price, 2, '.', ' ') . ' €',
                    'wholesale_price' => number_format($product->wholesale_price, 2, '.', ' ') . ' €',
                    'discount' => ( isset($product->discount->reduction)) ? number_format($product->discount->reduction*100, 2, '.', ' ') . ' %' : 0 . ' %',
                    'racio' => number_format( ( 1 - ( $product->wholesale_price / $product->price ) ) * 100, 2, '.', ' '),
                    'width' => $product->width,
                    'height' => $product->height,
                    'depth' => $product->depth,
                    'weight' => $product->weight,
                        
                    'asd_active' => 2,
                    'asd_reference' => '',
                    'asd_attr_reference' => '',
                    'asd_deprecated' => 2,
                    'asd_price' => '',
                    'asd_wholesale_price' => '',
                    'asd_discount' => '',
                    'asd_racio' => '',
                    'asd_supplier' => '',
                    'asd_manufacturer' => ''
                ];
            }
        }
        

        $stream = self::getExternalDataByPOST('/custom/api/priceMap/getData.php', ['products' => $products] );
        $products = $stream['data'];

        self::generateCSV($products, $request->id_manufacturer);
        
        unset($products['supplier']);
        unset($products['manufacturer']);
        
        $html = view('customTools/priceMap/priceMapBrand', compact('products'))->render();        

        return response()->json(['html' => $html]);
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
    
    public static function generateCSV($products, $id_manufacturer){
        
        $fileName = 'ASD_' . $id_manufacturer . '.csv';
        $filePath = 'catalogue/' . $fileName;
    
        $header_names = [ "Supplier", "Manufacturer", "SKU", "Name", "VAT", "Price", "Purchase", "Width", "Height", "Depth", "Weight", "Discount", "Tags", "Meta title", "Meta Tags", "URL" ];
    
        $csvData = fopen('php://temp', 'r+');
        fwrite($csvData, "\xEF\xBB\xBF");
        fputcsv($csvData, $header_names, ';');
    
        $supplier = $products['supplier'];
        $manufacturer = $products['manufacturer'];
    
        foreach ($products as $key => $row) {
            
            if (!is_array($row)) continue;

            if (!empty($row['reference']) && empty($row['asd_reference'])) {                
                $reference = $row['reference'];
                $attrRef = $row['attr_reference'];
                
                $asm_ref = (!empty($attrRef) && (strlen($attrRef) > 0)) ? $attrRef : $reference;
                
                $tags = $manufacturer . ", " . $reference . ", " . $attrRef;
                $url = self::sanitizeForUrl($manufacturer . "-" . $asm_ref);
                
                $csv = [
                    $supplier,
                    $manufacturer,
                    $asm_ref,
                    '',
                    7,
                    str_replace([' ', '€'], '', $row['price']),
                    str_replace([' ', '€'], '', $row['wholesale_price']),
                    $row['width'],
                    $row['height'],
                    $row['depth'],
                    $row['weight'],
                    '',
                    $tags,
                    '',
                    $tags,
                    $url,
                ];
    
                fputcsv($csvData, $csv, ';');
            }
        }

        rewind($csvData);
        $csvString = stream_get_contents($csvData);
        fclose($csvData);
    
        Storage::disk('public_uploads')->put($filePath, $csvString);
    }

    public static function sanitizeForUrl($string){
        
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9]+/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        $string = trim($string, '-');
        return $string;
    }    

    public function cron_priceMap($part = 0){

        $all_ids = price_map::getIDsManufacturer();
        $all_manufacturers = manufacturers::orderBy('id_manufacturer', 'ASC')->get();

        $total = $all_manufacturers->count();
        $per_part = ceil($total / 10); // 10 partes
        
        $manufacturers = $all_manufacturers->slice($part * $per_part, $per_part);

        foreach( $manufacturers AS $k => $manufacturer){
            
            price_map::emptyTableFor($manufacturer->id_manufacturer);
            $asm_products = product::with('attribute', 'discount')->where('id_manufacturer', $manufacturer->id_manufacturer)->get();
            
            $array_compare = array();
            foreach($asm_products AS $product){
                
                if( count($product->attribute) > 0){
                    foreach($product->attribute AS $attribute){

                        $wholesale = ($attribute->wholesale_price > 0) ? $attribute->wholesale_price : $product->wholesale_price;
                        $price = ($attribute->price > 0) ? ($product->price + $attribute->price) : $product->price;
                        
                        $racio = number_format( ( 1 - ( $wholesale / $price ) ) * 100, 2, '.', ' ');
                    
                        $array_compare[] = [
                            'asm_reference' => $attribute->reference,
                            'asm_price' => $price,
                            'asm_wholesale_price' => $wholesale,
                            'asm_active' => $product->active,
                            'asm_deprecated' => $product->wmdeprecated,
                            'asm_discount' => ( isset($product->discount->reduction)) ? ($product->discount->reduction*100): 0,
                            'asm_racio' => $racio+0,
                            'asd_price' => 0,
                            'asd_wholesale_price' => 0,
                            'asd_active' => 0,
                            'asd_deprecated' => 0,
                            'asd_discount' => 0,
                            'asd_racio' => 0
                        ];
                    }
                }else{

                    $array_compare[] = [
                        'asm_reference' => $product->reference,
                        'asm_price' => $product->price,
                        'asm_wholesale_price' => $product->wholesale_price,
                        'asm_active' => $product->active,
                        'asm_deprecated' => $product->wmdeprecated,
                        'asm_discount' => ( isset($product->discount->reduction)) ? (float)($product->discount->reduction*100) : (float)0,
                        'asm_racio' => ( 1 - ( $product->wholesale_price / $product->price ) ) * 100 + 0,
                        'asd_price' => 0,
                        'asd_wholesale_price' => 0,
                        'asd_active' => 0,
                        'asd_deprecated' => 0,
                        'asd_discount' => 0,
                        'asd_racio' => 0
                    ];
                }
            }
            
            $stream = self::getExternalDataByPOST('/custom/api/priceMap/compareData.php', ['products' => $array_compare] );

            $array_compare = $stream['data'];
            
            foreach($array_compare AS $product_compare){
                
                $save = 0;
                
                if( ( !isset( $product_compare['asm_racio'] ) ) || ($product_compare['asm_racio']) < 5.01) $save=1;
                if( ( !isset( $product_compare['asd_racio'] ) ) || ($product_compare['asd_racio']) < 5.01) $save=1;
                
                if($save == 1) price_map::saveData($manufacturer->id_manufacturer, $manufacturer->name, $product_compare);
            }
        }
    }
}