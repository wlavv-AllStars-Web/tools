<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\prestashop\product_lang;

class product extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."product";
    }

    public function lang()
    {
        return $this->hasOne(product_lang::class, "id_product", 'id_product')->where('id_lang', 1);
    }
}
