<?php

namespace App\Models\modules\ukoocompat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\suppliers;
use App\Models\prestashop\supplier_lang;

class ps_ASM_ukoo_customer extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."ps_ASM_ukoo_customer";
    }
}
