<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class asm_dashboard extends PrestashopModel
{
    use HasFactory;

    protected $fillable = ['name'];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('custom_asm_dashboard');
    }

    public static function getExceptions($board){
        return self::select('id_product')
            ->where('board', $board)
            ->get();
    }

    public static function addException($data){
        $exception = new self();
        $exception->board = $data->panel;
        $exception->id_product = $data->var_1;
        $exception->reference = $data->var_2;
        $exception->brand = (isset($data->var_3) && !is_null($data->var_3)) ? $data->var_3 : '';
        $exception->verified = 1;
        $exception->operator = auth()->id() ?: 0;
        $exception->save();

        $exception->logCustomAction('exception_added', [ 'panel' => $data->panel, 'id_product' => $data->var_1, 'reference' => $data->var_2 ]);

        return 1;
    }
}
