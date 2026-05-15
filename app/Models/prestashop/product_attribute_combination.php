<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class product_attribute_combination extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = null; // tabela não tem PK real
    public $incrementing = false;

    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('product_attribute_combination');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function attribute()
    {
        return $this->belongsTo(attribute::class, 'id_attribute', 'id_attribute');
    }

    public function attribute_langs()
    {
        return $this->hasMany(attribute_lang::class, 'id_attribute', 'id_attribute');
    }

    public function attribute_lang()
    {
        return $this->hasOne(attribute_lang::class, 'id_attribute', 'id_attribute')
            ->where('id_lang', config('app.id_lang') ?? 1);
    }
}