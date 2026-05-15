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

        return self::select(
                $orderHistoryTable . '.id_order',
                $ordersTable . '.reference',
                DB::raw('COUNT(' . $orderHistoryTable . '.id_order_state) AS total'),
                $customerTable . '.firstname',
                $customerTable . '.lastname'
            )
            ->join($ordersTable, $orderHistoryTable . '.id_order', '=', $ordersTable . '.id_order')
            ->join($customerTable, $ordersTable . '.id_customer', '=', $customerTable . '.id_customer')
            ->where($orderHistoryTable . '.id_order_state', 2)
            ->when(!empty($array), function ($query) use ($array, $orderHistoryTable) {
                $query->whereNotIn($orderHistoryTable . '.id_order', $array);
            })
            ->groupBy(
                $orderHistoryTable . '.id_order',
                $ordersTable . '.reference',
                $customerTable . '.firstname',
                $customerTable . '.lastname'
            )
            ->get();
    }

    public function order(){
        return $this->belongsTo(orders::class, 'id_order', 'id_order');
    }
}