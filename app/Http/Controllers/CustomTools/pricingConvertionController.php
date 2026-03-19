<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;

use App\Models\prestashop\product;

class pricingConvertionController extends Controller
{
    public $actions;

    public function index()
    {
        //$this->actions[]     = [ 'name' => 'VERIFY', 'icon' => '<i class="f-left fa fa-trash"></i>', 'url' => route('checkVat.verify'), 'class' => "btn btn-warning"];
        
        dd('pricing');
        $data = [ 
            'actions'  => $this->actions
            ];

        return View::make('customTools/pricing/index')->with($data);
    }
}
