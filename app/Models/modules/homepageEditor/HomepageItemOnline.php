<?php

namespace App\Models\modules\homepageEditor;

use Illuminate\Database\Eloquent\Model;

class HomepageItemOnline extends Model
{
    protected $table = 'homepage_asm_online';
    public $timestamps = false;

    protected $fillable = [
        'slot_id', 'active', 'icon_type', 'destination',
        'image_en', 'image_es', 'image_fr', 'info',
    ];
}
