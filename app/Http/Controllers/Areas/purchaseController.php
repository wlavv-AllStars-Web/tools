<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\modules\dashboard\dashboard;

class purchaseController extends Controller
{
    public $actions = [];
    public $breadcrumbs = [];

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('purchase'), 'url' => route('purchase.index')];
    }

    public function index()
    {
        $data = [
            'counters'      => dashboard::calculateAndGetCountersOfTab('purchase'),
            'accessList'    => $this->accessList(),
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs
        ];

        return View::make('areas/purchase/index')->with($data);
    }

    private function accessList(){
        
        $accessList = array();
        $accessList[]                           = ['name' =>  trans('messages.oms'),                    'url' => route('erp.oms.dashboard'),                           'icon' => '<i style="font-size: 40px;" class="fa-solid fa-warehouse"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.Auto Orders'),         	'url' => route('purchase.tools.auto_orders.index'),              'icon' => '<i style="font-size: 40px;" class="fa fa-xl fa-solid fa-boxes-packing"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.backorders_suppliers'),   'url' => route('purchase.tools.suppliersBackorders.index'),       'icon' => '<i style="font-size: 40px;" class="fa fa-xl fa-solid fa-arrow-down-short-wide"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.priceMap'),               'url' => route('purchase.tools.price_map.index'),                 'icon' => '<i style="font-size: 40px;" class="fa fa-solid fa-money-check-dollar"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.suppliers_map'),          'url' => route('purchase.tools.suppliers.map.index'),             'icon' => '<i style="font-size: 40px;" class="fa fa-solid fa-puzzle-piece"></i>'];
        $accessList[]                           = ['name' =>  trans('messages.quote'),                  'url' => route('purchase.tools.quotes.index', ['list' => 1]),     'icon' => '<i style="font-size: 40px;" class="fa-solid fa-bell-concierge"></i>'];
        return $accessList;
    }
}
