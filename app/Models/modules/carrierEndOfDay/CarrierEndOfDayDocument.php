<?php

namespace App\Models\modules\carrierEndOfDay;

use Illuminate\Database\Eloquent\Model;

class CarrierEndOfDayDocument extends Model
{
    protected $table = 'carrier_end_of_day_documents';

    protected $fillable = [
        'document_date',
        'carrier_name',
        'shipments_count',
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'document_date' => 'date',
        'generated_at' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(CarrierEndOfDayDocumentLine::class, 'document_id')
            ->orderBy('order_id')
            ->orderBy('order_reference');
    }
}
