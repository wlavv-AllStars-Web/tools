<?php

namespace App\Models\modules\oms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierTermLevel extends Model
{
    use HasFactory;

    protected $table = 'oms_supplier_term_levels';

    protected $fillable = [
        'supplier_id',
        'label',
        'min_amount',
        'max_amount',
        'discount_percent',
        'free_shipping',
        'sort_order',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'supplier_id' => 'integer',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'free_shipping' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
