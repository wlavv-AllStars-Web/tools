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

class customerSupportController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('customer'), 'url' => route('customer.index')];

    }

    public function index()
    {
        $data = [
            'counters'      => self::counters(),
            'panels'        => [],
            'accessList'    => self::accessList(),
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs
        ];

        return View::make('areas/customer/index')->with($data);
    }

    private function accessList(){
        
        $accessList = array();
        return $accessList;
    }

    private function counters(){
        $counters = array();
        $counters['warranties']                 = issues::dashboard_warranties('counter');
        $counters['returns']                    = issues::dashboard_returns('counter');
        $counters['waiting_info']               = orders::dashboard_waiting_info('counter');
        return $counters;
    }

}
