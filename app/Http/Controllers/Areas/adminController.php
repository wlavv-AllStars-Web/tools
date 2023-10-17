<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

class adminController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('administration'), 'url' => route('administration.index')];

    }

    public function index()
    {
        $data = [
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs,
            'accessList' => [
                ['name' =>  trans('administration'), 'url' => route('administration.index')],
            ]
        ];

        return View::make('areas/administration/index')->with($data);
    }

    public function create() {}
    public function store(Request $request) { }
    public function show(string $id) { }
    public function edit(string $id) { }
    public function update(Request $request, string $id) { }
    public function destroy(string $id) { }
}
