<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class product_attachment extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = 'id_product_attachment';
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('product_attachment');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function attachment()
    {
        return $this->hasOne(attachment::class, 'id_attachment', 'id_attachment');
    }

    public function product()
    {
        return $this->hasOne(product::class, 'id_product', 'id_product');
    }
}