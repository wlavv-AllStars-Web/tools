<?php

namespace App\Models\modules\homepageEditor;

use Illuminate\Database\Eloquent\Model;

class HomepageItemTemp extends Model
{
    protected $table = 'homepage_asm_temp';
    public $timestamps = false;

    protected $fillable = [
        'slot_id', 'icon_type', 'destination',
        'image_en', 'image_es', 'image_fr', 'info',
    ];
}
