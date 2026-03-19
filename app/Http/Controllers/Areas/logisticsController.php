<?php

namespace App\Http\Controllers\Areas;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\prestashop\orders;
use App\Models\prestashop\order_carrier;
use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;
use App\Models\prestashop\stock_available;

use App\Models\modules\dashboard\dashboard;

class logisticsController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('logistics.index')];
    }

    public function index()
    {
        $data = [
            'counters'      => dashboard::getCountersOFTab('logistics'),
            'panels'        => [],
            'accessList'    => self::accessList(),
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs
        ];

        return View::make('customTools/logistics/index')->with($data);
    }

    private function accessList(){
        $accessList = array();
        $accessList[] = ['name' =>  trans('Logistics'), 'url' => route('logistics.index')];
        return $accessList;
    }

    private function counters(){
        $counters = array();
        $counters['partialOrders']          = orders::dashboard_partial_orders('counters');
        $counters['negativeStock']          = stock_available::dashboard_negative_stock('counter');
        $counters['no_tracking']            = orders::dashboard_without_tracking('counters');
        $counters['same_ean_diff_ref']      = product::dashboard_same_ean_diff_ref('counter');
        $counters['qtity_arrive']           = product::dashboard_qtd_arrive('counters');
        
        /** MAGGY **/
        $logged = Auth::user()->id;
        
        if( in_array($logged, [2, 43, 59])) $counters['no_weight']= product::dashboard_no_weight('counters');
        
        
        /** OUTROS **/
        $counters['no_size']                = product::dashboard_no_size('counters');
        $counters['no_ean']                 = product::dashboard_no_ean('counters');
        $counters['no_housing']             = product::dashboard_no_housing('counters');
        $counters['end_of_life']            = product::dashboard_end_of_life_logistics('counters');
        $counters['same_sku_diff_measures'] = product::dashboard_same_sku_diff_measures('counters');
        $counters['shipping_restrictions']  = product::dashboard_shipping_restrictions('counter');

        $counters['non_standard']           = product::dashboard_non_standard('counter');

        return $counters;
    }
}
