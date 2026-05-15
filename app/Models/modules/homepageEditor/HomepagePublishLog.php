<?php

namespace App\Models\modules\homepageEditor;

use Illuminate\Database\Eloquent\Model;

class HomepagePublishLog extends Model
{
    protected $table = 'homepage_asm_publish_logs';
    public $timestamps = false;

    protected $fillable = [
        'published_by', 'published_at', 'notes',
    ];
}
