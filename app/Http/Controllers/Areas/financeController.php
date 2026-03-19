<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\product_attribute;
use App\Models\prestashop\pack;
use App\Models\prestashop\issues;
use App\Models\prestashop\orders;
use App\Models\prestashop\product;
use Illuminate\Support\Facades\DB;

use App\Models\modules\dashboard\dashboard;


class financeController extends Controller
{
    public $actions;
    public $breadcrumbs;
    /**public $convertion = 1;**/
    public $convertion = 1;
    public $year;
    public $month;

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('finance'), 'url' => route('finance.index')];
        $this->actions[]     = [];
        $this->year = date("Y");
        
        $month = date("m");
        
        if( $month == 01){
            $this->year = date("Y") - 1;
            $this->month = 12;
        }else{
            $this->year = date("Y");
            $this->month = $month-1;
        }
    }

    public function index(){
        
        
        dashboard::orderInvoiceExVat();
        
        $rates = issues::getCurrencyRates();

        $data = [
            'counters'      => dashboard::getCountersOFTab('finance'),
            'panels'        => [],
            'accessList'    => self::accessList(),
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs,
            'rates'         => (object)[
                'yuan'      => $rates->field_1,
                'pound'     => $rates->field_2,
                'dollar'    => $rates->field_3,
                'yen'       => $rates->field_4,
            ]
        ];

        return View::make('areas/finance/index')->with($data);
    }
    
    private function accessList(){
        
        $accessList = array();
        $accessList[] =         ['name' =>  trans('messages.Inventory'), 'url' => route('finance.download_inventory'), 'icon' => '<i style="font-size: 40px;" class="fa-solid fa-download"></i>'];
        $accessList[] =         ['name' =>  trans('messages.INTRASTAT'), 'url' => route('finance.download_intrastat'), 'icon' => '<i style="font-size: 40px;" class="fa-solid fa-download"></i>'];
        $accessList[] =         ['name' =>  trans('messages.VERIFICATION'), 'url' => route('carrierIssues.verification.index'), 'icon' => '<i class="fa-solid fa-truck-fast" style="font-size: 40px;"></i>'];
        $accessList[] =         ['name' =>  trans('messages.Return'), 'url' => route('carrierReturn.index'), 'icon' => '<i class="fa-solid fa-truck-arrow-right" style="font-size: 40px;"></i>'];
        return $accessList;
    }

    public function createInventoryCSV($array, $rates){ 
        
        $total = 0;
        $fileName = 'inventory_' . date('Ymd') . '.csv';
        $filePath = public_path() . '/admin/download/' . $fileName;

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = [ 'REFERENCE', 'QUANTITY', 'WHOLESALE PRICE', 'TOTAL' ];

        $file = fopen(public_path() . '/admin/download/' . $fileName, 'w');
        
        fputcsv($file, $columns, ';');

        foreach ($array as $item){
            $reference = $item->reference;
            $quantity = $item->quantity;
            $wholesale = $item->eur_convertion;
            $wholesale_row = $item->total_row;
            
            fputcsv($file, [$reference, $quantity, $wholesale, $wholesale_row], ';');
            $total += $item->total_row;
        }

        fputcsv($file, ['', '', '', $total ], ';');
        
        fclose($file);
        
        return $total;
    }

    public function inventory_father($data, $rate){ 

        $currency = 'EUR';
        $field = 'wholesale_price';

        $fathers = DB::table(env('DB2_DB_prefix') . 'stock_available')
            ->select( env('DB2_DB_prefix') . 'manufacturer.*', env('DB2_DB_prefix') . 'stock_available.id_product', env('DB2_DB_prefix') . 'stock_available.id_product_attribute', env('DB2_DB_prefix') . 'product.reference', env('DB2_DB_prefix') . 'product.wholesale_price', env('DB2_DB_prefix') . 'product.wholesale_price_pound', env('DB2_DB_prefix') . 'product.wholesale_price_dollar', env('DB2_DB_prefix') . 'product.wholesale_price_yen', env('DB2_DB_prefix') . 'stock_available.quantity')
            ->join(  env('DB2_DB_prefix') . 'product', env('DB2_DB_prefix') . 'stock_available.id_product', '=', env('DB2_DB_prefix') . 'product.id_product')
            ->join(  env('DB2_DB_prefix') . 'manufacturer', env('DB2_DB_prefix') . 'product.id_manufacturer', '=', env('DB2_DB_prefix') . 'manufacturer.id_manufacturer')
            ->where( env('DB2_DB_prefix') . 'stock_available.quantity', '>', 0 )
            ->where( env('DB2_DB_prefix') . 'stock_available.id_product_attribute', 0 )
            ->groupBy(  env('DB2_DB_prefix') . 'product.reference' )
            ->get();
            
        foreach ($fathers as $item){
            
            if($item->currency == 'USD') { $field = 'wholesale_price_dollar';   $currency = 'USD'; }
            if($item->currency == 'GBP') { $field = 'wholesale_price_pound';    $currency = 'GBP'; }
            if($item->currency == 'JPY') { $field = 'wholesale_price_yen';      $currency = 'JPY'; }
            if($item->currency == 'YEN') { $field = 'wholesale_price_yen';      $currency = 'YEN'; }
            if($item->currency == 'EUR') { $field = 'wholesale_price';          $currency = 'EUR'; }
            
            if(substr($item->reference, -2) != '-Z'){

                $is_pack = pack::where('id_product_pack', $item->id_product)->count();
                
                if($is_pack > 0){
                    
                    /** PACKS **/
                    $pack_products = pack::where('id_product_pack', $item->id_product)->get();

                    foreach($pack_products AS $product){
                        
                        if($product->id_product_attribute_item == 0){
                            
                            $pack_father = DB::table(env('DB2_DB_prefix') . 'stock_available')
                                ->select( env('DB2_DB_prefix') . 'manufacturer.*', env('DB2_DB_prefix') . 'stock_available.id_product', env('DB2_DB_prefix') . 'stock_available.id_product_attribute', env('DB2_DB_prefix') . 'product.reference', env('DB2_DB_prefix') . 'product.wholesale_price', env('DB2_DB_prefix') . 'product.wholesale_price_pound', env('DB2_DB_prefix') . 'product.wholesale_price_dollar', env('DB2_DB_prefix') . 'product.wholesale_price_yen', env('DB2_DB_prefix') . 'stock_available.quantity')
                                ->join(  env('DB2_DB_prefix') . 'product', env('DB2_DB_prefix') . 'stock_available.id_product', '=', env('DB2_DB_prefix') . 'product.id_product')
                                ->join(  env('DB2_DB_prefix') . 'manufacturer', env('DB2_DB_prefix') . 'product.id_manufacturer', '=', env('DB2_DB_prefix') . 'manufacturer.id_manufacturer')
                                ->where( env('DB2_DB_prefix') . 'stock_available.quantity', '>', 0 )
                                ->where( env('DB2_DB_prefix') . 'stock_available.id_product', $product->id_product_item )
                                ->where( env('DB2_DB_prefix') . 'stock_available.id_product_attribute', 0 )
                                ->groupBy(  env('DB2_DB_prefix') . 'product.reference' )
                                ->get();
                                    
                            foreach ($pack_father as $item){

                                if($item->currency == 'USD') { $field = 'wholesale_price_dollar';   $currency = 'USD'; }
                                if($item->currency == 'GBP') { $field = 'wholesale_price_pound';    $currency = 'GBP'; }
                                if($item->currency == 'JPY') { $field = 'wholesale_price_yen';      $currency = 'JPY'; }
                                if($item->currency == 'YEN') { $field = 'wholesale_price_yen';      $currency = 'YEN'; }
                                if($item->currency == 'EUR') { $field = 'wholesale_price';          $currency = 'EUR'; }
                                
                                if(substr($item->reference, -2) != '-Z'){

                                    $wholesale = $item->$field;
                                    if( $wholesale < 0.01 ) $wholesale = $item->wholesale_price; 
                                    
                                    $data[$item->reference] = (object)[
                                        'reference' => $item->reference,
                                        'quantity' => $item->quantity,
                                        'wholesale_price' => $wholesale,
                                        'currency' => $currency,
                                        'eur_convertion' => $wholesale * $rate[$currency],
                                        'total_row' => ( $item->quantity * ( $wholesale * $rate[$currency] ) )
                                    ];
                                }
                            }
                            
                        }else{

                            if($item->currency == 'USD') { $field = 'wholesale_price_dollar';   $currency = 'USD'; }
                            if($item->currency == 'GBP') { $field = 'wholesale_price_pound';    $currency = 'GBP'; }
                            if($item->currency == 'JPY') { $field = 'wholesale_price_yen';      $currency = 'JPY'; }
                            if($item->currency == 'YEN') { $field = 'wholesale_price_yen';      $currency = 'YEN'; }
                            if($item->currency == 'EUR') { $field = 'wholesale_price';          $currency = 'EUR'; }
            
                            $pack_son = DB::table(env('DB2_DB_prefix') . 'stock_available')
                                ->select( env('DB2_DB_prefix') . 'manufacturer.*', env('DB2_DB_prefix') . 'stock_available.id_product', env('DB2_DB_prefix') . 'stock_available.id_product_attribute', env('DB2_DB_prefix') . 'product.reference', env('DB2_DB_prefix') . 'product.wholesale_price', env('DB2_DB_prefix') . 'product.wholesale_price_pound', env('DB2_DB_prefix') . 'product.wholesale_price_dollar', env('DB2_DB_prefix') . 'product.wholesale_price_yen', env('DB2_DB_prefix') . 'stock_available.quantity')
                                ->join(  env('DB2_DB_prefix') . 'product', env('DB2_DB_prefix') . 'stock_available.id_product', '=', env('DB2_DB_prefix') . 'product.id_product')
                                ->join(  env('DB2_DB_prefix') . 'manufacturer', env('DB2_DB_prefix') . 'product.id_manufacturer', '=', env('DB2_DB_prefix') . 'manufacturer.id_manufacturer')
                                ->where( env('DB2_DB_prefix') . 'stock_available.quantity', '>', 0 )
                                ->where( env('DB2_DB_prefix') . 'stock_available.id_product', $product->id_product_item )
                                ->where( env('DB2_DB_prefix') . 'stock_available.id_product_attribute', $product->id_product_attribute_item )
                                ->groupBy(  env('DB2_DB_prefix') . 'product.reference' )
                                ->get();
                                    
                            foreach ($pack_son as $item){
                                
                                if(substr($item->reference, -2) != '-Z'){

                                    $wholesale = $item->$field;
                                    if( $wholesale < 0.01 ) $wholesale = $item->wholesale_price;  
                                    
                                    $data[$item->reference] = (object)[
                                        'reference' => $item->reference,
                                        'quantity' => $item->quantity,
                                        'wholesale_price' => $wholesale,
                                        'currency' => $currency,
                                        'eur_convertion' => $wholesale * $rate[$currency],
                                        'total_row' => ( $item->quantity * ( $wholesale * $rate[$currency] ) )
                                    ];
                                }else{
                                    $attr= product_attribute::where('id_product_attribute', $item->id_product_attribute)->first();

                                    $wholesale = $attr->$field;
                                    if( $wholesale < 0.01 ) $wholesale = $attr->wholesale_price;  
                                    
                                    $data[$item->reference] = (object)[
                                        'reference' => $attr->reference,
                                        'quantity' => $item->quantity,
                                        'wholesale_price' => $wholesale + $attr->$field,
                                        'currency' => $currency,
                                        'eur_convertion' => $wholesale * $rate[$currency],
                                        'total_row' => ( $item->quantity * ( $wholesale * $rate[$currency] ) )
                                    ];
                                }
                            }
                        }
                    }
                }else{
        
                    $wholesale = $item->$field;
                    if( $wholesale < 0.01 ) $wholesale = $item->wholesale_price;                    

                    $data[$item->reference] = (object)[
                        'reference' => $item->reference,
                        'quantity' => $item->quantity,
                        'wholesale_price' => $wholesale,
                        'currency' => $currency,
                        'eur_convertion' => $wholesale * $rate[$currency],
                        'total_row' => ( $item->quantity * ( $wholesale * $rate[$currency] ) )
                    ];
                }
            }
        }
        
        return $data;
    }

    public function inventory_sons($data, $rate){ 
        
        $array = array();
        $currency = 'EUR';
 
        $sons = DB::table(env('DB2_DB_prefix') . 'stock_available')
            ->select( 
                env('DB2_DB_prefix') . 'manufacturer.*', 
                env('DB2_DB_prefix') . 'stock_available.id_product', 
                env('DB2_DB_prefix') . 'stock_available.id_product_attribute', 
                env('DB2_DB_prefix') . 'product_attribute.reference', 
                env('DB2_DB_prefix') . 'product.wholesale_price', 
                env('DB2_DB_prefix') . 'product.wholesale_price_pound', 
                env('DB2_DB_prefix') . 'product.wholesale_price_dollar', 
                env('DB2_DB_prefix') . 'product.wholesale_price_yen', 
                env('DB2_DB_prefix') . 'stock_available.quantity',
                env('DB2_DB_prefix') . 'product_attribute.wholesale_price AS attr_wholesale_price', 
                env('DB2_DB_prefix') . 'product_attribute.wholesale_price_pound AS attr_wholesale_price_pound', 
                env('DB2_DB_prefix') . 'product_attribute.wholesale_price_dollar AS attr_wholesale_price_dollar', 
                env('DB2_DB_prefix') . 'product_attribute.wholesale_price_yen AS attr_wholesale_price_yen'
            )
            ->join(  env('DB2_DB_prefix') . 'product_attribute', env('DB2_DB_prefix') . 'stock_available.id_product_attribute', '=', env('DB2_DB_prefix') . 'product_attribute.id_product_attribute')
            ->join(  env('DB2_DB_prefix') . 'product', env('DB2_DB_prefix') . 'stock_available.id_product', '=', env('DB2_DB_prefix') . 'product.id_product')
            ->join(  env('DB2_DB_prefix') . 'manufacturer', env('DB2_DB_prefix') . 'product.id_manufacturer', '=', env('DB2_DB_prefix') . 'manufacturer.id_manufacturer')
            ->where( env('DB2_DB_prefix') . 'stock_available.quantity', '>', 0 )
            ->where( env('DB2_DB_prefix') . 'stock_available.id_product_attribute', '<>', 0 )
            ->groupBy(  env('DB2_DB_prefix') . 'product_attribute.reference' )
            ->get();
                
        foreach ($sons as $item){

            $field = 'wholesale_price';
            
            if($item->currency == 'USD') {
                $field = 'attr_wholesale_price_dollar';
                $currency = 'USD'; 
                if($item->$field == 0) $field = 'attr_wholesale_price';
                if($item->$field == 0) $field = 'wholesale_price_dollar';
                if($item->$field == 0) $field = 'wholesale_price';
            }
            
            if($item->currency == 'GBP') { 
                $field = 'attr_wholesale_price_pound';    
                $currency = 'GBP'; 
                if($item->$field == 0) $field = 'attr_wholesale_price';
                if($item->$field == 0) $field = 'wholesale_price_pound';
                if($item->$field == 0) $field = 'wholesale_price';
            }
            
            if($item->currency == 'JPY') { 
                $field = 'attr_wholesale_price_yen';      
                $currency = 'JPY'; 
                if($item->$field == 0) $field = 'attr_wholesale_price';
                if($item->$field == 0) $field = 'wholesale_price_yen';
                if($item->$field == 0) $field = 'wholesale_price';
            }
            
            if($item->currency == 'YEN') { 
                $field = 'attr_wholesale_price_yen';      
                $currency = 'YEN'; 
                if($item->$field == 0) $field = 'attr_wholesale_price';
                if($item->$field == 0) $field = 'wholesale_price_yen';
                if($item->$field == 0) $field = 'wholesale_price';
            }
            
            if($item->currency == 'EUR') { 
                $field = 'attr_wholesale_price';          
                $currency = 'EUR'; 
                if($item->$field == 0) $field = 'wholesale_price';
            }
            
            $wholesale = $item->$field;
            
            $converted = ( str_contains($field, '_price_') ) ?  $wholesale * $rate[$currency] :  $wholesale;
            
            
            /**
            if($item->reference == '61000591001MM'){
                
                echo $converted;
                echo '<br>' . $wholesale;
                dd($item);
            }**/
            
            $data[$item->reference] = (object)[
                'reference' => $item->reference,
                'quantity' => $item->quantity,
                'wholesale_price' => $wholesale,
                'currency' => $currency,
                'eur_convertion' => $converted,
                'total_row' => $item->quantity * $converted
            ];
            
        }
        
        return $data;
    }
    
    public function download_inventory(Request $request){ 

        $total=0;
        $data = array();
        
        $rates_data = issues::getCurrencyRates();
        
        $rates = array();
        $rates['EUR'] = 1.00;
        
        //$rates['YEN'] = 0.00666; //3829208.54
        //$rates['YEN'] = 0.00588;   //3789335.99
        
        $rates['YEN'] = 0.00588; /** 1/170 - LUDUVINA SET IT ON 03/12/2025**/
        $rates['GBP'] = 1.15;    /** LUDUVINA SET IT ON 03/12/2025**/
        $rates['USD'] = 1;
        
        $data = self::inventory_father($data, $rates);
        $data = self::inventory_sons($data, $rates);
        
        $total = self::createInventoryCSV($data, $rates);

        $data = [
            'actions'    => [],
            'breadcrumbs'=> $this->breadcrumbs,
            'array' => $data,
            'total' => $total,
            'rates' => $rates,
            'link' => 'https://webtools.all-stars-motorsport.com/admin/download/inventory_' . date('Ymd') . '.csv?t='.rand(),
        ];
        
        return View::make('areas/finance/inventory')->with($data);
    }
    
    public function intrastat_import(Request $request){ 
        return self::createCHCSV();
    }
    
    public function intrastat_export(Request $request){ 
        return self::createEXCSV();
    }
    
    public function download_intrastat(Request $request){ 

        $data = [
            'year' => $this->year,
            'month' => $this->month,
            'actions'    => [],
            'breadcrumbs'=> $this->breadcrumbs,
            'htmlAfterUpload' => ''
            /**'htmlAfterUpload' => '<a id="dropZoneDownload" class="btn btn-success" href="https://webtools.all-stars-motorsport.com/admin/download/INTRA-EX-' . $this->year . $this->month . '.csv" download="download" style="margin: 5px;width: calc( 100% - 10px );">DOWNLOAD</a>'**/
        ];
        
        return View::make('areas/finance/intrastat')->with($data);
    }
    
    public function createCHCSV(){ 

        $fileName = 'INTRA-CH-' . $this->year . $this->month . '.csv';
        $filePath = public_path() . '/admin/download/' . $fileName;

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $file = fopen(public_path() . '/admin/download/' . $fileName, 'w');
        
        fputcsv($file, ['FLUXO', 'PERIODO', 'NIF', 'REF', 'NC', 'PAIS', 'PORIGEM', 'REGIAO', 'CODENT', 'NATTRA', 'MODTRA', 'AERPOR', 'MASSA', 'UNSUP', 'VALFAC', 'ADQNIF', 'ERRO'], ';');

        $data = DB::select("SELECT " . env('DB2_DB_prefix') . "manufacturer.country_code AS iso_code, " . env('DB2_DB_prefix') . "bms_procurement_purchase_order_reception_product.sku AS reference, " . env('DB2_DB_prefix') . "bms_procurement_purchase_order_reception_product.qty AS qty, " . env('DB2_DB_prefix') . "product.nc, " . env('DB2_DB_prefix') . "product.weight, (" . env('DB2_DB_prefix') . "product.wholesale_price*" . env('DB2_DB_prefix') . "bms_procurement_purchase_order_reception_product.qty) AS wholesale_price 
        FROM " . env('DB2_DB_prefix') . "bms_procurement_purchase_order_reception 
        LEFT JOIN " . env('DB2_DB_prefix') . "bms_procurement_purchase_order_reception_product 
        ON " . env('DB2_DB_prefix') . "bms_procurement_purchase_order_reception.id_bms_procurement_purchase_order_reception = " . env('DB2_DB_prefix') . "bms_procurement_purchase_order_reception_product.reception_id 
        LEFT JOIN " . env('DB2_DB_prefix') . "product 
        ON " . env('DB2_DB_prefix') . "product.id_product = " . env('DB2_DB_prefix') . "bms_procurement_purchase_order_reception_product.product_id 
        LEFT JOIN " . env('DB2_DB_prefix') . "manufacturer 
        ON " . env('DB2_DB_prefix') . "manufacturer.id_manufacturer = " . env('DB2_DB_prefix') . "product.id_manufacturer
        WHERE MONTH(" . env('DB2_DB_prefix') . "bms_procurement_purchase_order_reception.date_add) = " . $this->month . " 
        AND YEAR(" . env('DB2_DB_prefix') . "bms_procurement_purchase_order_reception.date_add) = " . $this->year . " 
        AND " . env('DB2_DB_prefix') . "product.id_manufacturer in (109, 113, 138, 141, 72, 93, 117, 136, 104, 68, 99, 69, 82, 142, 20, 124, 143, 92, 66, 121, 122, 123, 161)");
        
        foreach($data AS $row){
        
            $weight = round( $row->weight, 1);
            $weight = ($weight == 0.0) ? 0.1 : $weight;
            $weight = str_replace(".",",", $weight );
            
            fputcsv($file, ['INTRA-CH', $this->year . $this->month, '513881387', $row->reference, $row->nc, $row->iso_code, $row->iso_code, '10', 'EXW', '11', '3', '', $weight, $row->qty, intval($row->wholesale_price), '', ''], ";");
        }
        
        fclose($file);
        
        return json_encode($fileName);
    }

    public function createEXCSV(){ 
        
        $row = 0;
        $fileName = 'INTRA-EX-' . $this->year . $this->month . '.csv';
        $filePath = public_path() . '/admin/download/' . $fileName;

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        if (file_exists($filePath)) unlink($filePath); 
        
        $file = fopen($filePath, 'a');
        
        fputcsv($file, ['FLUXO', 'PERIODO', 'NIF', 'REF', 'NC', 'PAIS', 'PORIGEM', 'REGIAO', 'CODENT', 'NATTRA', 'MODTRA', 'AERPOR', 'MASSA', 'UNSUP', 'VALFAC', 'VAL ESTAT', 'ADQNIF', 'ERRO'], ';');        
        
        $iso_code_list = ['DE', 'AT', 'BE', 'BG', 'CY', 'HR', 'DK', 'SK', 'SI', 'ES', 'EE', 'FI', 'FR', 'GR', 'EL', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'CZ', 'RO', 'SE'];

        if (($handle = fopen(public_path() . "/uploads/finance/moloni/moloni.csv", "r")) !== FALSE) {

            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                
                $data = array_map("utf8_encode", $data);
                $num = count($data);
                $row++;
                
                if($row > 2){

                    if( (isset($data[12])) && ($data[12] != "") && ($data[0] == 'Fatura-Recibo')) {
                        
                        if( ($data[12] != "Portes") && ( !self::startsWith($data[12], 'SHIPPING')) && ($data[12] != "ccFee") ) {

                            $iso_code = substr($data[5], 0, 2);

                            if(in_array($iso_code, $iso_code_list)){
                                
                                $attr = product_attribute::where('reference', $data[12])->first();
       
                                if(!is_null($attr)){
                                    
                                    $product = product::select('reference')->where('id_product', $attr->id_product)->first();
                                    $reference = $product->reference;
                                    
                                }else{
                                    $reference = $data[12];
                                }
                                
                                $product = product::join( env('DB2_DB_prefix') . 'manufacturer', env('DB2_DB_prefix') . 'manufacturer.id_manufacturer', '=', env('DB2_DB_prefix') . 'product.id_manufacturer' )->where('reference', $reference)->first();

                                if(is_null($product)){
                                    
                                    $nc='';
                                    $weight=0;
                                    
                                }else{
                                    $nc=$product->nc;
                                    $weight = $product->weight;
                                }
                                    
                                $nif='';
                                if ( strpos($data[5], '999999990') !== false) { 
                                    $nif = 'QV999999999999';
                                }else{
                                    $nif = $data[5];
                                }
                                
                                $weight = round( $weight, 1);
                                $weight = ($weight == 0.0) ? 0.1 : $weight;
                                $weight = str_replace(".",",", $weight );
                                
                                
                                $array = ['INTRA-EX', $this->year . $this->month, '513881387', $data[12], $nc, $iso_code, 'PT', '10', 'EXW', '11', '3', '', $weight, $data[15], intval($data[18]), '', $nif, ''];
                                
                                fputcsv($file, $array, ';');
                            }
                        }
                    }
                }
            }

            fclose($handle);
        }
        fclose($file);

        return json_encode($fileName);
    }
    
    public function startsWith ($string, $startString){
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }
    
    public function save_currency_rate(Request $request){
        return issues::saveCurrencyRate($request);
    }

}
