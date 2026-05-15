<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class manufacturers_lang extends PrestashopModel{
    
    use HasFactory;

    protected $primaryKey = null; // tabela sem PK simples
    public $incrementing = false;

    protected $fillable = [];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('manufacturer_lang');
    }

    public function manufacturer(){
        return $this->belongsTo(manufacturers::class, 'id_manufacturer', 'id_manufacturer');
    }

    public function language(){
        return $this->belongsTo(language::class, 'id_lang', 'id_lang');
    }
}