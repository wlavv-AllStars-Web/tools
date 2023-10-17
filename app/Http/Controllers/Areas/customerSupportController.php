<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

class customerSupportController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('customer'), 'url' => route('customer.index')];

    }

    public function index()
    {
        $data = [
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs,
            'accessList' => [
                ['name' =>  trans('messages.customers list'),  'url' => route('customer.index'),  'icon' => '<i style="font-size: 40px;" class="fa-solid fa-users"></i>'],
                ['name' =>  trans('messages.products'),  'url' => route('products.index'),  'icon' => '<i style="font-size: 40px;" class="fa-solid fa-boxes-stacked"></i>'],
                ['name' =>  trans('messages.orders'),    'url' => route('orders.index'),    'icon' => '<i style="font-size: 40px;" class="fa-solid fa-truck-fast"></i>'],
                ['name' =>  trans('messages.suppliers'), 'url' => route('suppliers.index'), 'icon' => '<i style="font-size: 40px;" class="fa-solid fa-cart-flatbed"></i>'],
                ['name' =>  trans('messages.addresses'), 'url' => route('addresses.index'), 'icon' => '<i style="font-size: 40px;" class="fa-regular fa-map"></i>'],
            ]
        ];

        return View::make('areas/customer/index')->with($data);
    }

    public function create() {}
    public function store(Request $request) { }
    public function show(string $id) { }
    public function edit(string $id) { }
    public function update(Request $request, string $id) { }
    public function destroy(string $id) { }
}
