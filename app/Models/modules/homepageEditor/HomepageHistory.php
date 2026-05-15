<?php

namespace App\Models\modules\homepageEditor;

use Illuminate\Database\Eloquent\Model;

class HomepageHistory extends Model
{
    protected $table = 'homepage_asm_history';
    public $timestamps = false;

    protected $fillable = [
        'publish_id', 'slot_id', 'icon_type', 'destination',
        'image_en', 'image_es', 'image_fr', 'info',
    ];
}
