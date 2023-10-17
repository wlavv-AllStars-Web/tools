<?php

namespace App\Http\Controllers\Prestashop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Models\prestashop\manufacturers;
use App\Models\prestashop\manufacturers_lang;
use App\Models\prestashop\country;
use App\Models\prestashop\language;
use App\Models\prestashop\currency;

class manufacturersController extends Controller
{

    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('messages.Manufacturers'), 'url' => route('manufacturers.index')];
    }

    public function index()
    {
        $manufacturers = manufacturers::get();
        
        $this->actions[]     = [ 'name' => trans('messages.Add manufacturer'), 'icon' => '<i class="fa fa-add"></i>', 'url' => '#', 'class' => "btn btn-success"];

        $data = [
            'manufacturers'   => $manufacturers,
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs
        ];

        return View::make('prestashop/manufacturers/index')->with($data);
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
        $this->actions[]     = [ 'name' => 'Edit', 'icon' => '<i class="fa fa-edit"></i>', 'url' => route('manufacturers.edit', $id), 'class' => "btn btn-warning"];
        $this->breadcrumbs[] = [ 'name' => 'Manufacturers info', 'url' => route('manufacturers.show', $id)];

        $manufacturer = manufacturers::with('lang')->where('id_manufacturer', $id)->first();

        $data = [
            'manufacturer'=> $manufacturer,
            'actions'     => $this->actions,
            'breadcrumbs' => $this->breadcrumbs
        ];

        return View::make('prestashop/manufacturers/show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
 
        $this->actions[]     = [ 'name' => 'Save', 'icon' => '<i class="fa fa-save"></i>', 'url' => "", 'class' => "btn btn-primary", 'onclick' => "$('#manufacturersForm').submit()"];
        $this->breadcrumbs[] = [ 'name' => 'Manufacturers edit', 'url' => route('manufacturers.store')];

        $manufacturer = manufacturers::with('lang')->where('id_manufacturer', $id)->first();
        $countries = country::get();
        $language = language::get();
        $currencies = currency::get();
        
        $data = [
            'manufacturer' => $manufacturer,
            'countries'    => $countries,
            'language'     => $language,
            'currencies'   => $currencies,
            'actions'      => $this->actions,
            'breadcrumbs'  => $this->breadcrumbs
        ];

        return View::make('prestashop/manufacturers/edit')->with($data);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        manufacturers::where('id_manufacturer', $id)->update(
            [
                'name'          => $request->get('name'),
                'active'        => $request->get('active'),
                'warranty'      => $request->get('warranty'),
                'country_code'  => $request->get('country_code'),
                'currency'      => $request->get('currency'),
                'date_upd'      => date('Y-m-d H:i:s')
            ]
        );
        
        $languages = language::get();

        foreach( $languages AS $language){

            manufacturers_lang::where('id_manufacturer', $id)->where('id_lang', $language->id_lang)->update(
                [
                    'description'      => $request->get('description')[$language->id_lang],
                    'short_description'=> $request->get('short_description')[$language->id_lang],
                    'meta_title'       => $request->get('meta_title')[$language->id_lang],
                    'meta_keywords'    => $request->get('meta_keywords')[$language->id_lang],
                    'meta_description' => $request->get('meta_description')[$language->id_lang]
                ]
            );

        }

        return redirect(route('manufacturers.show', $id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
