<?php

namespace App\Models\modules\carrierEndOfDay;

use Illuminate\Database\Eloquent\Model;

class CarrierEndOfDayDocumentLine extends Model
{
    protected $table = 'carrier_end_of_day_document_lines';

    protected $fillable = [
        'document_id',
        'source_order_carrier_id',
        'order_id',
        'order_reference',
        'country',
        'weight',
        'width',
        'length',
        'depth',
        'tracking_number',
    ];
}
