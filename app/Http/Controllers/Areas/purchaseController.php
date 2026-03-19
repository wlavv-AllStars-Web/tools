<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\modules\productIssues\productIssues;
use App\Models\modules\quotes\quotes;

use App\Models\prestashop\product;

use App\Models\modules\dashboard\dashboard;

class purchaseController extends Controller
{
    public $actions;
    public $breadcrumbs;

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('purchase'), 'url' => route('purchase.index')];
    }

    public function index()
    {

        dashboard::getCountersContentOfTabPanel( 'purchase', 'dashboard_in_stock_not_sold');
        
        $data = [
            'counters'      => dashboard::getCountersOFTab('purchase'),
            'accessList'    => self::accessList(),
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs
        ];

        return View::make('areas/purchase/index')->with($data);
    }

    private function accessList(){
        
        $accessList = array();
        $accessList[]                           = ['name' =>  trans('messages.Auto Orders'),         	'url' => route('autoOrders.index'),      		'icon' => '<i style="font-size: 40px;" class="fa fa-xl fa-solid fa-boxes-packing"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.backorders_suppliers'),   'url' => route('suppliersBackorders.index'),    'icon' => '<i style="font-size: 40px;" class="fa fa-xl fa-solid fa-arrow-down-short-wide"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.priceMap'),               'url' => route('priceMap.index'),               'icon' => '<i style="font-size: 40px;" class="fa fa-solid fa-money-check-dollar"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.suppliers_map'),          'url' => route('suppliersMap.index'),           'icon' => '<i style="font-size: 40px;" class="fa fa-solid fa-puzzle-piece"></i>'];
        /**$accessList[]                           = ['name' =>  trans('messages.brandResources'),         'url' => route('manufacturers.resources'),      'icon' => '<i style="font-size: 40px;" class="fa-solid fa-photo-film"></i>'];**/
        $accessList[]                           = ['name' =>  trans('messages.quote'),                  'url' => route('quotes.index', ['list' => 1]),  'icon' => '<i style="font-size: 40px;" class="fa-solid fa-bell-concierge"></i>'];
        return $accessList;
    }
}