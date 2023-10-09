<?php

namespace App\Http\Controllers\Prestashop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Models\prestashop\suppliers;
use App\Models\prestashop\suppliers_lang;

class supplierController extends Controller
{

    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('logistics')];
        $this->breadcrumbs[] = [ 'name' =>  trans('Suppliers'), 'url' => route('suppliers.index')];
    }

    public function index()
    {
        $suppliers = suppliers::get();
        
        $this->actions[]     = [ 'name' => 'Add supplier', 'url' => '#', 'class' => "btn btn-success"];

        $data = [
            'suppliers'   => $suppliers,
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs
        ];

        return View::make('prestashop/suppliers/index')->with($data);
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->actions[]     = [ 'name' => 'Edit', 'url' => route('suppliers.edit', $id), 'class' => "btn btn-warning"];
        $this->breadcrumbs[] = [ 'name' => 'Supplier info', 'url' => route('suppliers.show', $id)];

        $supplier = suppliers::with('lang')->where('id_supplier', $id)->first();

        $data = [
            'supplier'   => $supplier,
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs
        ];

        return View::make('prestashop/suppliers/show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
 
        $this->actions[]     = [ 'name' => 'Save', 'url' => "", 'class' => "btn btn-primary", 'onclick' => "$('#suppliersForm').submit()"];
        $this->breadcrumbs[] = [ 'name' => 'Suppliers edit', 'url' => route('suppliers.store')];

        $supplier = suppliers::with('lang')->where('id_supplier', $id)->first();

        $data = [
            'supplier'   => $supplier,
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs
        ];

        return View::make('prestashop/suppliers/edit')->with($data);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        suppliers::where('id_supplier', $id)->update(
            [
                'name'     => $request->get('name'),
                'active'   => $request->get('active'),
                'date_upd'   => date('Y-m-d H:i:s')
            ]
            );
        
        suppliers_lang::where('id_supplier', $id)->where('id_lang', 1)->update(
            [
                'description'      => $request->get('description')[1],
                'meta_title'       => $request->get('meta_title')[1],
                'meta_keywords'    => $request->get('meta_keywords')[1],
                'meta_description' => $request->get('meta_description')[1]
            ]
        );
        
        suppliers_lang::where('id_supplier', $id)->where('id_lang', 4)->update(
            [
                'description'      => $request->get('description')[4],
                'meta_title'       => $request->get('meta_title')[4],
                'meta_keywords'    => $request->get('meta_keywords')[4],
                'meta_description' => $request->get('meta_description')[4]
            ]
        );
        
        suppliers_lang::where('id_supplier', $id)->where('id_lang', 5)->update(
            [
                'description'      => $request->get('description')[5],
                'meta_title'       => $request->get('meta_title')[5],
                'meta_keywords'    => $request->get('meta_keywords')[5],
                'meta_description' => $request->get('meta_description')[5]
            ]
        );

        return redirect(route('suppliers.show', $id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
