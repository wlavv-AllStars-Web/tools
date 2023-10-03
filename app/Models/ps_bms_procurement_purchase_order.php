<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ps_supplier_lang;

class ps_bms_procurement_purchase_order extends Model
{
    protected $connection = 'mysql2';
    public $table = 'ps_bms_procurement_purchase_order';
    public $nrOpenOrders = 0;
    use HasFactory;

    public function supplier()
    {
        return $this->hasOne(ps_supplier::class, "id_supplier", 'supplier_id');
    }

    public function openOrders()
    {
         $this->openOrders = ps_bms_procurement_purchase_order::where('status_id', '5')->where('supplier_id', $this->supplier_id)->count();
    }
}
