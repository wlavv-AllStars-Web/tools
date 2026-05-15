<?php

namespace App\Models\modules\supplier_issues;

use Auth;
use App\Models\prestashop\suppliers;
use App\Models\modules\auto_orders\AutoOrdersPurchaseList;

use App\Models\modules\supplier_warranty_issues\supplier_warranty_issues;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Concerns\BuildsDashboardPanels;
class supplier_issues extends Model
{
    
    use BuildsDashboardPanels;
use HasFactory;
    protected $table = "supplier_issues";

    public function supplier(){
        return $this->hasOne(suppliers::class, "id_supplier", 'id_supplier'); 
    }

    public static function getAllActive( ){ 
        
        $supplierTable = env('DB2_DB_prefix', env('DB2_prefix', 'ps_')) . 'supplier';
        $suppliers = supplier_issues::select('supplier_issues.id_supplier', 's.name')
            ->join($supplierTable . ' as s', 's.id_supplier', '=', 'supplier_issues.id_supplier')
            ->groupBy('supplier_issues.id_supplier', 's.name')
            ->orderBy('s.name', 'ASC')
            ->get();
        
        $suppliers_list = array();
        
        foreach($suppliers AS $supplier){
            $suppliers_list[] = [
                'id_supplier' => $supplier->id_supplier,
                'name' =>$supplier->name,
                'issues' => supplier_issues::where('id_supplier', $supplier->id_supplier)->orderBy('date', 'DESC')->get(),
            ];
        }

        return $suppliers_list;
    }

    public static function saveNewIssue( $data ){ 

        $issue = new supplier_issues();
        $issue->id_supplier = $data->id_supplier;
        $issue->reference = $data->reference;
        $issue->quantity = $data->quantity;
        $issue->date = $data->date;
        $issue->alert_by = $data->alert_by;
        $issue->description = $data->description;
        $issue->info = $data->info;
        $issue->status = $data->status;
        $issue->save();
        
        return 1;
    }

    public static function updateIssue( $data ){ 
        
        $issue = supplier_issues::where('id', $data->id)->first();
        $issue->description = $data->description;
        $issue->info = $data->info;
        $issue->status = $data->status;
        $issue->save();
        
        return 1;
    }

    public static function movedFromWarrantyIssue( $id ){ 
        
        $data_issue = supplier_warranty_issues::where('id', $id)->first();

        AutoOrdersPurchaseList::insertFromWarranty($id);

        $issue = new supplier_issues();
        $issue->id_supplier = $data_issue->id_supplier;
        $issue->reference = $data_issue->reference;
        $issue->quantity = 1;
        $issue->date = $data_issue->date;
        $issue->alert_by = Auth::user()->name;
        $issue->description = $data_issue->description;
        $issue->info = '';
        $issue->status = 'FROM WARRANTY';
        $issue->save();
        
        return 1;
    }

    public static function dashboard_warranty_issues($type){

        $data = array();
        $bd_data = self::where('status', '<>', 'CLOSED ISSUE')->get();

        foreach($bd_data AS $item) $data[] = ['date' => $item->date, 'reference' => $item->reference, 'info' => $item->info, 'quantity' => $item->quantity];
        
        return [
            'name'          => trans('dashboard.WARRANTIES'),
            'col'           => 4,
            'item_id'       => $type . '_warranties',
            'prestashop'    => null,
            'columns'       => ['date', 'reference', 'info', 'quantity'],
            'counter'       => count($data),
            'data'          => $data
        ];        
    }
    
}

