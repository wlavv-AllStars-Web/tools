<?php

namespace App\Models\modules\changesTracker;

use Illuminate\Database\Eloquent\Model;

class PsChangeFile extends Model
{
    protected $table = 'ps_change_files';

    protected $fillable = [
        'project_id',
        'original_name',
        'stored_path',
        'mime_type',
        'file_size',
    ];

    public function project()
    {
        return $this->belongsTo(PsChangeProject::class, 'project_id');
    }
}
