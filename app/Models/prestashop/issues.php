<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class issues extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."asm_tables";
    }
    
    public static function saveReport($id_type, $title, $message, $id_product, $id_product_attribute, $reference)
    {    
        issues::insert(
            [
                'id_type' => $id_type,
                'asm_year' => date('Y'),
                'asm_month' => date('m'),
                'done' => 0,
                'approved' => 0,
                'field_1' => $title,
                'field_2' => $message,
                'field_3' => $id_product,
                'field_4' => $id_product_attribute,
                'field_5' => $reference
            ]
        );
    }
}