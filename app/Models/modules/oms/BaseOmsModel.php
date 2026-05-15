<?php

namespace App\Models\modules\oms;

use Illuminate\Database\Eloquent\Model;

abstract class BaseOmsModel extends Model
{
    protected $connection = 'mysql';
    protected $guarded = [];

    public static function psPrefix(): string
    {
        return env('DB2_prefix', env('DB2_DB_prefix', 'ps_'));
    }
}
