<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class suppliers extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = 'id_supplier';
    protected $fillable = ['name'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('supplier');
    }
    
    public function lang()
    {
        return $this->hasMany(suppliers_lang::class, 'id_supplier', 'id_supplier');
    }
    
    public function lang_en()
    {
        return $this->hasOne(suppliers_lang::class, 'id_supplier', 'id_supplier')
            ->where('id_lang', 1);
    }
    
    public function address()
    {
        return $this->hasOne(address::class, 'id_supplier', 'id_supplier');
    }

    public function products()
    {
        return $this->hasMany(product::class, 'id_supplier', 'id_supplier');
    }
}