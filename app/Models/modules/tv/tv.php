<?php

namespace App\Models\modules\tv;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use App\Models\prestashop\manufacturers;

class tv extends Model{     

    use HasFactory;
    protected $table = 'tv';
    protected $fillable = [
        'id_manufacturer',
        'src',
        'active',
        'text',
    ];

    public $timestamps = false;
}