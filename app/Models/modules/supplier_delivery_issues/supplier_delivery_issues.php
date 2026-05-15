<?php

namespace App\Models\modules\supplier_delivery_issues;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class supplier_delivery_issues extends Model
{
    use HasFactory;
    protected $table = "supplier_delivery_issues";

    public function supplier(){
        return $this->hasOne(suppliers::class, "id_supplier", 'id_supplier'); 
    }

    public static function getAllActive( ){ 
        
        $supplierTable = env('DB2_DB_prefix', env('DB2_prefix', 'ps_')) . 'supplier';
        $suppliers = supplier_delivery_issues::select('supplier_delivery_issues.id_supplier', 's.name')
            ->join($supplierTable . ' as s', 's.id_supplier', '=', 'supplier_delivery_issues.id_supplier')
            ->groupBy('supplier_delivery_issues.id_supplier', 's.name')
            ->orderBy('s.name', 'ASC')
            ->get();
        
        $suppliers_list = array();
        
        foreach($suppliers AS $supplier){
            $suppliers_list[] = [
                'id_supplier' => $supplier->id_supplier,
                'name' =>$supplier->name,
                'open' => supplier_delivery_issues::where('closed', 0)->where('id_supplier', $supplier->id_supplier)->count(),
                'issues' => supplier_delivery_issues::where('id_supplier', $supplier->id_supplier)->orderBy('invoice_date', 'DESC')->get(),
            ];
        }

        return $suppliers_list;
    }

    public static function saveNewDeliveryIssue( $data ){ 
        
        $issue = new supplier_delivery_issues();
        $issue->id_supplier = $data->id_supplier;
        $issue->po_id = $data->po_id;
        $issue->po_reference = $data->po_reference;
        $issue->reference = $data->reference;
        $issue->qty_ordered = $data->qty_ordered;
        $issue->qty_invoiced = $data->qty_invoived;
        $issue->qty_received = $data->qty_received;
        $issue->invoice_date = $data->invoice_date;
        $issue->comment = $data->comment;
        $issue->created_at = date('Y-m-d');
        $issue->save();
        
        return 1;
    }

    public static function updateDeliveryIssue( $data ){ 
        
        $issue = supplier_delivery_issues::where('id', $data->id)->update(
            [
                'qty_ordered' => $data->qty_ordered, 
                'qty_invoiced' => $data->qty_invoiced, 
                'qty_received' => $data->qty_received, 
                'comment' => $data->comment, 
                'updated_at' => date('Y-m-d')
            ]
        );
        return 1;
    }

    public static function closeDeliveryIssue( $data ){ 
        supplier_delivery_issues::where('id', $data->id)->update( [ 'closed' => 1 ]);
        return 1;
    }

}
