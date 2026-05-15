<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\modules\dashboard\dashboard;

class dataController extends Controller
{
    public $breadcrumbs = [];
    
    public function __construct(){
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('data'), 'url' => route('data.index')];
    }

    public function index(){
        $data = [
            'counters'      => dashboard::calculateAndGetCountersOfTab('data'),
            'breadcrumbs'=> $this->breadcrumbs,
            'accessList' => []
        ];

        return View::make('areas/data/index')->with($data);
    }
}
