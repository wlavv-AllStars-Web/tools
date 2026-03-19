<?php

namespace App\Models\modules\bms_procurement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\suppliers;
use App\Models\prestashop\supplier_lang;

class bms_procurement_purchase_order extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."bms_procurement_purchase_order";
    }

    public function supplier()
    {
        return $this->hasOne(suppliers::class, "id_supplier", 'supplier_id');
    }

    public function openOrders()
    {
         $this->openOrders = bms_procurement_purchase_order::where('status_id', '5')->where('supplier_id', $this->supplier_id)->count();
    }

    public function rows()
    {
        return $this->hasMany(bms_procurement_purchase_order_product::class, "po_id", 'id_bms_procurement_purchase_order');
    }

    public static function createOrder($reference, $id_supplier)
    {

        $order = new bms_procurement_purchase_order();
        $order->date_add = date('Y-m-d');
        $order->date_upd = date('Y-m-d');
        $order->reference = $reference;
        $order->supplier_id = $id_supplier;
        $order->eta = date('Y-m-d');
        $order->status_id = 5;
        $order->employee_id = auth()->id();
        $order->currency_id = 42;
        $order->supplier_invoice_date = '0000-00-00';
        $order->supplier_invoice_due_date = '0000-00-00';
        $order->supplier_payment_date = '0000-00-00';
        $order->warehouse_id = 0;
        $order->printed = 0;

        $order->save();

        return $order->id;
    }

    public static function getOpenOrdersWithRows($id_supplier){
        return bms_procurement_purchase_order::with('rows')->where('supplier_id', $id_supplier)->where('date_add', '<', date("Y-m-d", strtotime("-1 months")))->whereIn('status_id', [5, 6])->get();
    }

    public static function getAllOpenOrdersWithRows(){
        return bms_procurement_purchase_order::with('rows')->where('date_add', '<', date("Y-m-d", strtotime("-1 months")))->whereIn('status_id', [5, 6])->get();
    }
    
}
