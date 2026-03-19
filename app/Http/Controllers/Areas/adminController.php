<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\prestashop\currency;
use App\Models\prestashop\pack;
use App\Models\prestashop\product;
use App\Models\prestashop\specific_price;
use App\Models\prestashop\product_comment;
use App\Models\prestashop\stock_available;
use App\Models\prestashop\groupinc_configuration;

use App\Models\modules\dashboard\dashboard;

class adminController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('administration'), 'url' => route('administration.index')];

    }

    public function index()
    {
        $data = [
            'rates' => self::currencies(),
            'actions'    => $this->actions,
            'counters'      => dashboard::getCountersOFTab('admin'),
            'breadcrumbs'=> $this->breadcrumbs,
            'accessList' => [
                ['name' =>  trans('messages.tasks'),'url' => route('asg_tasks.index'),                'icon' => '<i style="font-size: 40px;" class="fa-solid fa-list-check"></i>']

                /**['name' =>  trans('administration'), 'url' => route('administration.index')],*/
            ]
        ];

        return View::make('areas/administration/index')->with($data);
    }

    private function counters(){

        $counters = array();
        $counters['end_of_life_without_stock']  = product::dashboard_end_of_life_without_stock('counter');
        $counters['on_clearence']               = product::dashboard_on_clearence('counter');
        $counters['end_of_life']                = product::dashboard_end_of_life('counter');
        $counters['no_purchase_price']          = product::dashboard_no_purchase_price('counter');
        $counters['wholesale_price_exVAT']      = product::dashboard_wholesale_price_exVAT('counter');
        $counters['same_sku_diff_discount']     = specific_price::dashboard_same_sku_diff_discount('counter');
        $counters['reviews']                    = product_comment::dashboard_reviews('counter');
        $counters['global_discounts']           = groupinc_configuration::dashboard_global_discounts('counter');
        $counters['in_stock_not_sold']          = product::dashboard_in_stock_not_sold('counter');
        $counters['avs_on_erp']                 = product::dashboard_avs_on_erp('counter');
        $counters['packs_without_stock']        = pack::dashboard_packs_without_stock('counter');
        $counters['locked_products_with_stock'] = product::dashboard_locked_products_with_stock('counter');
        
        
        return $counters;
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
}
