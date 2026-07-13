<?php

namespace App\Models\modules\supplier_warranty_issues;

use App\Models\prestashop\suppliers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\modules\supplier_issues\supplier_issues;


use App\Models\Concerns\BuildsDashboardPanels;
class supplier_warranty_issues extends Model
{
    
    use BuildsDashboardPanels;
use HasFactory;
    protected $table = "supplier_warranty_issues";

    public function supplier(){
        return $this->hasOne(suppliers::class, "id_supplier", 'id_supplier'); 
    }

    private static function prestashopTable(string $table): string
    {
        $prefix = (string) (env('DB2_DB_prefix') ?: env('DB2_prefix') ?: 'ps_');
        $prefix = str_contains($prefix, '.') ? $prefix : config('database.connections.mysql2.database') . '.' . $prefix;

        return $prefix . $table;
    }
/**
    public static function getAllActive( ){ 
        return supplier_warranty_issues::where('closed', '=', 0)->get();
    }
**/
    public static function getAllActive( ){ 
        $supplierIds = supplier_warranty_issues::query()
            ->select('id_supplier')
            ->distinct()
            ->pluck('id_supplier')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($supplierIds->isEmpty()) {
            return [];
        }

        $supplierNames = suppliers::query()
            ->whereIn('id_supplier', $supplierIds)
            ->pluck('name', 'id_supplier');

        $suppliers = $supplierIds
            ->map(fn ($id) => (object) [
                'id_supplier' => $id,
                'name' => $supplierNames[$id] ?? ('Supplier #' . $id),
            ])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
        
        $suppliers_list = array();
        
        foreach($suppliers AS $supplier){
            $suppliers_list[] = [
                'id_supplier' => $supplier->id_supplier,
                'name' =>$supplier->name,
                'issues' => supplier_warranty_issues::where('id_supplier', $supplier->id_supplier)->orderBy('date', 'DESC')->get(),
            ];
        }

        return $suppliers_list;
    }

    public static function dashboard_warranties($type){

        $data = array();
        $bd_data = self::where('closed', 0)->get();

        foreach($bd_data AS $item) $data[] = ['reference' => $item->reference, 'action' => $item->action];
        
        return [
            'name'          => trans('dashboard.WARRANTIES'),
            'col'           => 4,
            'item_id'       => $type . '_warranties',
            'columns'       => ['reference', 'action'],
            'counter'       => count($data),
            'data'          => $data
        ];        
    }

    public static function saveNewIssue( $data ){ 
        
        $issue = new supplier_warranty_issues();
        $issue->id_order = $data->id_order;
        $issue->id_supplier = $data->id_supplier;
        $issue->date = $data->date;
        $issue->reference = $data->reference;
        $issue->description = $data->description;
        $issue->action = $data->action;
        $issue->closed = $data->closed + 0;
        $issue->save();
        return 1;
    }

    public static function updateIssue( $data ){ 
        
        $issue = supplier_warranty_issues::where('id', $data->id)->first();
        $issue->description = $data->description;
        $issue->action = $data->action;
        $issue->save();
        return 1;
    }

    public static function closeWarrantyIssue( $data ){ 
        
        if($data->move == 1){
            
            supplier_warranty_issues::where('id', $data->id)->update( [ 'closed' => 1 ]);
            supplier_issues::movedFromWarrantyIssue( $data->id );
            
        }else{
            supplier_warranty_issues::where('id', $data->id)->update( [ 'closed' => 1 ]);
        }
        return 1;
    }
}
