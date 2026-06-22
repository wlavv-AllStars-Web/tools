<?php

namespace App\Models\modules\asg_events;

use Illuminate\Database\Eloquent\Model;

class asg_events extends Model
{
    protected $connection = 'mysql';

    protected $table = 'galleries';

    protected $primaryKey = 'id_gallery';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id_shop' => 'integer',
        'display' => 'integer',
        'position' => 'integer',
    ];

    public function images()
    {
        return $this->hasMany(
            asg_events_image::class,
            'id_gallery',
            'id_gallery'
        )->orderBy('position')->orderBy('id_gallery_image');
    }

    public function getImagesArrayAttribute(): array
    {
        return $this->images
            ->pluck('image')
            ->filter()
            ->values()
            ->all();
    }

    public function getIsExternalAttribute(): bool
    {
        return $this->gallery_type === 'flickr';
    }
}
