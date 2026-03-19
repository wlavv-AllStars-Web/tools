<?php

namespace App\Http\Controllers\Prestashop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Http\Controllers\CustomTools\mailsController;

use App\Models\prestashop\orders;
use App\Models\prestashop\customer;
use App\Models\prestashop\asm_dashboard;

class ordersController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) { }
    public function show(string $id) { }
    public function edit(string $id) { }
    public function update(Request $request, string $id) { }
    public function destroy(string $id) { }
    
    public function sendReviewedEmail(Request $request) { 

        $order = orders::where('id_order', $request->id)->first();
        
        $customer = customer::where('id_customer', $order->id_customer)->first();

        $data = [
            'firstname' => $customer->firstname,
            'lastname'  => $customer->lastname,
            'id_lang'   => $order->id_lang
        ];
        
        $template = 'EN_email_reviewed';
        
        if($order->id_lang == 4) $template = 'ES_email_reviewed';
        if($order->id_lang == 5) $template = 'FR_email_reviewed';
        
        $email = NEW mailsController();
        $html = $email->createStructure('none', $template, trans('mails.Order review'), $data, $order->id_lang);
        $email->send($customer->email, $html, trans('mails.Order review'));

        $addException = (object)[ 
            'panel' => 'order_reviewed', 
            'var_1' => $order->id_order, 
            'var_2' => $order->date_add, 
            'var_3' => $customer->email
        ];

        asm_dashboard::addException( $addException );

        return response()->json([ 'result' => 'success' ]);
    }

    
}