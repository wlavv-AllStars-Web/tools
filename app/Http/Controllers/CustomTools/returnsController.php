<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;



use App\Models\modules\refund\refund;
use App\Models\prestashop\issues;
use App\Models\prestashop\order_history;
use App\Models\prestashop\order_return;
use App\Models\prestashop\order_return_history;
use App\Models\prestashop\order_return_detail;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomTools\mailsController;

class returnsController extends Controller{
    public $actions = [];
    public $breadcrumbs = [];
    
    public function __construct(){
        
        $this->breadcrumbs[] = ['name' => 'sales', 'url' => route('sales.index')];
        $this->breadcrumbs[] = ['name' => 'Returns', 'url' => route('returns.index', ['type' => 1]), 'no_translation' => 1];
        $this->actions[]     = [];
    }
    
    public function index($id = 0){
        
        $breadcrumbs = $this->breadcrumbs;

        if( $id == 1 ){
            $returns = order_return::whereIn('state', [12, 13])->where('process', 'return')->get();
            $list = 1;
        }else{
            $returns = order_return::whereIn('state', [10, 11, 14])->where('process', 'return')->get();
            $list = 0;
        }

        return view('customTools.returns.index', compact('breadcrumbs', 'returns', 'list'));
    }
    
    public function getModal($id){
        
        $detail = order_return_detail::where('id_order_detail', $id)->with('orderDetail')->first();
        $return = order_return::where('id_order_return', $detail->id_order_return)->where('process', 'return')->with('order')->first();
        
        return response()->json([
            'title' => 'Return detail of order with ID: ' . $return->id_order . ' ( ' . $return->order->reference . ' ) ',
            'html' => view('customTools.returns.includes.modal', compact('return', 'detail'))->render(),
        ]);
    }
    
    public static function addReturnHistoryState($id_order_return, $id_order_return_state){
        
        $history = new order_return_history();
        $history->id_order_return = $id_order_return;
        $history->id_order_return_state = $id_order_return_state;
        $history->date_add = date('y-m-d h:i:s');
        $history->save();        
    }
    
    public function changeStatus(Request $request){

        $return = order_return::where('id_order_return', $request->id_order_return)->where('process', 'return')->first();

        $id_associated = $request->returnStatusSelect;

        $data['name'] = $return->customer->firstname . ' ' . $return->customer->lastname;
        
        $id_lang= $return->order->id_lang;
        
        $iso = 'en';
        if($id_lang == 4) $iso = 'es';
        if($id_lang == 5) $iso = 'fr';
        
        switch($id_associated){
            case 10:{
                $template = 'returns_'.$iso.'_5_1';
                $subject[2] = 'Return – request registered';
                $subject[4] = 'Devolución – solicitud registrada';
                $subject[5] = 'Retour - demande enregistrée';
                break;
            }
            case 11:{
                $template = 'returns_'.$iso.'_5_2';
                $subject[2] = 'Return - awaiting delivery';
                $subject[4] = 'Devolución - en espera de entrega';
                $subject[5] = 'Retour - en attente livraison';

                $order_return_detail = order_return_detail::where('id_order_return', $request->id_order_return)->first();
                $shipped = order_history::where('id_order', $return->id_order)->where('id_order_state', 4)->orderBy('id_order_history', 'DESC')->first();

                $reason = '';

                $reference = $order_return_detail->orderDetail->product->reference;
                $auto_code = '000' . substr($reference, 0, 2) . '-' . $return->id_order . 'O';
                
                $shipping_date = date('d/m/Y', strtotime($shipped->date_add));
                
                issues::saveReturn($return->id_order, $reference, $order_return_detail->product_quantity, $shipping_date, $auto_code, $reason);
                break;
            }
            case 12:{
                $template = 'returns_'.$iso.'_5_5';
                $subject[2] = 'Return – not approved';
                $subject[4] = 'Devolución – no aprobada';
                $subject[5] = 'Retour – non aprouve';

                $data['value_to_pay'] = $request->value_to_pay;
                $data['link_for_payment'] = $request->link_for_payment;
                $data['link_to_pictures'] = $request->link_to_pictures;
                break;
            }
            case 13:{
                $template = 'returns_'.$iso.'_5_4';
                $subject[2] = 'Return – approved';
                $subject[4] = 'Devolución – aprobada';
                $subject[5] = 'Retour – aprouvé';

                $order_return_detail = order_return_detail::where('id_order_return', $request->id_order_return)->first();

                $refund['order_id'] = $return->id_order;
                $refund['country'] = $return->order->invoice->country->lang_en->name;
                $refund['lang'] = $return->order->id_lang;
                $refund['refund_reason'] = ' [ ' . $order_return_detail->refund_method . ' ] ';
                $refund['refund_payment_method'] = $order_return_detail->refund_method;
                $refund['order_modification'] = 0;
                $refund['return_file_ok'] = 0;
                $refund['product_reference'] = $order_return_detail->orderDetail->product->reference;
                $refund['refund_status'] = 'Pending';
                $refund['amount_to_refund'] = 0;
                $refund['amount_refunded'] = 0;
                $refund['refund_date'] = null;
                $refund['credit_note'] = null;
                $refund['new_order_id'] = null;
                $refund['new_order_amount'] = null;
                $refund['eta'] = null;

                refund::newRefund((object)$refund);
                break;
            }
            case 14:{
                $template = 'returns_'.$iso.'_5_3';
                $subject[2] = 'Return - package received';
                $subject[4] = 'Devolución - paquete recebido';
                $subject[5] = 'Retour – colis reçu';
                break;
            }
        }

        order_return::where('id_order_return', $request->id_order_return)->where('process', 'return')->update(['state' => $id_associated]);
        self::addReturnHistoryState($request->id_order_return, $id_associated);        
        
        $mail_object = new mailsController();
        
        if( $template != 0){
            $html = $mail_object->createStructure('ASM_white', $template, $subject[$return->order->id_lang], (object)$data, $return->order->id_lang);
            $mail_object->send($return->customer->email, $html, $subject[$return->order->id_lang], 'asm_sales');
        }
        
        return back();
    }
}
