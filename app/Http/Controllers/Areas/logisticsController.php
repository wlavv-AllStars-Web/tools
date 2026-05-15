<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Models\modules\dashboard\dashboard;

class logisticsController extends Controller
{
    public $actions = [];
    public $breadcrumbs = [];
    
    public function __construct(){
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('logistics.index')];
    }

    public function index(){
        $data = [
            'counters'      => dashboard::calculateAndGetCountersOfTab('logistics'),
            'accessList'    => ['name' =>  trans('Logistics'), 'url' => route('logistics.index')],
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs
        ];

        return View::make('customTools/logistics/index')->with($data);
    }

}