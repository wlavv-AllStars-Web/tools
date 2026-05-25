<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class order_history extends PrestashopModel{
    
    use HasFactory;

    protected $primaryKey = 'id_order_history';
    protected $fillable = ['name'];

    public function __construct(array $attributes = []){
        
        parent::__construct($attributes);
        $this->table = self::tableName('order_history');
    }
    
    public static function getPanelInfo($array){
        
        $orderHistoryTable = self::tableName('order_history');
        $ordersTable = self::tableName('orders');
        $customerTable = self::tableName('customer');
        $orderStateLangTable = self::tableName('order_state_lang');

        return self::select(
                $orderHistoryTable . '.id_order',
                $orderHistoryTable . '.id_order_state',
                $ordersTable . '.reference',
                DB::raw('COUNT(' . $orderHistoryTable . '.id_order_state) AS total'),
                DB::raw('COALESCE(' . $orderStateLangTable . '.name, ' . $orderHistoryTable . '.id_order_state) AS status'),
                $customerTable . '.firstname',
                $customerTable . '.lastname'
            )
            ->join($ordersTable, $orderHistoryTable . '.id_order', '=', $ordersTable . '.id_order')
            ->join($customerTable, $ordersTable . '.id_customer', '=', $customerTable . '.id_customer')
            ->leftJoin($orderStateLangTable, function ($join) use ($orderHistoryTable, $orderStateLangTable) {
                $join->on($orderStateLangTable . '.id_order_state', '=', $orderHistoryTable . '.id_order_state')
                    ->where($orderStateLangTable . '.id_lang', 1);
            })
            ->when(!empty($array), function ($query) use ($array, $orderHistoryTable) {
                $query->whereNotIn($orderHistoryTable . '.id_order', $array);
            })
            ->groupBy(
                $orderHistoryTable . '.id_order',
                $orderHistoryTable . '.id_order_state',
                $ordersTable . '.reference',
                $orderStateLangTable . '.name',
                $customerTable . '.firstname',
                $customerTable . '.lastname'
            )
            ->havingRaw('COUNT(' . $orderHistoryTable . '.id_order_state) > 1')
            ->get();
    }

    public function order(){
        return $this->belongsTo(orders::class, 'id_order', 'id_order');
    }
}
