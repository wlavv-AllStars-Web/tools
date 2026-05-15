<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class order_carrier extends PrestashopModel{
    
    use HasFactory;

    protected $primaryKey = 'id_order_carrier';
    protected $fillable = ['name'];

    public function __construct(array $attributes = []){
        
        parent::__construct($attributes);
        $this->table = self::tableName('order_carrier');
    }

    public static function shippingByOrder($year){
        
        $orderCarrierTable = self::tableName('order_carrier');
        $ordersTable = self::tableName('orders');
        $carrierTable = self::tableName('carrier');

        $dateFrom = $year . '-01-01 00:00:00';
        $validStates = [2, 3, 4, 5, 15, 16, 28];

        $total_order = self::select(DB::raw('SUM(' . $ordersTable . '.total_shipping_tax_incl) AS total_shipping'))
            ->leftJoin($ordersTable, $orderCarrierTable . '.id_order', '=', $ordersTable . '.id_order')
            ->where($orderCarrierTable . '.date_add', '>', $dateFrom)
            ->whereIn($ordersTable . '.current_state', $validStates)
            ->value('total_shipping');

        $total_by_carrier = self::select( $carrierTable . '.name AS name', DB::raw('SUM(' . $ordersTable . '.total_shipping_tax_incl) AS total_shipping') )
            ->leftJoin($carrierTable, $carrierTable . '.id_carrier', '=', $orderCarrierTable . '.id_carrier')
            ->leftJoin($ordersTable, $orderCarrierTable . '.id_order', '=', $ordersTable . '.id_order')
            ->where($ordersTable . '.date_add', '>', $dateFrom)
            ->whereIn($ordersTable . '.current_state', $validStates)
            ->groupBy($carrierTable . '.id_reference', $carrierTable . '.name')
            ->get();

        $carrier_data = [
            'DPD' => 0,
            'TNT' => 0,
            'FEDEX' => 0,
            'GLS' => 0,
            'UPS' => 0,
            'NACEX' => 0,
        ];

        foreach ($total_by_carrier as $carrier) {
            $name = strtoupper($carrier->name ?? '');
            $total = (float) ($carrier->total_shipping ?? 0);
            if (strpos($name, 'DPD') !== false) $carrier_data['DPD'] += $total;
            if (strpos($name, 'UPS') !== false) $carrier_data['UPS'] += $total;
            if (strpos($name, 'TNT') !== false) $carrier_data['TNT'] += $total;
            if (strpos($name, 'NACEX') !== false) $carrier_data['NACEX'] += $total;
            if (strpos($name, 'FEDEX') !== false) $carrier_data['FEDEX'] += $total;
            if (strpos($name, 'GLS') !== false) $carrier_data['GLS'] += $total;
        }

        $carrier_data['total'] = (float) ($total_order ?? 0);
        return $carrier_data;
    }
}