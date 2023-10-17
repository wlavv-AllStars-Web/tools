<?php

namespace App\Models\modules\bms_procurement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;

class bms_procurement_purchase_order_product extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."bms_procurement_purchase_order_product";
    }

    public function product()
    {
        return $this->hasOne(product::class, "id_product", 'product_id');
    }

    public function attribute()
    {
        return $this->hasOne(product_attribute::class, "id_product_attribute", 'product_attribute_id');
    }
}
