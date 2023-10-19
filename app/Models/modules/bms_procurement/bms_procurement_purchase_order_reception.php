<?php

namespace App\Models\modules\bms_procurement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class bms_procurement_purchase_order_reception extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['po_id'];

    public function __construct()
    {
        $this->table = env('DB2_prefix')."bms_procurement_purchase_order_reception";
    }

    
    public static function getLastEntries($nrEntries){

        $tempData = DB::table(env('DB2_DB_prefix') . 'bms_procurement_purchase_order_reception')
        ->select('po_id', 'id_bms_procurement_purchase_order_reception', 'reference', 'sku', 'qty', 'firstname', 'lastname', 'deleted')
        ->join(       env('DB2_DB_prefix') . 'bms_procurement_purchase_order', env('DB2_DB_prefix') . 'bms_procurement_purchase_order_reception.po_id', '=', env('DB2_DB_prefix') . 'bms_procurement_purchase_order.id_bms_procurement_purchase_order')
        ->join(       env('DB2_DB_prefix') . 'bms_procurement_purchase_order_reception_product', env('DB2_DB_prefix') . 'bms_procurement_purchase_order_reception.id_bms_procurement_purchase_order_reception', '=', env('DB2_DB_prefix') . 'bms_procurement_purchase_order_reception_product.reception_id')
        ->join(       env('DB2_DB_prefix') . 'employee', env('DB2_DB_prefix') . 'bms_procurement_purchase_order_reception.employee_id', '=', env('DB2_DB_prefix') . 'employee.id_employee')
        ->orderBy(    env('DB2_DB_prefix') . 'bms_procurement_purchase_order_reception.id_bms_procurement_purchase_order_reception', 'DESC')
        ->take($nrEntries)->get();

        return json_decode($tempData, true);        
    }    
}