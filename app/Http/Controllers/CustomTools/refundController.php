<?php

namespace App\Http\Controllers\CustomTools;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\prestashop\customer;
use App\Models\prestashop\product;
use App\Models\prestashop\orders;
use App\Models\prestashop\orders_details;
use App\Models\prestashop\order_payment;
use App\Models\prestashop\order_state_lang;

use App\Models\modules\refund\refund;

class refundController extends Controller
{
    public $actions;
    public $breadcrumbs;

    public function index(Request $request){
        
        $this->breadcrumbs[] = [ 'name' =>  trans('finance'), 'url' => route('finance.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('Refund'), 'url' => route('refund.index')];
        
        $data = [
            'actions'    => [],
            'breadcrumbs'=> $this->breadcrumbs,
            'refunds'    =>  Refund::getRefunds( $request->year, $request->month, $request->method ),
            'archived'    => Refund::getRefunds( $request->year, $request->month, $request->method, 'archived' )
        ];
        
        return View::make('customTools/refund/index')->with($data);
    }

    public function newRefund(Request $request){
        return refund::newRefund($request);
    }

    public function getInfo(Request $request){
        
        $order = orders::where('id_order', $request->id_order)->first();
        $order_details = orders_details::where('id_order', $request->id_order)->pluck('product_reference');
        $order_payment = order_payment::where('order_reference', $order->reference)->value('payment_method');

        $refs = implode(', ',$order_details->toArray());
        $customer = customer::where('id_customer', $order->id_customer)->first();
        
        $data = [
            'name'  => $customer->firstname . ' ' . $customer->lastname,
            'lang'  => $order->id_lang,
            'email' => $customer->email,
            'total' => $order->total_paid,
            'country' => $order->invoice->country->lang_en->name,
            'purchase_date' => Carbon::parse($order->date_add)->format('Y-m-d'),
            'today' => Carbon::parse(date('Y-m-d'))->format('Y-m-d'),
            'payment' => $order_payment,
            'references' => $refs
        ];
        
        return response()->json($data);;
    }

    public function editRefund(Request $request){
        $refund = refund::
        select('*', env('DB2_DB_prefix') . 'orders.date_add AS purchase_date')
        ->leftjoin(env('DB2_DB_prefix') . 'orders', 'refunds.id_order', '=', env('DB2_DB_prefix') . 'orders.id_order')
        ->leftjoin(env('DB2_DB_prefix') . 'customer', env('DB2_DB_prefix') . 'orders.id_customer', '=', env('DB2_DB_prefix') . 'customer.id_customer')
        ->leftjoin(env('DB2_DB_prefix') . 'order_state_lang', env('DB2_DB_prefix') . 'orders.current_state', '=', env('DB2_DB_prefix') . 'order_state_lang.id_order_state')
        ->findOrFail($request->id);
        return view('customTools.refund.includes.edit-form', compact('refund'));
    }

    
    public function updateRefund(Request $data){
        
        $refund = refund::findOrFail($data->id);
        $refund->order_modification = $data->order_modification;
        $refund->return_file_ok = $data->return_file_ok;
        $refund->product_reference = $data->product_reference;
        $refund->refund_reason = $data->refund_reason;
        $refund->refund_payment_method = $data->refund_payment_method;
        $refund->amount_to_refund = $data->amount_to_refund;
        $refund->amount_refunded = $data->amount_refunded;
        $refund->refund_date = $data->refund_date;
        $refund->credit_note = $data->credit_note;
        $refund->refund_status = $data->refund_status;
        $refund->new_order_id = $data->new_order_id;
        $refund->new_order_amount = $data->new_order_amount;
        $refund->eta = $data->eta;
        $refund->update();
    
        return redirect()->route('refund.index')->with('success', 'Refund atualizado com sucesso.');
    }

}