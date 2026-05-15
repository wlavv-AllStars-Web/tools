<?php

namespace App\Models\modules\asdResources;

use Illuminate\Database\Eloquent\Model;

class PrestashopManufacturerShop extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'ps_manufacturer_shop';

    public $timestamps = false;
    public $incrementing = false;

    protected $guarded = [];
}
