<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class product_attribute extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."product_attribute";
    }

    public function product()
    {
        return $this->hasOne(product::class, "id_product", 'id_product');
    }

    public static function getCombination($id_product_attribute){

        $combination = '';
        $attribute_name = null;
   
        if($id_product_attribute > 0){

            $data = DB::table(env('DB2_DB_prefix') . 'attribute_lang')
            ->select( env('DB2_DB_prefix') . 'attribute_lang.name')
            ->join(   env('DB2_DB_prefix') . 'product_attribute_combination', env('DB2_DB_prefix') . 'attribute_lang.id_attribute', '=', env('DB2_DB_prefix') . 'product_attribute_combination.id_attribute')
            ->join(   env('DB2_DB_prefix') . 'product_attribute', env('DB2_DB_prefix') . 'product_attribute_combination.id_product_attribute', '=', env('DB2_DB_prefix') . 'product_attribute.id_product_attribute')
            ->where(  env('DB2_DB_prefix') . 'product_attribute.id_product_attribute', $id_product_attribute )
            ->where(  env('DB2_DB_prefix') . 'attribute_lang.id_lang', 1 )
            ->get();

            foreach($data AS $item) $attribute_name .= $item->name. '; ';
            $combination =  substr($attribute_name, 0, -2);
        }

        return $combination;
    }
    
}
