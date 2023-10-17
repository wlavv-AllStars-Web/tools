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
}
