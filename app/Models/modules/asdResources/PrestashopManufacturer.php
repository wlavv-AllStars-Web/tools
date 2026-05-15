<?php

namespace App\Models\modules\asdResources;

use Illuminate\Database\Eloquent\Model;

class PrestashopManufacturer extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'ps_manufacturer';
    protected $primaryKey = 'id_manufacturer';

    public $timestamps = false;

    protected $guarded = [];
}
