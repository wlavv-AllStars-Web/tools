<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class stock_available extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."stock_available";
    }

    public static function counterNegativeStock(){

        $tempData = DB::table(env('DB2_DB_prefix') . 'stock_available')
            ->join(       env('DB2_DB_prefix') . 'product',            env('DB2_DB_prefix') . 'stock_available.id_product', '=', env('DB2_DB_prefix') . 'product.id_product')
            ->select(     env('DB2_DB_prefix') . 'product.reference',  env('DB2_DB_prefix') . 'stock_available.quantity')
            ->where(      env('DB2_DB_prefix') . 'stock_available.quantity', '<', 0 )
            ->orderBy(    env('DB2_DB_prefix') . 'product.reference')
            ->get();

        $data = json_decode($tempData, true);

        return [
            'col'     => 2,
            'item_id' => 'counter_negativeStock',
            'name'    => trans('Negative stock'),
            'counter' => count($data),
            'columns' => [trans('reference'), trans('quantity') ],
            'data'    => $data
        ];
    }
    

    public static function panelNegativeStock(){

        $tempData = DB::table(env('DB2_DB_prefix') . 'stock_available')
            ->join(       env('DB2_DB_prefix') . 'product',            env('DB2_DB_prefix') . 'stock_available.id_product', '=', env('DB2_DB_prefix') . 'product.id_product')
            ->select(     env('DB2_DB_prefix') . 'product.reference',  env('DB2_DB_prefix') . 'stock_available.quantity')
            ->where(      env('DB2_DB_prefix') . 'stock_available.quantity', '<', 0 )
            ->orderBy(    env('DB2_DB_prefix') . 'product.reference')
            ->get();

        $data = json_decode($tempData, true);

        return [
            'item_id' => 'panel_negativeStock',
            'name'    => trans('Negative stock'),
            'counter' => count($data),
            'columns' => [trans('reference'), trans('quantity') ],
            'data'    => $data
        ];
    }
    
}
