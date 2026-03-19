<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\modules\dashboard\dashboard;
use App\Models\prestashop\order_return;
use App\Models\prestashop\currency;

use App\Models\modules\shipping_erp\shipping_erp;



class webController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('web'), 'url' => route('web.index')];
    }

    public function index()
    {

        /**
        dashboard::getCountersContentOfTabPanel( 'web', 'dashboard_progress_order_return');
        dashboard::getCountersContentOfTabPanel( 'web', 'dashboard_received_order_return');
        **/
        
        //$data = shipping_erp::getProductsOfERP(542);
        //dd($data[0][0]);

        //dashboard::getCountersContentOfTabPanel( 'web', 'dashboard_closed_order_warranty');
                
        $data = [
            'rates' => self::currencies(),
            'counters'      => dashboard::getCountersOFTab('web'),
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs,
            'accessList' => self::accessList()
        ];

        return View::make('areas/web/index')->with($data);
    }

    public static function getASDCurrencies(){

        $data = [];
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://www.all-stars-distribution.com/custom/api/currencies/getCurrencies.php');
        
        if($response->getStatusCode() == 200) $data = json_decode($response->getBody(), true);

        return $data;
    }

    private function currencies(){

        $asm_currencies = currency::where('deleted', 0)->pluck('conversion_rate', 'iso_code');
        
        $asd_currencies = self::getASDCurrencies();
        
        return [
                'ASM' => [
                    'EUR' => [
                        'purchase' =>  $asm_currencies['EUR'],
                        'sale'     =>  $asm_currencies['EUR'],
                        ],
                    'USD' => [
                        'purchase' =>  $asm_currencies['USW'],
                        'sale'     =>  $asm_currencies['USS'],
                        ],
                    'GBP' =>  [
                        'purchase' =>  $asm_currencies['GBW'],
                        'sale'     =>  $asm_currencies['GBS'],
                        ],
                    'YEN' =>  [
                        'purchase' =>  $asm_currencies['JPW'],
                        'sale'     =>  $asm_currencies['JPS'],
                        ],
                    ],
                'ASD' =>[
                    'EUR' => [
                        'purchase' =>  $asd_currencies['EUR'],
                        'sale'     =>  $asd_currencies['EUR'],
                        ],
                    'USD' => [
                        'purchase' =>  $asd_currencies['USW'],
                        'sale'     =>  $asd_currencies['USS'],
                        ],
                    'GBP' =>  [
                        'purchase' =>  $asd_currencies['GBW'],
                        'sale'     =>  $asd_currencies['GBS'],
                        ],
                    'YEN' =>  [
                        'purchase' =>  $asd_currencies['JPW'],
                        'sale'     =>  $asd_currencies['JPS'],
                        ],
                    ],
                ];  
    }
    
    
    private function accessList(){
        
        $accessList = array();
        $accessList[]                           = ['name' =>  trans('messages.logs'),               'url' => route('logs.index'),                       'icon' => '<i style="font-size: 40px;" class="fa fa-history" aria-hidden="true"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.COMPATS'),         	'url' => route('compats.index'),      		        'icon' => '<i style="font-size: 40px;" class="fa-solid fa-boxes-packing"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.TV'),         	    'url' => route('tv.index'),      		            'icon' => '<i style="font-size: 40px;" class="fa-solid fa-tv"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.purchasePrice'),      'url' => route('purchasePrice.index'),              'icon' => '<i style="font-size: 40px;" class="fa-solid fa-money-bill-transfer"></i>'];
        //$accessList[]                           = ['name' =>  trans('messages.basePrice'),        'url' => route('basePrice.index'),                  'icon' => '<i style="font-size: 40px;" class="fa-solid fa-eur"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.trackingTranslation'),'url' => route('translation.index'),                'icon' => '<i style="font-size: 40px;" class="fa-solid fa-language"></i>'];
        //$accessList[]                           = ['name' =>  trans('messages.ERP'),              'url' => route('erp.index', ['list' => 1]),         'icon' => '<i style="font-size: 40px;" class="fa-solid fa-industry"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.tasks'),              'url' => route('asg_tasks.index'),                  'icon' => '<i style="font-size: 40px;" class="fa-solid fa-list-check"></i>'];
        
        

        return $accessList;
    }
    
    public function create() {}
    public function store(Request $request) { }
    public function show(string $id) { }
    public function edit(string $id) { }
    public function update(Request $request, string $id) { }
    public function destroy(string $id) { }
}
