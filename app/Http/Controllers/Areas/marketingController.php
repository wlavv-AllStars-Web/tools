<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Http\Controllers\CustomTools\mailsController;

use App\Models\prestashop\product;
use App\Models\prestashop\product_lang;
use App\Models\prestashop\asm_youtube;
use App\Models\prestashop\product_attribute;
use App\Models\prestashop\ASM_ukoo_customer;
use App\Models\prestashop\asm_dashboard;
use App\Models\prestashop\asm_newsletter_email;
use App\Models\prestashop\ASD_missing_images;

use App\Models\modules\ukoocompat\ukoocompat_compat;
use App\Models\modules\ukoocompat\ukoocompat_compat_criterion;

use App\Models\modules\dashboard\dashboard;

class marketingController extends Controller{
    
    public $actions;
    public $breadcrumbs;
    
    public function __construct(){
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('marketing'), 'url' => route('marketing.index')];
    }

    public function index(){
        
        $data = [
            'counters'      => dashboard::getCountersOFTab('marketing'),
            'panels'        => [],
            'accessList'    => self::accessList(),
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs
        ];

        return View::make('areas/marketing/index')->with($data);
    }

    private function accessList(){
        
        $accessList = array();
        return $accessList;
    }
    
    private function counters(){
        
        $counters = array();
        $counters['no_real_photos'] = product::dashboard_no_real_photos('counter');
        $counters['product_less_then_5'] = product::dashboard_product_less_then_5_pics('counter');
        $counters['attribute_less_then_5'] = product_attribute::dashboard_attribute_less_then_5_pics('counter');
        $counters['on_video'] = product::dashboard_no_video('counter');
        $counters['youtube'] = asm_youtube::dashboard_broken_link('counter');
        $counters['products_for_newsletter'] = product::dashboard_products_for_newsletter('counter');
        $counters['newsletter_registration'] = ASM_ukoo_customer::dashboard_newsletter_registration ('counter');
        $counters['ASD_missing_images'] = ASD_missing_images::dashboard_missing_images('counter');

        return $counters;
    }

    public function post(Request $request){

        $sold = array();

        if( $request->action == 'productForNewsletter' ){
            
            $detail_compat = array();
            $compats = ukoocompat_compat::getCompatsOfTheProduct($request->id_product);
            
            foreach( $compats AS $compat){
                $detail_compat[] = ukoocompat_compat_criterion::getCompatDetails($compat->id_ukoocompat_compat);
            }
            
            foreach( $detail_compat AS $detail){
                $emails = [
                    'en' => ASM_ukoo_customer::getEmailsOfTheCompats($detail, 'en'),
                    'es' => ASM_ukoo_customer::getEmailsOfTheCompats($detail, 'es'),
                    'fr' => ASM_ukoo_customer::getEmailsOfTheCompats($detail, 'fr')
                ];
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

            return response()->json([ 'result' => 'success' ]);
            
        }elseif( $request->action == 'removeProductForNewsletter'){
            
            $data = (object)[
                'panel' => 'products_for_newsletter',
                'var_1' => $request->id_product,
                'var_2' => $request->reference,
                'var_3' => '',
            ];
                
            asm_dashboard::addException($data);
            
            return response()->json([ 'result' => 'success' ]);

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

