<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class orders_details extends Model
{   
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."order_detail";
    }

}
