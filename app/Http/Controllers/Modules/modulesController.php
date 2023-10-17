<?php

namespace App\Http\Controllers\Modules;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

class modulesController extends Controller
{    
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('modules'), 'url' => route('modules.index')];
    }

    public function index() {

        $modules = [
            [ 'name' =>  trans('messages.bmsProcurement'), 'url' => route('bmsProcurement.index')]
        ];

        $data = [
            'modules'    => $modules,
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs
        ];

        return View::make('modules/index')->with($data);
    }
    public function create() {}
    public function store(Request $request) { }
    public function show(string $id) { }
    public function edit(string $id) { }
    public function update(Request $request, string $id) { }
    public function destroy(string $id) { }
}
