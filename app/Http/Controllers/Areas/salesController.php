<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\prestashop\product;
use App\Models\prestashop\orders;
use App\Models\prestashop\issues;
use App\Models\prestashop\product_lang;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\asm_email_alert;
use App\Models\modules\price_map\price_map;
use App\Models\modules\supplier_warranty_issues\supplier_warranty_issues;

use App\Models\modules\dashboard\dashboard;

class salesController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('sales'), 'url' => route('sales.index')];

    }

    public function index()
    {

        dashboard::getCountersContentOfTabPanel( 'purchase', 'dashboard_quote_backoffice');
        dashboard::getCountersContentOfTabPanel( 'sales', 'dashboard_quote_frontoffice');

        dashboard::getCountersContentOfTabPanel( 'sales', 'dashboard_new_order_warranty');
        dashboard::getCountersContentOfTabPanel( 'sales', 'dashboard_progress_order_warranty');
        dashboard::getCountersContentOfTabPanel( 'finance', 'returns_warranties');
        
        $data = [
            'counters'      => dashboard::getCountersOFTab('sales'),
            'panels'        => [],
            'accessList'    => self::accessList(),
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs,
            'racio'         => price_map::getAll()
        ];

        return View::make('areas/sales/index')->with($data);
    }

    private function accessList(){
        
        $accessList = array();
        $accessList[]                           = ['name' =>  trans('messages.backorders'),             'url' => route('backorders.index'),             'icon' => '<i style="font-size: 40px;" class="fa-solid fa-business-time"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.PRODUCT ISSUES'),         'url' => route('productIssues.index'),          'icon' => '<i style="font-size: 40px;" class="fa fa-solid fa-box-open"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.quote'),                  'url' => route('quotes.index', ['list' => 1]),  'icon' => '<i style="font-size: 40px;" class="fa-solid fa-bell-concierge"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.returns'),                'url' => route('returns.index'),                'icon' => '<i style="font-size: 40px;" class="fa-solid fa-person-walking-arrow-loop-left"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.warranties'),             'url' => route('warranties.index'),             'icon' => '<i style="font-size: 40px;" class="fa-solid fa-award"></i>'];

        return $accessList;
    }

    private function counters(){
        $counters = array();

        $counters['warranties']                 = supplier_warranty_issues::dashboard_warranties('counter');
        $counters['returns']                    = issues::dashboard_returns('counter');
        $counters['waiting_info']               = orders::dashboard_waiting_info('counter');
        
        $counters['no_instructions']            = product::dashboard_no_instructions('counter');
        $counters['clients_request']            = asm_email_alert::dashboard_product_request('counter');
        $counters['no_availability_text']       = product_lang::dashboard_no_availability_text('counter');
        $counters['ec_approved']                = product::dashboard_ec_approved('counter');
        $counters['universal_products']         = product::dashboard_universal_products('counter');
        $counters['no_compatibilities']         = product::dashboard_no_compatibilities('counter');
        $counters['compatibilities_exception']  = product::dashboard_compatibilities_exception('counter');
        $counters['ean_with_spaces']            = product::dashboard_ean_with_spaces('counter');
        $counters['recommended_products']       = product::dashboard_recommended_products('counter');
        $counters['without_brand']              = product::dashboard_without_brand('counter');
        $counters['without_category']           = product::dashboard_without_category('counter');
        $counters['visibility']                 = product::dashboard_visibility('counter');
        $counters['references_with_spaces']     = product::dashboard_references_with_spaces('counter');
        $counters['titles_double_spaces']       = product_lang::dashboard_double_spaces('counter');

        $counters['products_pack']              = product::dashboard_packs('counter');

        return $counters;
    }

    public function post(Request $request){

        if( $request->action == 'sendNewsletter' ){
            
            $requested_product = asm_email_alert::find($request->id);
            
            $product_detail = product::with('lang')->where('id_product' , $requested_product->id_product)->first();
            
            $data = [
                'requested' => $requested_product,
                'product_info' => $product_detail
            ];
            
            $email = NEW mailsController();
            $html = $email->createStructure('ASM', 'requestedProducts', trans('mails.Requested products notification'), $data, $requested_product->id_lang);
            //$email->send($requested_product->email, $html, trans('mails.Requested products notification'));
            

            return response()->json([ 'result' => 'success' ]);
            
        }elseif( $request->action == 'removeItemForNewsletter' ){
            
            //asm_email_alert::where('id', $request->id)->delete();
            
            return response()->json([ 'result' => 'success' ]);

        }

    }
}
