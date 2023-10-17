<?php

namespace App\Models\modules\bms_procurement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class bms_procurement_purchase_order_reception_product extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['reception_id'];


    public function __construct()
    {
        $this->table = env('DB2_prefix')."bms_procurement_purchase_order_reception_product";
    }
}
