<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class profiles extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = 'id_profile';
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('profile');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function employees()
    {
        return $this->hasMany(employees::class, 'id_profile', 'id_profile');
    }

    public function permissions()
    {
        return $this->hasMany(permissions::class, 'id_profile', 'id_profile');
    }
}