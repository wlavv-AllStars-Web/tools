<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Config;

class product_attribute_shop extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['wholesale_price'];
    
    public function __construct()
    {
        $this->table = env('DB2_prefix')."product_attribute_shop";
    }
}
