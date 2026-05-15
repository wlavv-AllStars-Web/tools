<?php

namespace App\Models\modules\homepageASD;

use Illuminate\Database\Eloquent\Model;

class HomepageASDItem extends Model
{
    protected $table = 'homepage_asd';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'image_path',
        'link_url',
        'title',
    ];
}