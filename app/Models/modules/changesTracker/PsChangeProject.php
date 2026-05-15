<?php

namespace App\Models\modules\changesTracker;

use Illuminate\Database\Eloquent\Model;

class PsChangeProject extends Model
{
    protected $table = 'ps_change_projects';

    protected $fillable = [
        'title',
        'description',
        'requested_by',
        'change_date',
        'area',
        'status',
    ];

    protected $casts = [
        'change_date' => 'date',
    ];

    public function files()
    {
        return $this->hasMany(PsChangeFile::class, 'project_id');
    }

    public function errors()
    {
        return $this->hasMany(PsChangeError::class, 'project_id')->orderByDesc('created_at');
    }
}
