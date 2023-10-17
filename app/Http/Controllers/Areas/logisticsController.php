<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

class logisticsController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('logistics.index')];

    }

    public function index()
    {
        $data = [
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs,
            'accessList' => [
                ['name' =>  trans('Logistics'), 'url' => route('logistics.index')],
            ]
        ];

        return View::make('customTools/logistics/index')->with($data);
    }

    public function create(){ }
    public function store(Request $request){ }
    public function show(string $id){}
    public function edit(string $id){}
    public function update(Request $request, string $id){}
    public function destroy(string $id){}
}
