<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product_lang extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."product_lang";
    }
}
