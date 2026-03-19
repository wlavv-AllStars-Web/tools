<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\prestashop\issues;

use App\Models\prestashop\cart;
use App\Models\prestashop\cart_product;

use App\Models\prestashop\orders;
use App\Models\prestashop\orders_details;
use App\Models\prestashop\order_carrier;
use App\Models\prestashop\order_history;

use App\Models\prestashop\order_return;
use App\Models\prestashop\order_return_history;
use App\Models\prestashop\order_return_detail;

use App\Models\modules\refund\refund;
use App\Models\modules\productIssues\productIssues;
use App\Models\modules\supplier_issues\supplier_issues;

use App\Models\prestashop\ukoocompat_compat;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomTools\mailsController;

use App\Services\Logs\LogService;

class warrantiesController extends Controller{
    
    public function __construct(){
        
        $this->breadcrumbs[] = [ 'name' =>  trans('sales'),  'url' => route('sales.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('warranties'), 'url' => route('warranties.index')];
    }
    
    public function index($id = 0){
        
        $breadcrumbs = $this->breadcrumbs;

        if( $id == 1 ){
            $warranties = order_return::whereIn('state', [4, 5])->where('process', 'warranty')->orderBy('id_order_return', 'ASC')->get();
            $list = 1;
        }else{
            $warranties = order_return::whereIn('state', [1, 2, 3])->where('process', 'warranty')->orderBy('id_order_return', 'ASC')->get();
            $list = 0;
        }
        
        return view('customTools.warranties.index', compact('breadcrumbs', 'warranties', 'list'));
    }

    public function getModal($id){
        
        $detail = order_return_detail::where('id_order_detail', $id)->with('orderDetail')->first();
        
        $new_files = $detail->new_files;
        
        if( $detail->new_files == 1){
            LogService::create( 'WARRANTY MEDIA MODAL OPEN!',  'WARRANTY MODULE',  'info', 'WARRANTY MODAL OF DETAIL #' . $detail->id_order_detail . ' | new files checked!' );
    
            $detail->new_files = 0;
            $detail->update(); 
        } 
        
        $warranty = order_return::where('id_order_return', $detail->id_order_return)->where('process', 'warranty')->with('order')->first();
        
        $images = self::loadWarrantyMedia($warranty->id_customer, $warranty->id_order);
        
        return response()->json([
            'title' => 'Return detail of order with ID: ' . $warranty->id_order . ' ( ' . $warranty->order->reference . ' ) ',
            'html' => view('customTools.warranties.includes.modal', compact('warranty', 'detail', 'images', 'new_files'))->render(),
        ]);
    }
    
    /** xatamente igual a return **/
    public static function addWarrantyHistoryState($id_order_return, $id_order_return_state){
        $history = new order_return_history();
        $history->id_order_return = $id_order_return;
        $history->id_order_return_state = $id_order_return_state;
        $history->date_add = date('y-m-d h:i:s');
        $history->save();     
    }
    
    public static function addWarrantyToIssues($id_order_return, $newOrderID = 0){

        $warranty = order_return::where('id_order_return', $id_order_return)->where('process', 'warranty')->with('order')->first();
        $order_return_detail = order_return_detail::where('id_order_return', $id_order_return)->first();
        $refund['order_id'] = $warranty->id_order;
        $refund['country'] = $warranty->order->invoice->country->lang_en->name;
        $refund['lang'] = $warranty->order->id_lang;
        $refund['refund_reason'] = 'REFUND OF WARRANTY ISSUED ABOUT ORDER NUMBER: ' . $warranty->id_order;
        $refund['refund_payment_method'] = $warranty->order->payment;
        $refund['order_modification'] = 0;
        $refund['return_file_ok'] = 0;
        $refund['product_reference'] = $order_return_detail->orderDetail->product->reference;
        $refund['refund_status'] = 'Pending';
        $refund['amount_to_refund'] = 0;
        $refund['amount_refunded'] = 0;
        $refund['refund_date'] = null;
        $refund['credit_note'] = 0;
        $refund['new_order_id'] = $newOrderID;
        $refund['new_order_amount'] = 0;
        $refund['eta'] = null;
        refund::newRefund((object)$refund);
    }
    
    /** APPARENTEMENTE É PARA REMOVER O ESTADO **/
    /**
    public static function getCompatibilities($id_order_return){
        
        $detail = order_return_detail::where('id_order_return', $id_order_return)->first();
        $compats = ukoocompat_compat::where('id_product', $detail->id_product)->get();
        
        if( count($compats) == 0) return 'Product is universal! ';
        
        $html = '<table border="0" cellpadding="3" cellspacing="3" style="width: 100%; margon: 10px;text-align: center; border-collapse:collapse; background-color:#ffffff; font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#333;">';
            $html .= '<thead>';
                $html .= '<tr>';
                    $html .= '<td>BRAND</td>';
                    $html .= '<td>MODEL</td>';
                    $html .= '<td>TYPE</td>';
                    $html .= '<td>VERSION</td>';
                $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';

            foreach($compats AS $compat){
                if( isset( $compat->compat_criterion_brand->criterion_lang ) ){
                $html .= '<tr>';
                    $html .= '<td>' . $compat->compat_criterion_brand->criterion_lang->value . '</td>';
                    $html .= '<td>' . $compat->compat_criterion_model->criterion_lang->value . '</td>';
                    $html .= '<td>' . $compat->compat_criterion_type->criterion_lang->value . '</td>';
                    $html .= '<td>' . $compat->compat_criterion_version->criterion_lang->value . '</td>';
                $html .= '</tr>';
                }
            }
            
            $html .= '<tbody>';
        return $html .= '<table>';
    }
    **/
    
    /** APPARENTEMENTE ESTE PROCESSO É PARA SER MANUAL **/
    /**
    public static function sendEmailToSupplier($id_order_return){
        
        $detail = order_return_detail::where('id_order_return', $id_order_return)->first();
        $warranty = order_return::where('id_order_return', $id_order_return)->first();

        $issue_photos = self::loadWarrantyMedia($warranty->id_customer, $warranty->id_order);
        
        $subject = 'Warranty verification request – [ ' . $detail->orderDetail->product_reference . ' ]';
        
        $address = $warranty->order->invoice->address1;
        if( strlen($warranty->order->invoice->address2) > 0) $address .= '<br>' . $warranty->order->invoice->address2;
        $address .= '<br>' . $warranty->order->invoice->postcode . ' - ' . $warranty->order->invoice->city;
        $address .= '<br>' . $warranty->order->invoice->country->lang_en->name;
        
        $data = [
            'subject' => $subject,
            'product_reference' => $detail->orderDetail->product_reference,
            'product_name' => $detail->orderDetail->product_name,
            'car_brand' => $detail->brand,
            'car_model' => $detail->model,
            'car_vin' => $detail->chassis,
            'customer_firstname' => $warranty->customer->firstname,
            'customer_lastname' => $warranty->customer->lastname,
            'customer_email' => $warranty->customer->email,
            'customer_address' => $address,
            'issue_description' => $detail->problem_description,
            'issue_photos' => $issue_photos->problem
        ];

        $mail_object = new mailsController();
        
        $email = $warranty->customer->email;
        $email = 'bruno.fernandes.asm@gmail.com';
        
        $html = $mail_object->createStructure('ASM', 'warranty_suppliers_en', $subject, $data, $warranty->order->id_lang);
        $mail_object->send($email, $html, $subject);
        
    }
    **/
    
    public function changeStatus(Request $request)
    {
        $warranty = order_return::where('id_order_return', $request->id_order_return)->where('process', 'warranty')->first();
        
        $id_associated = $request->warrantyStatusSelect;

        $data['name'] = $warranty->customer->firstname . ' ' . $warranty->customer->lastname;
        $data['compatibilities'] = $request->compatibilities;

        $id_lang= $warranty->order->id_lang;
        
        $iso = 'en';
        if($id_lang == 4) $iso = 'es';
        if($id_lang == 5) $iso = 'fr';

        switch($id_associated){
            case 1:{
                $template = 'warranties_'.$iso.'_6_1';
                $subject[1] = 'Warranty – Request Registered';
                $subject[4] = 'Garantía – solicitud registrada';
                $subject[5] = 'Garantie – demande enregistrée';
                break;
            }
            case 2:{
                $template = 'warranties_'.$iso.'_6_2';
                $subject[1] = 'Warranty – Request Being Processed';
                $subject[4] = 'Garantía – En proceso de tramitación';
                $subject[5] = 'Garantie – en cours de traitement';

                $order_return_detail = order_return_detail::where('id_order_return', $request->id_order_return)->first();

                $data_product = array();
                $data_product['reference'] =  $order_return_detail->orderDetail->product_reference;
                $data_product['description'] = $order_return_detail->problem_description;
                $data_product['car'] = $order_return_detail->brand . ' ' . $order_return_detail->model . ' ( ' . $order_return_detail->chassis . ' ) ';
                $data_product['assembly'] = ( $order_return_detail->procedure_id == 1 ) ? 1 : 0;
                $data_product['compatibility'] = ( $order_return_detail->procedure_id == 2 ) ? 1 : 0;
                $data_product['defect'] = ( $order_return_detail->procedure_id == 3 ) ? 1 : 0;
                $data_product['shop'] = 'ASM';
                $data_product['status'] = 'PENDING';
                $data_product['conclusion'] = '';
                $data_product['date'] = date('Y-m-d');
                $data_product['id_order'] = $warranty->id_order;
                productIssues::saveData($data_product);
                    
                /** CHANGED TO BE MANUAL, AS SO NEXT ROW IS COMMENTED **/
                /**self::sendEmailToSupplier($request->id_order_return);**/

                break;
            }
            case 3:{
                $template = 'warranties_'.$iso.'_6_3';

                $order_return_detail = order_return_detail::where('id_order_return', $request->id_order_return)->first();
                $order_return_detail->files_required = $request->supplier_reply;
                $order_return_detail->update();

                $order = orders::where('id_order', $warranty->id_order)->first();
                
                $data['supplier_message'] = $request->supplier_reply;
                $data['href'] = "https://www.all-stars-motorsport.com/index.php?controller=returnwarranty&action=view-warranty&id_order=" . $warranty->id_order . "&order_ref=" . $order->reference . "&selection=" . $order_return_detail->id_order_detail . "%3A1&multi=1&validate=false";
                
                $subject[1] = 'Warranty – Request for Additional Information';
                $subject[4] = 'Garantía – Solicitud de información adicional';
                $subject[5] = 'Garantie – demande d’éléments complémentaires';
                break;
            }
            case 4:{
                $template = 'warranties_'.$iso.'_6_4';
                $subject[1] = 'Warranty – Approved';
                $subject[4] = 'Garantía – Aprobada';
                $subject[5] = 'Garantie – approuvée';

                $newOrderID = 0;
                if( ( isset($request->fromOurStock) ) && ( $request->fromOurStock == 1 ) ){
                    
                    $order_return_detail = order_return_detail::where('id_order_return', $request->id_order_return)->first();
                    $shipped = order_history::where('id_order', $warranty->id_order)->where('id_order_state', 4)->orderBy('id_order_history', 'DESC')->first();
                    
                    $reference = $order_return_detail->orderDetail->product->reference;
                    
                    $newOrderID = self::createGuaranteeOrder($warranty->id_order, $order_return_detail->id_order_detail, $order_return_detail->id_order_return);   
                    
                    $data_reference = $order_return_detail->product_quantity . ' * ' . $reference;
                    
                    issues::saveWarranty($warranty->id_order, $data_reference, $order_return_detail->problem_description);

                    $data_issue = array();
                    $data_issue['id_supplier'] = $order_return_detail->orderDetail->product->id_supplier;
                    $data_issue['reference'] = $order_return_detail->orderDetail->product->reference;
                    $data_issue['quantity'] = $order_return_detail->product_quantity;
                    $data_issue['date'] = date('Y-m-d');
                    $data_issue['alert_by'] = 'AUTO WARRANTY';
                    $data_issue['description'] = $order_return_detail->problem_description;
                    $data_issue['info'] = '';
                    $data_issue['status'] = 'IN PROGRESS';
                    
                    supplier_issues::saveNewIssue( (object)$data_issue );
                }

                /**
                if( ( isset($request->addToRefundTable) ) && ( $request->addToRefundTable == 1 ) ) self::addWarrantyToIssues($request->id_order_return, $newOrderID);
                **/
                
                break;
            }
            case 5:{
                
                $data['response_manufacturer'] = $request->response_manufacturer;

                $template = 'warranties_'.$iso.'_6_5';
                $subject[1] = 'Warranty – Not Approved';
                $subject[4] = 'Garantía – No aprobada';
                $subject[5] = 'Garantie – non approuvée';
                break;
            }
            
            /** APPARENTEMENTE É PARA REMOVER O ESTADO **/
            /**
            case 6:{
                $template = 'warranties_'.$iso.'_6_9';
                $subject[1] = 'WARRANTY - COMPATIBILITY CONFIRMATION';
                $subject[4] = 'WARRANTY - COMPATIBILITY CONFIRMATION ES';
                $subject[5] = 'WARRANTY - COMPATIBILITY CONFIRMATION FR';

                $data['compatibilities'] = self::getCompatibilities($request->id_order_return);
                break;
            }
            **/
            /**
            case 7:{
                $template = 'warranties_'.$iso.'_6_10';
                $subject[1] = 'WARRANTY - INSTALLATION AND COMPATIBILITY ISSUE';
                $subject[4] = 'WARRANTY - INSTALLATION AND COMPATIBILITY ISSUE ES';
                $subject[5] = 'WARRANTY - INSTALLATION AND COMPATIBILITY ISSUE FR';
                break;
            }
            case 8:{
                $template = 'warranties_'.$iso.'_6_11';
                $subject[1] = 'WARRANTY - SUPPLIERS NOTICE';
                $subject[4] = 'WARRANTY - SUPPLIERS NOTICE ES';
                $subject[5] = 'WARRANTY - SUPPLIERS NOTICE FR';

                $data['supplier_message'] = $request->supplier_reply;

                break;
            }
            
            default :{
                $template = 'warranties_'.$iso.'_6_1';
                $subject[1] = 'NEW WARRANTY';
                $subject[4] = 'NEW WARRANTY ES';
                $subject[5] = 'NEW WARRANTY FR';
                break;
            }
            **/
        }

        order_return::where('id_order_return', $request->id_order_return)->update(['state' => $id_associated]);
        self::addWarrantyHistoryState($request->id_order_return, $id_associated);        
        
        $mail_object = new mailsController();
        
        
        $html = $mail_object->createStructure('ASM_white', $template, $subject[$warranty->order->id_lang], (object)$data, $warranty->order->id_lang);
        $mail_object->send($warranty->customer->email, $html, $subject[$warranty->order->id_lang]);
        
        return back();
        
    }
    
    public static function loadWarrantyMedia($customerId, $orderId){
        
        $basePath = "/home/allstar1/public_html/upload/return_warranty/{$customerId}_{$orderId}";
    
        $groups = [
            'packing' => [],
            'product' => [],
            'problem' => [],
            'additional' => [],
        ];
    
        if (!is_dir($basePath)) {
            return $groups;
        }
    
        $files = glob($basePath . '/*.{jpg,jpeg,png,webp,gif,mov,webm,mp4}', GLOB_BRACE);
        
        foreach ($files as $file) {
    
            $filename = strtolower(basename($file)); // nome do ficheiro
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
            $group = null;
    
            if (str_contains($filename, 'packing')) {
                $group = 'packing';
            } elseif (str_contains($filename, 'product')) {
                $group = 'product';
            } elseif (str_contains($filename, 'problem')) {
                $group = 'problem';
            } elseif (str_contains($filename, 'additional')) {
                $group = 'additional';
            }
    
            if (!$group) {
                continue;
            }
    
            $relativePath = "https://www.all-stars-motorsport.com/upload/return_warranty/" . $customerId . '_' . $orderId . '/' . $filename;
            $groups[$group][] = (object)[ 'url'  => $relativePath ];
        }
    
        return (object)$groups;
    }

    
    function createGuaranteeOrder(int $originalOrderId, int $orderDetailId, int $orderReturnDetailId){
        
        return DB::transaction(function () use ($originalOrderId, $orderDetailId, $orderReturnDetailId) {
    
            $now  = Carbon::now()->toDateTimeString();
            $orig = orders::where('id_order', $originalOrderId)->first();
            $od   = orders_details::where('id_order_detail', $orderDetailId)->first();
            $ret  = order_return_detail::where('id_order_return', $orderReturnDetailId)->first();

            if (! $orig) throw new \Exception("Encomenda original não encontrada.");
            if (! $od) throw new \Exception("order_detail não encontrado.");
            if ((int)$od->id_order !== (int)$originalOrderId) throw new \Exception("O order_detail não pertence à encomenda original.");
            if (! $ret) throw new \Exception("order_return_detail não encontrado.");

            $qty = max(1, (int)$ret->product_quantity);

            $newCartId = cart::insertGetId([
                'id_customer' => $orig->id_customer,
                'id_address_delivery' => $orig->id_address_delivery,
                'id_address_invoice' => $orig->id_address_invoice,
                'id_currency' => $orig->id_currency,
                'id_lang' => $orig->id_lang,
                'date_add' => $now,
                'date_upd' => $now,
            ]);

            cart_product::insert([
                'id_cart' => $newCartId,
                'id_product' => $od->product_id,
                'id_product_attribute' => $od->product_attribute_id,
                'quantity' => $qty,
                'date_add' => $now,
            ]);

            $reference = strtoupper(substr(sha1(uniqid()), 0, 9));
    
            $newOrderId = orders::insertGetId([
                'id_cart' => $newCartId,
                'id_customer' => $orig->id_customer,
                'id_address_delivery' => $orig->id_address_delivery,
                'id_address_invoice' => $orig->id_address_invoice,
                'id_currency' => $orig->id_currency,
                'id_lang' => $orig->id_lang,
    
                'id_carrier' => $orig->id_carrier,
                'current_state' => 29,
    
                'module' => 'bankwire',
                'payment' => 'bankwire',
    
                'total_paid' => 0,
                'total_paid_real' => 0,
                'total_paid_tax_incl' => 0,
                'total_paid_tax_excl' => 0,
                'total_products' => 0,
                'total_products_wt' => 0,
                'total_shipping' => 0,
                'total_shipping_tax_incl' => 0,
                'total_shipping_tax_excl' => 0,
    
                'secure_key' => $orig->secure_key,
                'reference' => $reference,
    
                'date_add' => $now,
                'date_upd' => $now,
                'valid' => 1,
            ]);

            orders_details::insert([
                'id_order' => $newOrderId,
                'product_id' => $od->product_id,
                'product_attribute_id' => $od->product_attribute_id,
                'product_quantity' => $qty,
                'product_name' => $od->product_name,
                'product_reference' => $od->product_reference,
                'product_price' => 0,
                'unit_price_tax_incl' => 0,
                'unit_price_tax_excl' => 0,
                'total_price_tax_incl' => 0,
                'total_price_tax_excl' => 0,
                'reduction_percent' => 0,
                'reduction_amount' => 0
            ]);
    
            order_carrier::insert([
                'id_order' => $newOrderId,
                'id_carrier' => $orig->id_carrier,
                'shipping_cost_tax_excl' => 0,
                'shipping_cost_tax_incl' => 0,
                'weight' => $orig->weight ?? 0,
                'date_add' => $now,
            ]);
    
            order_history::insert([
                'id_order' => $newOrderId,
                'id_order_state' => 29,
                'id_employee' => 43,
                'date_add' => $now,
            ]);
    
            return $newOrderId;
        });
    }
}