<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cart_product extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."cart_product";
    }
    
}
