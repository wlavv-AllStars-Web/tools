<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class cms_lang extends PrestashopModel{
    
    use HasFactory;

    protected $fillable = [];

    public function __construct(array $attributes = []){
        
        parent::__construct($attributes);
        $this->table = self::tableName('cms_lang');
    }

    public function cms(){
        
        return $this->belongsTo(cms::class, 'id_cms', 'id_cms');
    }
}