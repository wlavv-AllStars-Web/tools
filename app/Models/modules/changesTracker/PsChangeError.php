<?php

namespace App\Models\modules\changesTracker;

use Illuminate\Database\Eloquent\Model;

class PsChangeError extends Model
{
    protected $table = 'ps_change_errors';

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'resolution',
        'status',
        'detected_at',
        'resolved_at',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(PsChangeProject::class, 'project_id');
    }
}
