<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class carriers extends PrestashopModel{
    
    use HasFactory;

    protected $fillable = [];

    public function __construct(array $attributes = []){
        
        parent::__construct($attributes);
        $this->table = self::tableName('carrier');
    }
}