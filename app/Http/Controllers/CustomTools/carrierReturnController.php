<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;

use App\Models\modules\carrierReturn\carrierReturn;
use App\Models\prestashop\order_carrier;

class carrierReturnController extends Controller
{

    public function index() {

        $this->breadcrumbs[] = [ 'name' =>  trans('finance'), 'url' => route('finance.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('carrierReturns'), 'url' => route('carrierReturn.index')];

        $data = [
            'breadcrumbs'   => $this->breadcrumbs,
            'carrierReturnArchived' => carrierReturn::getcarrierReturn(0),
            'carrierReturnActive' => carrierReturn::getcarrierReturn(1)
        ];
        
        return View::make('customTools/carrierReturns/index')->with($data);
    }
    
    public function store(Request $request) {
        carrierReturn::saveData($request);
        return redirect()->route('carrierReturn.index');
    }

    public function archive(Request $request) {
        return carrierReturn::archive($request->id);
    }

    public function update(Request $request) {
        carrierReturn::updateData($request);
        return redirect()->route('carrierReturn.index');
    }
}