<?php

namespace App\Models\modules\oms;

class LogisticContainer extends BaseOmsModel
{
    protected $table = 'oms_logistic_containers';

    protected $fillable = [
        'type',
        'name',
        'width_cm',
        'height_cm',
        'depth_cm',
        'max_weight_kg',
        'max_pallets',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'width_cm' => 'float',
        'height_cm' => 'float',
        'depth_cm' => 'float',
        'max_weight_kg' => 'float',
        'max_pallets' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
