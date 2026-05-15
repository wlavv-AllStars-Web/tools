<?php

namespace App\Models\modules\asg_cars;

use Illuminate\Database\Eloquent\Model;

class asg_cars_product extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'ps_custom_asg_cars_product';

    protected $primaryKey = 'id_asg_car_product';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id_asg_car' => 'integer',
        'id_lang' => 'integer',
        'id_product' => 'integer',
        'position' => 'integer',
    ];

    public function car()
    {
        return $this->belongsTo(
            asg_cars::class,
            'id_asg_car',
            'id_asg_car'
        );
    }
}
