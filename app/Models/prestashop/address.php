<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\country;

class address extends Model
{

    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct(){
        $this->table = env('DB2_prefix')."address";
    }

    public function country(){
        return $this->hasOne(country::class, "id_country", 'id_country');
    }
}
