<?php

namespace App\Models\modules\asdResources;

use Illuminate\Database\Eloquent\Model;

class ASDBrandResource extends Model
{
    protected $table = 'asd_brand_resources';

    protected $fillable = [
        'id_manufacturer',
        'id_shop',
        'facebook_url',
        'website_url',
        'last_update',
        'catalog_file',
        'import_file',
        'logos_zip',
        'images_zip',
    ];
}