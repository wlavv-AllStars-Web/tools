<?php

namespace App\Models\modules\asg_cars;

use Illuminate\Database\Eloquent\Model;

class asg_cars extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'ps_custom_asg_cars';

    protected $primaryKey = 'id_asg_car';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id_shop' => 'integer',
        'display' => 'integer',
        'position' => 'integer',
    ];

    public function products()
    {
        return $this->hasMany(
            asg_cars_product::class,
            'id_asg_car',
            'id_asg_car'
        )->orderBy('category')->orderBy('position')->orderBy('id_asg_car_product');
    }

    public function getImagesArrayAttribute(): array
    {
        if (empty($this->images)) {
            return [];
        }

        $decoded = json_decode($this->images, true);

        return is_array($decoded) ? $decoded : [];
    }
}
