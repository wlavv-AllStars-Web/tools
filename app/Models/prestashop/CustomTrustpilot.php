<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomTrustpilot extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql2';

    protected $table = 'ps_custom_trustpilot';

    protected $primaryKey = 'id_custom_trustpilot';

    protected $fillable = [
        'recorded_on',
        'review_count',
        'rating',
        'review_count_period',
        'period_type',
        'period_start',
        'period_end',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'recorded_on' => 'date',
        'review_count' => 'integer',
        'rating' => 'decimal:1',
        'review_count_period' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
