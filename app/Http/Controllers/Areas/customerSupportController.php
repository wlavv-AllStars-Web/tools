<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\modules\dashboard\dashboard;

class customerSupportController extends Controller
{
    public $actions = [];
    public $breadcrumbs = [];
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('customer'), 'url' => route('customer.index')];

    }

    public function index()
    {
        $data = [
            'counters'      => dashboard::calculateAndGetCountersOfTab('support'),
            'panels'        => [],
            'accessList'    => $this->accessList(),
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs
        ];

        return View::make('areas/customer/index')->with($data);
    }

    private function accessList(){
        
        $accessList = array();
        return $accessList;
    }

}
