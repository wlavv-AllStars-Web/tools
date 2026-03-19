<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\suppliers_lang;
use App\Models\prestashop\address;

class suppliers extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."supplier";
    }
    
    public function lang()
    {
        return $this->hasMany(suppliers_lang::class, "id_supplier", 'id_supplier');
    }
    
    public function lang_en()
    {
        return $this->hasOne(suppliers_lang::class, "id_supplier", 'id_supplier')->where('id_lang', 1);
    }
    
    public function address()
    {
        return $this->hasOne(address::class, "id_supplier", 'id_supplier');
    }

}
