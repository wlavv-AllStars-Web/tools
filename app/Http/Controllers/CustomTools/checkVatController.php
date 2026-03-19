<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;

use App\Models\prestashop\address;
use App\Models\modules\checkVat\checkVat;

class CheckVatController extends Controller
{
    public $actions;

    public function index()
    {
        $this->actions[]     = [ 'name' => 'VERIFY', 'icon' => '<i class="f-left fa fa-trash"></i>', 'url' => route('checkVat.verify'), 'class' => "btn btn-warning"];

        $data = [ 
            'actions'  => $this->actions,
            'verified' => checkVat::groupBy('vat_number')->get(),
            'counters' => checkVat::getCounters()
            ];

        return View::make('customTools/checkVat/index')->with($data);
    }

    public function verify(){

        $addresses = address::select('ps_customer.id_customer', 'ps_address.vat_number')
            ->leftJoin('ps_customer', 'ps_address.id_customer', '=', 'ps_customer.id_customer')
            ->where('ps_customer.id_default_group', 4)
            ->where('ps_address.id_customer', '>', 1)
            ->where('ps_address.deleted', 0)
            ->where('ps_address.active', 1)
            ->where('ps_address.id_country', '<>', 249)
            ->orderBy('ps_customer.id_customer')
            ->groupBy('ps_address.id_address')
            ->get();
        
        checkVat::verify($addresses);

        Mail::raw('VAT check execution confirmation', function ($message) {
          $message->to('bruno.fernandes.asm@gmail.com')->subject('VAT check execution confirmation');
        });

        return redirect()->route('checkVat.index');
    }
}
