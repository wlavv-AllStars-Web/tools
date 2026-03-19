<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\ukoocompat_criterion_lang;

class ps_ukoocompat_criterion extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."ps_ukoocompat_criterion";
    }

    public function criterion_lang(){
        return $this->hasOne(ukoocompat_criterion_lang::class, "id_ukoocompat_criterion", 'id_ukoocompat_criterion')->where('id_lang', 1);
    }   
}
