<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class manufacturers extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."manufacturer";
    }
    
    public function lang()
    {
        return $this->hasMany(manufacturers_lang::class, "id_manufacturer", 'id_manufacturer');
    }
    
    public static function getManufacturersForSelect()
    {
        return self::select('id_manufacturer', 'name')->get();
    }
}
