<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\attribute_lang;

class product_attribute_combination extends Model{
    
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."product_attribute_combination";
    }

    public function attribute_lang(){
        return $this->hasOne(attribute_lang::class, "id_attribute", 'id_attribute');
    }
}
