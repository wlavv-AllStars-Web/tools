<?php

namespace App\Models\modules\ukoocompat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\suppliers;
use App\Models\prestashop\supplier_lang;

class ukoocompat_compat_criterion extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."ukoocompat_compat_criterion";
    }

    private static function getBrand($id_compat){
        return self::where('id_ukoocompat_compat', $id_compat)->where('id_ukoocompat_filter', 1)->pluck('id_ukoocompat_criterion')->first();
    }

    private static function getModel($id_compat){
        return self::where('id_ukoocompat_compat', $id_compat)->where('id_ukoocompat_filter', 2)->pluck('id_ukoocompat_criterion')->first();
    }

    private static function getType($id_compat){
        return self::where('id_ukoocompat_compat', $id_compat)->where('id_ukoocompat_filter', 3)->pluck('id_ukoocompat_criterion')->first();
    }

    private static function getVersion($id_compat){
        return self::where('id_ukoocompat_compat', $id_compat)->where('id_ukoocompat_filter', 4)->pluck('id_ukoocompat_criterion')->first();
    }

    public static function getCompatDetails($id_compat){
        
        return (object)[
            'brand' =>      self::getBrand($id_compat),
            'model' =>      self::getModel($id_compat),
            'type' =>       self::getType($id_compat),
            'version' =>    self::getVersion($id_compat)
        ];
    }
}
