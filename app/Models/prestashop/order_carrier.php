<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class order_carrier extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."order_carrier";
    }

    public static function shippingByOrder($year){
        
        $total_order = self::select( DB::raw('sum(ps_orders.total_shipping_tax_incl) AS total_shipping') )
            ->leftjoin( 'ps_orders', 'ps_order_carrier.id_order', '=', 'ps_orders.id_order')
            ->where('ps_order_carrier.date_add', '>', date('Y') . '-01-01 00:00:00')
            ->whereIn('ps_orders.current_state', [2, 3, 4, 5, 15, 16, 28])
            ->value('total_shipping');
        
        $total_by_carrier = self::select( 'ps_carrier.name AS name', DB::raw('sum(ps_orders.total_shipping_tax_incl) AS total_shipping') )
            ->leftjoin( 'ps_carrier', 'ps_carrier.id_carrier', '=', 'ps_order_carrier.id_carrier')
            ->leftjoin( 'ps_orders', 'ps_order_carrier.id_order', '=', 'ps_orders.id_order')
            ->where('ps_orders.date_add', '>', date('Y') . '-01-01 00:00:00')
            ->whereIn('ps_orders.current_state', [2, 3, 4, 5, 15, 16, 28])
            ->groupBy('ps_carrier.id_reference')
            ->get();
            
        $carrier_data['DPD'] = 0;
        $carrier_data['TNT'] = 0;
        $carrier_data['FEDEX'] = 0;
        $carrier_data['GLS'] = 0;
        $carrier_data['UPS'] = 0;
        $carrier_data['NACEX'] = 0;
        
        foreach($total_by_carrier AS $carrier){
            
            if (strpos($carrier['name'],"DPD") !== false)   $carrier_data['DPD']   += $carrier['total_shipping'];
            if (strpos($carrier['name'],"UPS") !== false)   $carrier_data['UPS']   += $carrier['total_shipping'];
            if (strpos($carrier['name'],"TNT") !== false)   $carrier_data['TNT']   += $carrier['total_shipping'];
            if (strpos($carrier['name'],"NACEX") !== false) $carrier_data['NACEX'] += $carrier['total_shipping'];
            if (strpos($carrier['name'],"FEDEX") !== false) $carrier_data['FEDEX'] += $carrier['total_shipping'];
            if (strpos($carrier['name'],"GLS") !== false)   $carrier_data['GLS']   += $carrier['total_shipping'];

        }

        $carrier_data['total'] = $total_order;

        return $carrier_data;
        
    }
}
