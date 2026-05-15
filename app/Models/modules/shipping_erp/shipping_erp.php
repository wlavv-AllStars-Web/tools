<?php

namespace App\Models\modules\shipping_erp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\modules\oms\SupplierInvoice;
use App\Services\oms\OmsLegacyProcurementService;

use App\Models\Concerns\BuildsDashboardPanels;
class shipping_erp extends Model{
    
    
    use BuildsDashboardPanels;
use HasFactory;
    protected $table = "shipping_erp";
    public $primaryKey = 'id';
    public $timestamps = false;
    
    public static function saveERPRelation($id_shipping, $erp_data){
        
        $data = array();

        self::where('id_shipping', $id_shipping)->delete();
        
        foreach($erp_data AS $erp){
            
            if(strlen($erp)  > 0){
                $shipping_erp = new shipping_erp();
                $shipping_erp->id_shipping = $id_shipping;
                $shipping_erp->id_erp = $erp;
                $shipping_erp->save();
            }
            
        }

    }
    
    public static function getShipping_ERP($id_shipping, $id_erp){
        return self::where('id_shipping', $id_shipping)->where('id_erp', $id_erp)->count();
    }

    public static function getShippingIdsForErp($id_erp){
        return self::where('id_erp', $id_erp)->pluck('id_shipping');
    }

    public static function replaceErpRelation($id_erp, $id_shipping = null){
        self::where('id_erp', $id_erp)->delete();

        if(!empty($id_shipping)){
            $shipping_erp = new shipping_erp();
            $shipping_erp->id_shipping = $id_shipping;
            $shipping_erp->id_erp = $id_erp;
            $shipping_erp->save();
        }
    }
    
    public static function getOrders($id_shipping){
        return self::where('id_shipping', $id_shipping)->get();
    }
    
    public static function getProductsOfERP($id_shipping){
        
        $products = array();
        
        $shipment = self::where('id_shipping', $id_shipping)->get();
        
        foreach($shipment AS $erp){
            
            $products[] = OmsLegacyProcurementService::linesForOrders([(int) $erp->id_erp]);
            
        }
        
        return $products;

    }
        
    public static function dashboard_invoiced_without_shipment_relation($type){
        
        $data = [];
    
        $linkedInvoiceIds = self::query()
            ->pluck('id_erp')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    
        $invoices = SupplierInvoice::query()
            ->with(['supplier', 'billedOrders'])
            ->when(!empty($linkedInvoiceIds), function ($query) use ($linkedInvoiceIds) {
                $query->whereNotIn('id', $linkedInvoiceIds);
            })
            ->whereHas('billedOrders')
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('id')
            ->get();
    
        foreach ($invoices as $invoice) {
            $data[] = [
                'id'            => $invoice->id,
                'reference'     => $invoice->invoice_reference ?? ('INV-' . $invoice->id),
                'supplier'      => $invoice->supplier->name ?? '-',
                'status'        => strtoupper(str_replace('_', ' ', (string) ($invoice->status ?? 'draft'))),
                'billed_orders' => $invoice->billedOrders->count(),
            ];
        }
    
        return [
            'name'              => 'INVOICES W/O SHIPMENT',
            'col'               => 4,
            'item_id'           => $type . '_invoiced_without_shipment_relation',
            'columns'           => ['id', 'reference', 'supplier'],
            'exception_fields'  => null,
            'link'              => route('admin.tools.oms.invoices.index', ['missing_shipment_relation' => 1]),
            'prestashop'        => null,
            'counter'           => count($data),
            'data'              => $data,
        ];
    }
    
}
