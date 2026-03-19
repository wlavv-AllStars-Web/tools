<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\prestashop\ukoocompat_compat_criterion;

class ukoocompat_compat extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."ukoocompat_compat";
    }
    
    public function compat_criterion_brand(){
        return $this->hasOne(ukoocompat_compat_criterion::class, "id_ukoocompat_compat", 'id_ukoocompat_compat')->where('id_ukoocompat_filter', 1);
    }
    
    public function compat_criterion_model(){
        return $this->hasOne(ukoocompat_compat_criterion::class, "id_ukoocompat_compat", 'id_ukoocompat_compat')->where('id_ukoocompat_filter', 2);
    }
    
    public function compat_criterion_type(){
        return $this->hasOne(ukoocompat_compat_criterion::class, "id_ukoocompat_compat", 'id_ukoocompat_compat')->where('id_ukoocompat_filter', 3);
    }
    
    public function compat_criterion_version(){
        return $this->hasOne(ukoocompat_compat_criterion::class, "id_ukoocompat_compat", 'id_ukoocompat_compat')->where('id_ukoocompat_filter', 4);
    }
    
}
