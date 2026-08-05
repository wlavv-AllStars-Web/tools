<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\product_attribute;
use App\Models\prestashop\pack;
use App\Models\prestashop\CurrencyVariation;
use App\Models\prestashop\product;
use App\Models\modules\issues\issues;
use Illuminate\Support\Facades\DB;
use App\Services\Finance\CorrectedInventoryService;

use App\Models\modules\dashboard\dashboard;


class financeController extends Controller
{
    public $actions = [];
    public $breadcrumbs = [];
    public $convertion = 1;
    public $year;
    public $month;

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('finance'), 'url' => route('finance.index')];
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
        
        $data = [
            'counters'      => dashboard::calculateAndGetCountersOfTab('finance'),
            'panels'        => [],
            'accessList'    => $this->accessList(),
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs,
            'rates'         => CurrencyVariation::orderBy('id', 'DESC')->first()
        ];

        return View::make('areas/finance/index')->with($data);
    }
    
    private function accessList(){
        
        $accessList = array();
        $accessList[] =         ['name' =>  trans('messages.Inventory'), 'url' => route('finance.download_inventory'), 'icon' => '<i style="font-size: 40px;" class="fa-solid fa-download"></i>'];
        $accessList[] =         ['name' =>  trans('messages.INTRASTAT'), 'url' => route('finance.tools.intrastat.index'), 'icon' => '<i style="font-size: 40px;" class="fa-solid fa-download"></i>'];
        $accessList[] =         ['name' =>  trans('messages.VERIFICATION'), 'url' => route('finance.tools.carrier_check.index'), 'icon' => '<i class="fa-solid fa-truck-fast" style="font-size: 40px;"></i>'];
        $accessList[] =         ['name' =>  trans('messages.Return'), 'url' => route('finance.tools.carrier_returns.index'), 'icon' => '<i class="fa-solid fa-truck-arrow-right" style="font-size: 40px;"></i>'];
        return $accessList;
    }

    private function prestashopMysql2Prefix(): string
    {
        $prefix = (string) (env('DB2_DB_prefix') ?: env('DB2_prefix') ?: 'ps_');

        return str_contains($prefix, '.') ? substr($prefix, strrpos($prefix, '.') + 1) : $prefix;
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
        $ps = $this->prestashopMysql2Prefix();

        $fathers = DB::connection('mysql2')->table($ps . 'stock_available')
            ->select(
                $ps . 'manufacturer.*',
                $ps . 'stock_available.id_product',
                $ps . 'stock_available.id_product_attribute',
                $ps . 'product.reference',
                $ps . 'product.wholesale_price',
                DB::raw('COALESCE(' . $ps . 'custom_product.wholesale_price_base_currency, 0) AS wholesale_price_pound'),
                DB::raw('COALESCE(' . $ps . 'custom_product.wholesale_price_base_currency, 0) AS wholesale_price_dollar'),
                DB::raw('COALESCE(' . $ps . 'custom_product.wholesale_price_base_currency, 0) AS wholesale_price_yen'),
                $ps . 'stock_available.quantity'
            )
            ->join(  $ps . 'product', $ps . 'stock_available.id_product', '=', $ps . 'product.id_product')
            ->leftJoin($ps . 'custom_product', $ps . 'custom_product.id_product', '=', $ps . 'product.id_product')
            ->join(  $ps . 'manufacturer', $ps . 'product.id_manufacturer', '=', $ps . 'manufacturer.id_manufacturer')
            ->where( $ps . 'stock_available.quantity', '>', 0 )
            ->where( $ps . 'stock_available.id_product_attribute', 0 )
            ->groupBy(  $ps . 'product.reference' )
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
                            
                            $pack_father = DB::connection('mysql2')->table($ps . 'stock_available')
                                ->select(
                                    $ps . 'manufacturer.*',
                                    $ps . 'stock_available.id_product',
                                    $ps . 'stock_available.id_product_attribute',
                                    $ps . 'product.reference',
                                    $ps . 'product.wholesale_price',
                                    DB::raw('COALESCE(' . $ps . 'custom_product.wholesale_price_base_currency, 0) AS wholesale_price_pound'),
                                    DB::raw('COALESCE(' . $ps . 'custom_product.wholesale_price_base_currency, 0) AS wholesale_price_dollar'),
                                    DB::raw('COALESCE(' . $ps . 'custom_product.wholesale_price_base_currency, 0) AS wholesale_price_yen'),
                                    $ps . 'stock_available.quantity'
                                )
                                ->join(  $ps . 'product', $ps . 'stock_available.id_product', '=', $ps . 'product.id_product')
                                ->leftJoin($ps . 'custom_product', $ps . 'custom_product.id_product', '=', $ps . 'product.id_product')
                                ->join(  $ps . 'manufacturer', $ps . 'product.id_manufacturer', '=', $ps . 'manufacturer.id_manufacturer')
                                ->where( $ps . 'stock_available.quantity', '>', 0 )
                                ->where( $ps . 'stock_available.id_product', $product->id_product_item )
                                ->where( $ps . 'stock_available.id_product_attribute', 0 )
                                ->groupBy(  $ps . 'product.reference' )
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
            
                            $pack_son = DB::connection('mysql2')->table($ps . 'stock_available')
                                ->select(
                                    $ps . 'manufacturer.*',
                                    $ps . 'stock_available.id_product',
                                    $ps . 'stock_available.id_product_attribute',
                                    $ps . 'product.reference',
                                    $ps . 'product.wholesale_price',
                                    DB::raw('COALESCE(' . $ps . 'custom_product_attribute.wholesale_price_base_currency, ' . $ps . 'custom_product.wholesale_price_base_currency, 0) AS wholesale_price_pound'),
                                    DB::raw('COALESCE(' . $ps . 'custom_product_attribute.wholesale_price_base_currency, ' . $ps . 'custom_product.wholesale_price_base_currency, 0) AS wholesale_price_dollar'),
                                    DB::raw('COALESCE(' . $ps . 'custom_product_attribute.wholesale_price_base_currency, ' . $ps . 'custom_product.wholesale_price_base_currency, 0) AS wholesale_price_yen'),
                                    $ps . 'stock_available.quantity'
                                )
                                ->join(  $ps . 'product', $ps . 'stock_available.id_product', '=', $ps . 'product.id_product')
                                ->leftJoin($ps . 'custom_product', $ps . 'custom_product.id_product', '=', $ps . 'product.id_product')
                                ->leftJoin($ps . 'custom_product_attribute', $ps . 'custom_product_attribute.id_product_attribute', '=', $ps . 'stock_available.id_product_attribute')
                                ->join(  $ps . 'manufacturer', $ps . 'product.id_manufacturer', '=', $ps . 'manufacturer.id_manufacturer')
                                ->where( $ps . 'stock_available.quantity', '>', 0 )
                                ->where( $ps . 'stock_available.id_product', $product->id_product_item )
                                ->where( $ps . 'stock_available.id_product_attribute', $product->id_product_attribute_item )
                                ->groupBy(  $ps . 'product.reference' )
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
                                    $attr = product_attribute::select(
                                        $ps . 'product_attribute.*',
                                        DB::raw('COALESCE(' . $ps . 'custom_product_attribute.wholesale_price_base_currency, 0) AS wholesale_price_pound'),
                                        DB::raw('COALESCE(' . $ps . 'custom_product_attribute.wholesale_price_base_currency, 0) AS wholesale_price_dollar'),
                                        DB::raw('COALESCE(' . $ps . 'custom_product_attribute.wholesale_price_base_currency, 0) AS wholesale_price_yen')
                                    )
                                        ->leftJoin($ps . 'custom_product_attribute', $ps . 'custom_product_attribute.id_product_attribute', '=', $ps . 'product_attribute.id_product_attribute')
                                        ->where($ps . 'product_attribute.id_product_attribute', $item->id_product_attribute)
                                        ->first();

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
        $ps = $this->prestashopMysql2Prefix();
 
        $sons = DB::connection('mysql2')->table($ps . 'stock_available')
            ->select( 
                $ps . 'manufacturer.*', 
                $ps . 'stock_available.id_product', 
                $ps . 'stock_available.id_product_attribute', 
                $ps . 'product_attribute.reference', 
                $ps . 'product.wholesale_price', 
                DB::raw('COALESCE(' . $ps . 'custom_product.wholesale_price_base_currency, 0) AS wholesale_price_pound'),
                DB::raw('COALESCE(' . $ps . 'custom_product.wholesale_price_base_currency, 0) AS wholesale_price_dollar'),
                DB::raw('COALESCE(' . $ps . 'custom_product.wholesale_price_base_currency, 0) AS wholesale_price_yen'),
                $ps . 'stock_available.quantity',
                $ps . 'product_attribute.wholesale_price AS attr_wholesale_price', 
                DB::raw('COALESCE(' . $ps . 'custom_product_attribute.wholesale_price_base_currency, 0) AS attr_wholesale_price_pound'),
                DB::raw('COALESCE(' . $ps . 'custom_product_attribute.wholesale_price_base_currency, 0) AS attr_wholesale_price_dollar'),
                DB::raw('COALESCE(' . $ps . 'custom_product_attribute.wholesale_price_base_currency, 0) AS attr_wholesale_price_yen')
            )
            ->join(  $ps . 'product_attribute', $ps . 'stock_available.id_product_attribute', '=', $ps . 'product_attribute.id_product_attribute')
            ->join(  $ps . 'product', $ps . 'stock_available.id_product', '=', $ps . 'product.id_product')
            ->leftJoin($ps . 'custom_product', $ps . 'custom_product.id_product', '=', $ps . 'product.id_product')
            ->leftJoin($ps . 'custom_product_attribute', $ps . 'custom_product_attribute.id_product_attribute', '=', $ps . 'product_attribute.id_product_attribute')
            ->join(  $ps . 'manufacturer', $ps . 'product.id_manufacturer', '=', $ps . 'manufacturer.id_manufacturer')
            ->where( $ps . 'stock_available.quantity', '>', 0 )
            ->where( $ps . 'stock_available.id_product_attribute', '<>', 0 )
            ->groupBy(  $ps . 'product_attribute.reference' )
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
        $rates['YEN'] = 0.00588; /** 1/170 - LUDUVINA SET IT ON 03/12/2025**/
        $rates['GBP'] = 1.15;    /** LUDUVINA SET IT ON 03/12/2025**/
        $rates['USD'] = 1;
        
        $data = CorrectedInventoryService::build($this->prestashopMysql2Prefix());
        
        $total = self::createInventoryCSV($data, $rates);

        $data = [
            'actions'    => [],
            'breadcrumbs'=> array_merge($this->breadcrumbs, [
                ['name' => 'Inventory', 'url' => route('finance.download_inventory'), 'no_translation' => 1],
            ]),
            'array' => $data,
            'total' => $total,
            'rates' => $rates,
            'link' => '/admin/download/inventory_' . date('Ymd') . '.csv?t='.rand(),
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
            'breadcrumbs'=> array_merge($this->breadcrumbs, [
                ['name' => 'Intrastat', 'url' => route(request()->routeIs('finance.tools.intrastat.*') ? 'finance.tools.intrastat.index' : 'finance.download_intrastat'), 'no_translation' => 1],
            ]),
            'htmlAfterUpload' => ''
        ];
        
        return View::make('areas/finance/intrastat')->with($data);
    }
    
    public function createCHCSV(){ 

        $period = sprintf('%04d%02d', (int) $this->year, (int) $this->month);
        $fileName = 'INTRA-CH-' . $period . '.csv';
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

        $receptionLines = DB::table('oms_receptions as receptions')
            ->join('oms_reception_lines as reception_lines', 'receptions.id', '=', 'reception_lines.reception_id')
            ->join('oms_billed_order_lines as billed_lines', 'billed_lines.id', '=', 'reception_lines.billed_order_line_id')
            ->whereMonth('receptions.created_at', $this->month)
            ->whereYear('receptions.created_at', $this->year)
            ->select('billed_lines.product_id', 'billed_lines.product_attribute_id', 'reception_lines.qty_received')
            ->get();

        $productIds = $receptionLines->pluck('product_id')->filter()->unique()->values();
        $attributeIds = $receptionLines->pluck('product_attribute_id')->filter()->unique()->values();
        $manufacturerIds = [109, 113, 138, 141, 72, 93, 117, 136, 104, 68, 99, 69, 82, 142, 20, 124, 143, 92, 66, 121, 122, 123, 161];

        $products = $productIds->isEmpty()
            ? collect()
            : DB::connection('mysql2')
                ->table('ps_product as product')
                ->leftJoin('ps_custom_product as custom_product', 'custom_product.id_product', '=', 'product.id_product')
                ->leftJoin('ps_manufacturer as manufacturer', 'manufacturer.id_manufacturer', '=', 'product.id_manufacturer')
                ->whereIn('product.id_product', $productIds)
                ->whereIn('product.id_manufacturer', $manufacturerIds)
                ->select('product.id_product', 'product.reference', 'product.weight', 'product.wholesale_price', 'custom_product.nc', 'manufacturer.country_code as iso_code')
                ->get()
                ->keyBy('id_product');

        $attributes = $attributeIds->isEmpty()
            ? collect()
            : DB::connection('mysql2')
                ->table('ps_product_attribute')
                ->whereIn('id_product_attribute', $attributeIds)
                ->pluck('reference', 'id_product_attribute');

        $data = $receptionLines->map(function ($line) use ($products, $attributes) {
            $product = $products->get((int) $line->product_id);
            if (!$product) {
                return null;
            }

            $quantity = (int) $line->qty_received;
            $attributeReference = $attributes->get((int) $line->product_attribute_id);

            return (object) [
                'iso_code' => $product->iso_code,
                'reference' => $attributeReference ?: $product->reference,
                'qty' => $quantity,
                'nc' => $product->nc,
                'weight' => $product->weight,
                'wholesale_price' => (float) $product->wholesale_price * $quantity,
            ];
        })->filter();
        foreach($data AS $row){
        
            $weight = round( $row->weight, 1);
            $weight = ($weight == 0.0) ? 0.1 : $weight;
            $weight = str_replace(".",",", $weight );
            
            fputcsv($file, ['INTRA-CH', $period, '513881387', $row->reference, $row->nc, $row->iso_code, $row->iso_code, '10', 'EXW', '11', '3', '', $weight, $row->qty, intval($row->wholesale_price), '', ''], ";");
        }
        
        fclose($file);
        
        return json_encode($fileName);
    }

    public function createEXCSV(){ 
        
        $row = 0;
        $moloniPath = public_path() . "/uploads/finance/moloni/moloni.csv";
        $period = $this->moloniPeriod($moloniPath) ?: ($this->year . $this->month);
        $fileName = 'INTRA-EX-' . $period . '.csv';
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

        if (($handle = fopen($moloniPath, "r")) !== FALSE) {

            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                
                $data = array_map(fn ($value) => $this->normalizeMoloniEncoding($value), $data);
                $num = count($data);
                $row++;
                
                if($row > 2){

                    if( (isset($data[12])) && ($data[12] != "") && ($data[0] == 'Fatura-Recibo')) {
                        
                        if (!$this->isIntrastatExcludedReference($data[12], $data[14] ?? '')) {

                            $iso_code = substr($data[5], 0, 2);

                            if(in_array($iso_code, $iso_code_list)){
                                
                                $attr = product_attribute::where('reference', $data[12])->first();
       
                                if(!is_null($attr)){
                                    
                                    $product = product::select('reference')->where('id_product', $attr->id_product)->first();
                                    $reference = $product->reference;
                                    
                                }else{
                                    $reference = $data[12];
                                }
                                
                                $product = product::select(env('DB2_DB_prefix') . 'product.*', env('DB2_DB_prefix') . 'custom_product.nc')
                                    ->join( env('DB2_DB_prefix') . 'manufacturer', env('DB2_DB_prefix') . 'manufacturer.id_manufacturer', '=', env('DB2_DB_prefix') . 'product.id_manufacturer' )
                                    ->leftJoin(env('DB2_DB_prefix') . 'custom_product', env('DB2_DB_prefix') . 'custom_product.id_product', '=', env('DB2_DB_prefix') . 'product.id_product')
                                    ->where('reference', $reference)
                                    ->first();

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
                                
                                
                                $array = ['INTRA-EX', $period, '513881387', $data[12], $nc, $iso_code, 'PT', '10', 'EXW', '11', '3', '', $weight, $data[15], (int) round($this->parseMoloniDecimal($data[18])), '', $nif, ''];
                                
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

    private function moloniPeriod(string $path): ?string
    {
        if (!file_exists($path)) {
            return null;
        }

        if (($handle = fopen($path, 'r')) === false) {
            return null;
        }

        $period = null;
        $row = 0;

        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            $row++;

            if ($row <= 2 || ($data[0] ?? '') !== 'Fatura-Recibo') {
                continue;
            }

            $date = trim((string) ($data[3] ?? ''));
            if (preg_match('/^(\d{4})-(\d{2})-\d{2}$/', $date, $matches)) {
                $period = $matches[1] . (int) $matches[2];
                break;
            }
        }

        fclose($handle);

        return $period;
    }

    private function isIntrastatExcludedReference(string $reference, string $description = ''): bool
    {
        $reference = trim($reference);
        $description = trim($description);

        if ($reference === '') {
            return true;
        }

        $excludedReferences = ['Portes', 'ccFee'];
        if (in_array($reference, $excludedReferences, true)) {
            return true;
        }

        foreach (['SHIPPING', 'UPS', 'DPD', 'NAC', 'MON.REL'] as $prefix) {
            if (self::startsWith($reference, $prefix)) {
                return true;
            }
        }

        return stripos($description, 'shipping') !== false
            || stripos($description, 'portes') !== false
            || stripos($description, 'transport') !== false;
    }

    private function parseMoloniDecimal(string $value): float
    {
        $value = str_replace([' ', '.'], ['', ''], trim($value));
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function normalizeMoloniEncoding($value): string
    {
        if (!is_string($value)) {
            return '';
        }

        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
        }

        $converted = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $value);

        return $converted !== false ? $converted : $value;
    }
    
    public function save_currency_rate(Request $request){
        return issues::saveCurrencyRate($request);
    }

}
