<?php

namespace App\Models\modules\asd_alerts;

use Illuminate\Database\Eloquent\Model;

class AsdAlertMessage extends Model
{
    protected $table = 'asd_alert_messages';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'message_status' => 'integer',
        'deleted' => 'integer',
        'creation_date' => 'datetime',
        'expiration_date' => 'datetime',
        'deleted_date' => 'datetime',
    ];

    public function isActive(): bool
    {
        return (int) $this->message_status === 1;
    }
}
