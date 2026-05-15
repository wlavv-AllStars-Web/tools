<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Http\Controllers\CustomTools\mailsController;

use App\Models\prestashop\product;
use App\Models\prestashop\product_lang;
use App\Models\prestashop\asm_dashboard;
use App\Models\prestashop\asm_newsletter_email;
use App\Models\prestashop\ASD_missing_images;

use App\Models\modules\compats\compats_newsletter;
use App\Models\modules\compats\compats_product;

use App\Models\modules\dashboard\dashboard;

class marketingController extends Controller{
    
    public $actions = [];
    public $breadcrumbs = [];
    
    public function __construct(){
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('marketing'), 'url' => route('marketing.index')];
    }

    public function index(){
        
        $data = [
            'counters'      => dashboard::calculateAndGetCountersOfTab('marketing'),
            'panels'        => [],
            'accessList'    => $this->accessList(),
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs
        ];

        return View::make('areas/marketing/index')->with($data);
    }

    private function accessList(){
        
        $accessList = array();
        $accessList[]                           = ['name' =>  trans('messages.TV'),         	    'url' => route('marketing.tools.tv.index'),      		       'icon' => '<i style="font-size: 40px;" class="fa-solid fa-tv"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.homepage'),         	'url' => route('marketing.tools.homepage.asm.index'),         'icon' => '<i style="font-size: 40px;" class="fa-solid fa-house-laptop"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.homepage_asd'),       'url' => route('marketing.tools.homepage.asd.index'),         'icon' => '<i style="font-size: 40px;" class="fa-solid fa-house-laptop"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.asm_resources'),      'url' => route('marketing.tools.resources.asm.index'),        'icon' => '<i style="font-size: 40px;" class="fa-solid fa-folder-open"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.resources'),         	'url' => route('marketing.tools.resources.asd.index'),        'icon' => '<i style="font-size: 40px;" class="fa-solid fa-folder-open"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.cars'),         	    'url' => route('marketing.tools.car_gallery.index'),          'icon' => '<i style="font-size: 40px;" class="fa-solid fa-car"></i>'];

        return $accessList;
    }

    public function post(Request $request){

        $sold = array();
        
        if( $request->action == 'productForNewsletter' ){
            
            $store = (string) $request->input('store', 'ASM');
            $compatStoreId = (int) config('allstars.stores.' . $store . '.compat_store_id', 2);
            $compatIds = compats_product::where('id_product', (int) $request->id_product)
                ->where('store', $compatStoreId)
                ->pluck('id_compat');

            $emails = [
                'en' => collect(),
                'es' => collect(),
                'fr' => collect()
            ];

            if ($compatIds->isNotEmpty()) {
                foreach (array_keys($emails) as $iso) {
                    $emails[$iso] = compats_newsletter::whereIn('id_compat', $compatIds)
                        ->where('store', $compatStoreId)
                        ->where('iso_code', $iso)
                        ->where('newsletter', 1)
                        ->pluck('email')
                        ->filter()
                        ->unique()
                        ->values();
                }
            }
            
            foreach($emails  AS $iso => $array_emails_lang){
                
                $id_lang = self::getIDLangFromLocale($iso);
                
                $html= self::getHtmlNewsletter($request->id_product, $id_lang);
                
                $product_name = product_lang::getProductName($request->id_product, $id_lang);

                foreach($array_emails_lang  AS $email){
                    asm_newsletter_email::insertRow($id_lang, $request->id_product, $email, 'All Stars Motorsport - '. $product_name, $html);
                }
                
            }

            /** Remove o registo de pedido de notificação do roduto da base de dados **/
            $data = (object)[
                'panel' => 'products_for_newsletter',
                'var_1' => $request->id_product,
                'var_2' => $request->reference,
                'var_3' => '',
            ];
                
            asm_dashboard::addException($data);

            return response()->json([ 
                'added'  => 'true',
                'result' => 'success' ]);
            
        }elseif( $request->action == 'removeProductForNewsletter'){
            
            $data = (object)[
                'panel' => 'products_for_newsletter',
                'var_1' => $request->id_product,
                'var_2' => $request->reference,
                'var_3' => '',
            ];
                
            asm_dashboard::addException($data);
            
            return response()->json([ 
                'added'  => false,'result' => 'success' ]);

        }
    }

    private static function getHtmlNewsletter($id_product, $id_lang){
        
        $product = product::select("*", DB::RAW('ps_product.price AS main_price'), DB::RAW('ps_product_lang.name AS product_name') )->leftJoin('ps_product_lang', 'ps_product.id_product', '=', 'ps_product_lang.id_product')
            ->leftJoin('ps_manufacturer', 'ps_product.id_manufacturer', '=', 'ps_manufacturer.id_manufacturer')
            ->leftJoin('ps_image', 'ps_product.id_product', '=', 'ps_image.id_product')
            ->leftJoin('ps_specific_price', 'ps_product.id_product', '=', 'ps_specific_price.id_product')
            ->where('ps_product.id_product', $id_product)
            ->where('ps_product_lang.id_lang', $id_lang)
            ->where('ps_image.cover', 1)
            ->first();
        
        $email = NEW mailsController();
        return $email->createStructure('asm_newsletter', 'asm_newsletter', trans('mails.Newsletter - All Stars Motorsport'), $product, $id_lang);
    }
    
    public function getIDLangFromLocale($locale){

        switch ($locale) {
            case 'en':
                $id_lang = 1;
                break;
            case 'es':
                $id_lang = 4;
                break;
            case 'fr':
                $id_lang = 5;
                break;
            default:
                $id_lang = 1;
        }

        return $id_lang;
    }
    
    public function getASDMissingImages(){
        return ASD_missing_images::addMissingImages();
    }
}
