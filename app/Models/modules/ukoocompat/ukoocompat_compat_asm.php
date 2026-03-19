<?php

namespace App\Models\modules\ukoocompat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\modules\ukoocompat\ukoocompat_criterion_lang;

class ukoocompat_compat_asm extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."ukoocompat_compat_asm";
    }

    public static function getBrands(){
        return self::with( 'brand_en', 'brand_es', 'brand_fr' )->groupBy('id_filter_value_1')->orderBy('id_filter_value_1')->get();
    }

    public static function getModels(){
        return self::with( 'model_en', 'model_es', 'model_fr' )->groupBy('id_filter_value_2')->orderBy('id_filter_value_2')->get();
    }

    public static function getVersions(){
        return self::with( 'version_en', 'version_es', 'version_fr' )->groupBy('id_filter_value_3')->orderBy('id_filter_value_3')->get();
    }

    public static function getTypes(){
        return self::with( 'type_en', 'type_es', 'type_fr' )->groupBy('id_filter_value_4')->orderBy('id_filter_value_4')->get();
    }

    public static function getCompatsRelations(){
        return self::with('brand', 'model', 'version', 'type')->groupBy('id_filter_value_4')->orderBy('id_filter_value_1')->get();
    }


    public function brand_en(){ return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_1')->where('id_lang', 1)->where('id_filter', 1); }
    public function brand_es(){ return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_1')->where('id_lang', 4)->where('id_filter', 1); }
    public function brand_fr(){ return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_1')->where('id_lang', 5)->where('id_filter', 1); }


    public function model_en(){ return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_2')->where('id_lang', 1)->where('id_filter', 2); }
    public function model_es(){ return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_2')->where('id_lang', 4)->where('id_filter', 2); }
    public function model_fr(){ return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_2')->where('id_lang', 5)->where('id_filter', 2); }


    public function version_en(){ return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_3')->where('id_lang', 1)->where('id_filter', 3); }
    public function version_es(){ return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_3')->where('id_lang', 4)->where('id_filter', 3); }
    public function version_fr(){ return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_3')->where('id_lang', 5)->where('id_filter', 3); }


    public function type_en(){ return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_4')->where('id_lang', 1)->where('id_filter', 4); }
    public function type_es(){ return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_4')->where('id_lang', 4)->where('id_filter', 4); }
    public function type_fr(){ return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_4')->where('id_lang', 5)->where('id_filter', 4); }


    public function brand(){   return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_1')->where('id_lang', 1)->where('id_filter', 1); }
    public function model(){   return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_2')->where('id_lang', 1)->where('id_filter', 2); }
    public function version(){ return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_3')->where('id_lang', 1)->where('id_filter', 3); }
    public function type(){    return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_filter_value_4')->where('id_lang', 1)->where('id_filter', 4); }
}
