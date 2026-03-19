<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class asm_dashboard extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."asm_dashboard";
    }

    public static function getExceptions($board)
    {
        return self::select('id_product')->where('board', $board)->get();
    }

    public static function addException($data)
    {

        $exception = new asm_dashboard();
        $exception->board = $data->panel;
        $exception->id_product = $data->var_1;
        $exception->reference = $data->var_2;
        $exception->brand = ( isset($data->var_3) && !is_null($data->var_3)) ? $data->var_3 : '';
        $exception->verified = 1;
        $exception->operator = auth()->id();

        $exception->save();
        
        return 1;
    }
    
    
}
