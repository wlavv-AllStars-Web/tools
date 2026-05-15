<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class suppliers_lang extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('supplier_lang');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function supplier()
    {
        return $this->belongsTo(suppliers::class, 'id_supplier', 'id_supplier');
    }

    public function language()
    {
        return $this->belongsTo(language::class, 'id_lang', 'id_lang');
    }
}