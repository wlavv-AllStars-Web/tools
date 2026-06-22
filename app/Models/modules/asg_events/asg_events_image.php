<?php

namespace App\Models\modules\asg_events;

use Illuminate\Database\Eloquent\Model;

class asg_events_image extends Model
{
    protected $connection = 'mysql';

    protected $table = 'galleries_images';

    protected $primaryKey = 'id_gallery_image';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id_gallery' => 'integer',
        'position' => 'integer',
    ];

    public function gallery()
    {
        return $this->belongsTo(
            asg_events::class,
            'id_gallery',
            'id_gallery'
        );
    }
}
