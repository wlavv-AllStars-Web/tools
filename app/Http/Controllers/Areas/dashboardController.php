<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Models\prestashop\product;
use App\Models\prestashop\orders;
use App\Models\prestashop\stock_available;

class dashboardController extends Controller
{
    public $actions;
    public $breadcrumbs;

    public function index()
    {

        $this->breadcrumbs[] = [ 'name' =>  trans('Dashboard'), 'url' => route('dashboard.index')];

        $data = [
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs,
            'counters'   => self::counters(2),
            'panels'     => self::panels(2),
        ];

        return View::make('areas/dashboard/index')->with($data);
        
    }

    private function panels($profile){

        $panels = array();

        
        if(in_array( $profile, [1, 2])){ /** ADMIN AND LOGISTICS **/
            $panels['partialOrders'] = orders::panelPartialOrders();
            $panels['negativeStock'] = stock_available::panelNegativeStock();
        }

        if(in_array( $profile, [1, 3])){}
        if(in_array( $profile, [1, 4])){}
        if(in_array( $profile, [1, 5])){}
        if(in_array( $profile, [1, 6])){}
        if(in_array( $profile, [1, 7])){}

        return $panels;
    }

    private function counters($profile){

        $counters = array();

        if(in_array( $profile, [1, 2])){ 
            $counters['partialOrders'] = orders::counterPartialOrders();
            $counters['negativeStock'] = stock_available::counterNegativeStock();
        }
        if(in_array( $profile, [1, 3])){}
        if(in_array( $profile, [1, 4])){}
        if(in_array( $profile, [1, 5])){}
        if(in_array( $profile, [1, 6])){}
        if(in_array( $profile, [1, 7])){}

        return $counters;
    }
    

    public function create(){}
    public function store(Request $request){}
    public function show(string $id){}
    public function edit(string $id){}
    public function update(Request $request, string $id){}
    public function destroy(string $id){}
}
