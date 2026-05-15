<?php
namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\modules\basePrice\basePrice;
use App\Models\prestashop\manufacturers;
use App\Models\prestashop\currency;
use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;

use App\Models\prestashop\product_shop;
use App\Models\prestashop\product_attribute_shop;

class basePriceController extends Controller
{

    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('webmaster'),  'url' => route('web.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('Base Price'), 'url' => route('basePrice.index')];
        $this->actions[]     = [];

    }
    
    public function index()
    {
    	$brands = manufacturers::select('id_manufacturer', 'name')->orderBy('name')->get();
    	$rows = basePrice::getRows();
    	$currency_eur = currency::where('iso_code', 'EUR')->first();
    	$currency_usd = currency::where('iso_code', 'USD')->first();
    	$currency_gbp = currency::where('iso_code', 'GBP')->first();
    	$currency_yen = currency::where('iso_code', 'JPY')->first();
    	
        $breadcrumbs = $this->breadcrumbs;
        
        return view('customTools.basePrice.index', compact('brands', 'rows', 'breadcrumbs', 'currency_eur', 'currency_usd', 'currency_gbp', 'currency_yen'));
    }

    public function store(Request $request)
    {
        if ($request->hasFile('src')) {
            $file = $request->file('src');
            $filename = $request->id_manufacturer . '.csv';
            $file->move(public_path('uploads/files/basePrices/'), $filename);
            
            basePrice::addUpdate($request->id_manufacturer);
            
        }
        
        return redirect()->back()->with('success', 'Item saved successfully!');
    }

    public function execute(Request $request)
    {
        $array_products = [];
    
        $path = public_path('uploads/files/basePrices/' . $request->id_manufacturer . '.csv');
    
        if (!file_exists($path)) {
            die("Ficheiro não encontrado: $path");
        }
    
        $mapa = [];
        if (($handle = fopen($path, "r")) !== false) {
            $header = fgetcsv($handle, 1000, ";");
    
            while (($row = fgetcsv($handle, 1000, ";")) !== false) {
                $linha = array_combine($header, $row);
                $mapa[strtolower($linha['reference'])] = $linha;
            }
    
            fclose($handle);
        }
        
        $manufacturer = manufacturers::where('id_manufacturer', $request->id_manufacturer)->first();
        
        $currency = strtolower( $manufacturer->currency );
        
        $currency_data = currency::where('iso_code', '=', $manufacturer->currency)->first();

        $conversion_rate = $currency_data->conversion_rate;
    
        $products = product::select('id_product', DB::raw('0 as id_product_attribute'), 'reference')
            ->where('id_manufacturer', $request->id_manufacturer)
            ->get();
    
        foreach ($products as $product) {

            $attributes = product_attribute::select('id_product', 'id_product_attribute', 'reference')
                ->where('id_product', $product->id_product)
                ->get();
    
            if ($attributes->count() > 0) {
                foreach ($attributes as $attr) {
                    $ref = strtolower($attr->reference);
    
                    $array_products[] = [
                        'id_product' => $attr->id_product,
                        'id_product_attribute' => $attr->id_product_attribute,
                        'reference' => $attr->reference,
                        'conversion_rate' => $conversion_rate,
                        'basePrice' => $mapa[$ref][$currency] ?? null,
                    ];
                }
            } else {
                $ref = strtolower($product->reference);
    
                $array_products[] = [
                    'id_product' => $product->id_product,
                    'id_product_attribute' => 0,
                    'reference' => $product->reference,
                    'conversion_rate' => $conversion_rate,
                    'basePrice' => $mapa[$ref][$currency] ?? null,
                ];
            }
        }
        
        return self::updateRows($array_products);
    }

    public static function updateRows($products)
    {

        foreach($products AS $product){
            
            $wholesale_price = $product['basePrice'] * $product['conversion_rate'];
            
            if($product['id_product_attribute'] == 0){
                
                if( isset($wholesale_price) && ($wholesale_price > 0 ) ){
                    product::where('id_product', '=', $product['id_product'])->update(['wholesale_price' => $wholesale_price]);
                    product_shop::where('id_product', '=', $product['id_product'])->update(['wholesale_price' => $wholesale_price]);
                }
                
            }else{

                if( isset($wholesale_price) && ( $wholesale_price > 0 ) ){
                    product_attribute::where('id_product', '=', $product['id_product'])->where('id_product_attribute', '=', $product['id_product_attribute'])->update(['wholesale_price' => $wholesale_price]);
                    product_attribute_shop::where('id_product', '=', $product['id_product'])->where('id_product_attribute', '=', $product['id_product_attribute'])->update(['wholesale_price' => $wholesale_price]);
                }
                
            }
            
        }
        
        return 1;
    }

    public static function updatePricing(Request $request)
    {
        if( $request->type == 'wholesale'){
            $data = self::getManufacturerPricingRows((int) $request->id_manufacturer);
            
            foreach($data AS $item){

                $new_wholesale = ($item['wholesale_price'] + 0) * $request->wholesale_convertion;

                if((int) $item['id_product_attribute'] === 0){
                    product_shop::where('id_product', '=', $item['id_product'])
                        ->where('id_shop', 3)
                        ->update(['wholesale_price' => $new_wholesale]);
                }else{
                    product_attribute_shop::where('id_product', '=', $item['id_product'])
                        ->where('id_product_attribute', '=', $item['id_product_attribute'])
                        ->where('id_shop', 3)
                        ->update(['wholesale_price' => $new_wholesale]);
                }
            }

        }else{
            echo 'PRICE';
            exit;
        }
        return 1;
    }

    public static function pricingData(Request $request)
    {
        return response()->json(self::getManufacturerPricingRows((int) $request->id_manufacturer));
    }

    private static function getManufacturerPricingRows(int $idManufacturer): array
    {
        $prefix = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));

        return DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->join($prefix . 'product_shop as ps', function ($join) {
                $join->on('ps.id_product', '=', 'p.id_product')
                    ->where('ps.id_shop', 3);
            })
            ->leftJoin($prefix . 'product_attribute as pa', 'pa.id_product', '=', 'p.id_product')
            ->leftJoin($prefix . 'product_attribute_shop as pas', function ($join) {
                $join->on('pas.id_product_attribute', '=', 'pa.id_product_attribute')
                    ->where('pas.id_shop', 3);
            })
            ->where('p.id_manufacturer', $idManufacturer)
            ->select([
                'p.id_product',
                DB::raw('COALESCE(pa.id_product_attribute, 0) as id_product_attribute'),
                DB::raw('CASE WHEN COALESCE(pa.id_product_attribute, 0) > 0 THEN pa.reference ELSE p.reference END as reference'),
                DB::raw('COALESCE(ps.price, p.price, 0) + CASE WHEN COALESCE(pa.id_product_attribute, 0) > 0 THEN COALESCE(pas.price, pa.price, 0) ELSE 0 END as price'),
                DB::raw('CASE WHEN COALESCE(pa.id_product_attribute, 0) > 0 AND COALESCE(pas.wholesale_price, pa.wholesale_price, 0) > 0 THEN COALESCE(pas.wholesale_price, pa.wholesale_price, 0) ELSE COALESCE(ps.wholesale_price, p.wholesale_price, 0) END as wholesale_price'),
            ])
            ->orderBy('reference')
            ->get()
            ->map(fn ($item) => [
                'id_product' => (int) $item->id_product,
                'id_product_attribute' => (int) $item->id_product_attribute,
                'reference' => (string) $item->reference,
                'price' => (float) $item->price,
                'wholesale_price' => (float) $item->wholesale_price,
            ])
            ->all();
    }
}
