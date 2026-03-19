<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\modules\dashboard\dashboard;
use App\Models\prestashop\order_return;

class dataController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('data'), 'url' => route('data.index')];
    }

    public function index()
    {

        dashboard::getCountersContentOfTabPanel( 'data', 'externalProductsNoImage');

        $data = [
            'counters'      => dashboard::getCountersOFTab('data'),
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs,
            'accessList' => self::accessList()
        ];

        return View::make('areas/data/index')->with($data);
    }

    private function accessList(){
        
        $accessList = array();
        return $accessList;
    }
    
    public function create() {}
    public function store(Request $request) { }
    public function show(string $id) { }
    public function edit(string $id) { }
    public function update(Request $request, string $id) { }
    public function destroy(string $id) { }
}
