<?php

namespace App\Models\modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

abstract class ToolsModel extends Model
{
    protected $connection = 'mysql';

    protected static function hasToolsTable(string $table): bool
    {
        return Schema::connection('mysql')->hasTable($table);
    }
}
