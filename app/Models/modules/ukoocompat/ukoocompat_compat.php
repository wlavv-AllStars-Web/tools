<?php

namespace App\Models\modules\ukoocompat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\suppliers;
use App\Models\prestashop\supplier_lang;

class ukoocompat_compat extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."ukoocompat_compat";
    }

    public static function getCompatsOfTheProduct($id_product){
        return self::select('id_ukoocompat_compat')->where('id_product', $id_product)->get();
    }
}
