<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
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

use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomTools\mailsController;

use App\Services\Logs\LogService;

class warrantiesController extends Controller{
    public $breadcrumbs = [];
    
    public function __construct(){
        
        $this->breadcrumbs[] = ['name' => 'sales', 'url' => route('sales.index')];
        $this->breadcrumbs[] = ['name' => 'Warranties', 'url' => route('warranties.index'), 'no_translation' => 1];
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

    public function getModal($id)
    {
        $detail = order_return_detail::query()
            ->select('ps_order_return_detail.*')
            ->join('ps_order_return', 'ps_order_return.id_order_return', '=', 'ps_order_return_detail.id_order_return')
            ->where('ps_order_return_detail.id_order_detail', $id)
            ->where('ps_order_return.process', 'warranty') 
            ->with('orderDetail')
            ->firstOrFail();
            
        $new_files = $detail->new_files;
    
        if ((int) $detail->new_files === 1){
            LogService::create( 'RETURN MEDIA MODAL OPEN!', 'RETURN MODULE', 'info', 'RETURN MODAL OF DETAIL #' . $detail->id_order_detail . ' | new files checked!' );
    
            $detail->new_files = 0;
            $detail->save();
        }
    
        $return = order_return::query()
            ->where('id_order_return', $detail->id_order_return)
            ->where('process', 'warranty')
            ->with('order')
            ->firstOrFail();
    
        $images = self::loadWarrantyMedia($return->id_customer, $return->id_order);
    
        return response()->json([
            'title' => 'Warranty detail of order with ID: ' . $return->id_order . ' ( ' . $return->order->reference . ' ) ',
            'html' => view('customTools.warranties.includes.modal', [
                'warranty' => $return,
                'detail' => $detail,
                'images' => $images,
                'new_files' => $new_files,
            ])->render(),
        ]);
    }

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
                $subject[2] = 'Warranty – Request Registered';
                $subject[4] = 'Garantía – solicitud registrada';
                $subject[5] = 'Garantie – demande enregistrée';
                break;
            }
            case 2:{
                $template = 'warranties_'.$iso.'_6_2';
                $subject[2] = 'Warranty – Request Being Processed';
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
                break;
            }
            case 3:{
                $template = 'warranties_'.$iso.'_6_3';

                $order_return_detail = order_return_detail::where('id_order_return', $request->id_order_return)->first();
                $order_return_detail->files_required = $request->supplier_reply;
                $order_return_detail->update();

                $order = orders::where('id_order', $warranty->id_order)->first();
                
                $data['supplier_message'] = $request->supplier_reply;
                $baseUrl = \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM');
                $data['href'] = $baseUrl . "/index.php?controller=returnwarranty&action=view-warranty&id_order=" . $warranty->id_order . "&order_ref=" . $order->reference . "&selection=" . $order_return_detail->id_order_detail . "%3A1&multi=1&validate=false";
                
                $subject[2] = 'Warranty – Request for Additional Information';
                $subject[4] = 'Garantía – Solicitud de información adicional';
                $subject[5] = 'Garantie – demande d’éléments complémentaires';
                break;
            }
            case 4:{
                $template = 'warranties_'.$iso.'_6_4';
                $subject[2] = 'Warranty – Approved';
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
                $subject[2] = 'Warranty – Not Approved';
                $subject[4] = 'Garantía – No aprobada';
                $subject[5] = 'Garantie – non approuvée';
                break;
            }
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
    
            $relativePath = \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') . "/upload/return_warranty/" . $customerId . '_' . $orderId . '/' . $filename;
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
