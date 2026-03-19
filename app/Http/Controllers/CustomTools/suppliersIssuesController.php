<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

use App\Models\prestashop\suppliers;
use App\Models\modules\supplier_issues\supplier_issues;
use App\Models\modules\supplier_delivery_issues\supplier_delivery_issues;
use App\Models\modules\supplier_warranty_issues\supplier_warranty_issues;


use App\Models\modules\bms_procurement\bms_procurement_purchase_order;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order_product;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order_reception_product;
use App\Models\modules\bms_procurement\bms_procurement_purchase_order_reception;

class suppliersIssuesController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function index($type){

        $deliveryIssues = supplier_delivery_issues::getAllActive();
        $supplierIssues = supplier_issues::getAllActive();
        $warrantyIssues = supplier_warranty_issues::getAllActive();
        
        if($type == 1){

            $data = [
                'type' => 1,
                'actions'    => [],
                'breadcrumbs'=> $this->breadcrumbs,
                'deliveryIssues' => $deliveryIssues,
                'warrantyIssues' => $warrantyIssues,
                'supplierIssues' => $supplierIssues,
                'suppliers'  => suppliers::orderBy('name', 'ASC')->pluck('name', 'id_supplier')
            ];
        
        }else{

            $data = [
                'type'           => 2,
                'actions'        => [],
                'breadcrumbs'    => $this->breadcrumbs,
                'deliveryIssues' => $deliveryIssues,
                'warrantyIssues' => $warrantyIssues,
                'supplierIssues' => $supplierIssues,
                'suppliers'      => suppliers::orderBy('name', 'ASC')->pluck('name', 'id_supplier')
            ];            
        }
        
        /**
        $data = [
            'actions'        => [],
            'breadcrumbs'    => $this->breadcrumbs,
            'deliveryIssues' => $deliveryIssues,
            'warrantyIssues' => $warrantyIssues,
            'supplierIssues' => $supplierIssues,
            'suppliers'      => suppliers::orderBy('name', 'ASC')->pluck('name', 'id_supplier')
        ];**/
        
        return View::make('customTools/suppliers/issues/index')->with($data);
    }
    
    /** DELIVERY ISSUE **/
    public function newDeliveryIssue(Request $request){
        supplier_delivery_issues::saveNewDeliveryIssue($request);
        return redirect()->route('suppliersIssues.index', ['type' => 2]);
    }
    
    public function updateDeliveryIssue(Request $request){
        return supplier_delivery_issues::updateDeliveryIssue($request);
    }
    
    public function closeDeliveryIssue(Request $request){
        return supplier_delivery_issues::closeDeliveryIssue($request);
    }
    /** DELIVERY ISSUE **/
    
    /** SUPPLIER ISSUE **/
    public function newSupplierIssue(Request $request){
        supplier_issues::saveNewIssue($request);
        return redirect()->route('suppliersIssues.index', ['type' => 1]);
    }
    
    public function updateSupplierIssue(Request $request){
        return supplier_issues::updateIssue($request);
    }
    /** SUPPLIER ISSUE **/
    
    
    
    /** WARRANTY ISSUE **/
    public function newWarrantyIssue(Request $request){
        supplier_warranty_issues::saveNewIssue($request);
        return redirect()->route('suppliersIssues.index', ['type' => 1]);
    }
    
    public function updateWarrantyIssue(Request $request){
        return supplier_warranty_issues::updateIssue($request);
    }
    
    public function closeWarrantyIssue(Request $request){
        return supplier_warranty_issues::closeWarrantyIssue($request);
    }
    /** WARRANTY ISSUE **/
    
    
    
}