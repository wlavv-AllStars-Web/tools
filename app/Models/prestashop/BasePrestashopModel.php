<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Model;

abstract class BasePrestashopModel extends Model
{
    protected $connection = 'mysql2';
    public $timestamps = false;
    protected $guarded = [];
}
