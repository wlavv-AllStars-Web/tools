<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ps_bms_procurement_purchase_order;

class ps_supplier extends Model
{
    protected $connection = 'mysql2';
    public $table = 'ps_supplier';
    use HasFactory;

}
