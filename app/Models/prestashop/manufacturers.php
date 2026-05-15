<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class manufacturers extends PrestashopModel{
    
    use HasFactory;

    protected $primaryKey = 'id_manufacturer';
    protected $fillable = ['name'];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('manufacturer');
    }
    
    public function lang(){
        return $this->hasMany(manufacturers_lang::class, 'id_manufacturer', 'id_manufacturer');
    }

    public function products(){
        return $this->hasMany(product::class, 'id_manufacturer', 'id_manufacturer');
    }
    
    public static function getManufacturersForSelect(){
        return self::select('id_manufacturer', 'name')->orderBy('name')->get();
    }
}