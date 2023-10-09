<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."product";
    }
}
