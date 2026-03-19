<?php

namespace App\Models\modules\shipping;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use App\Models\prestashop\suppliers;
use App\Models\modules\shipping\shipping_package;
use App\Models\modules\shipping\shipping_delay;
use App\Models\modules\supplier_map\supplier_map;
use App\Models\modules\shipping_erp\shipping_erp;

class shipping extends Model
{
    use HasFactory;
    protected $table = "shipping";
    public $primaryKey = 'id';
    
    private static $carriers = [
            1  => 'ABREU LOGISTICS',
            2  => 'ALL WAYS CARGO',
            3  => 'AM TRANSITÁRIOS',
            25 => 'CARGO BASE',
            30 => 'DACHSER',
            4  => 'DBSCHENKER',
            22 => 'DHL',
            5  => 'DPD',
            23 => 'EXTRA TRANSITARIOS',
            33 => 'FRACHT',
            6  => 'FEDEX',
            7  => 'GARLAND',
            29 => 'GLS',
            8  => 'LUSO CARGO',
            9  => 'MAERSK',
            10 => 'MAINFREIGHT',
            32 => 'MSC',
            11 => 'NACEX',
            27 => 'NAGEL',
            12 => 'NIPPON',
            26 => 'NOATUM',
            13 => 'RHENUS',
            31 => 'ROSADO',
            14 => 'SCHENKER',
            15 => 'SPEDYCARGO',
            16 => 'TNT',
            24 => 'TRANSGLORY',
            17 => 'TORRESTIR',
            18 => 'TRANSITEX',
            28 => 'TXT',
            19 => 'UPS',
            20 => 'WARELOG',
            21 => 'SENT BY SUPPLIER',
        ];

    public function supplier_info(){
        return $this->hasOne(suppliers::class, "id_supplier", 'supplier'); 
    }

    public function supplier_map(){
        return $this->hasOne(supplier_map::class, "id_supplier", 'supplier'); 
    }

    public function packaging(){
        return $this->hasMany(shipping_package::class, "id_shipping", 'id'); 
    }

    public function delays(){
        return $this->hasMany(shipping_delay::class, "id_shipping", 'id')->orderBy('id', 'DESC'); 
    }

    public function lastDelay(){
        return $this->hasOne(shipping_delay::class, "id_shipping", 'id')->orderBy('id', 'DESC'); 
    }
    
    public static function getShipements( $status, $date=null ){
        
        if( is_null($date)){
            return shipping::with('supplier_info', 'packaging', 'delays', 'supplier_map')
                ->where('status', $status)
                ->get()
                ->map(function ($shipment) {
                    $shipment->carrier_name = $shipment->carrier_name;
                    return $shipment;
                });
        }else{
            return shipping::with('supplier_info', 'packaging', 'delays')
                ->where('status', $status)
                ->orderBy($date, 'DESC')
                ->get()
                ->map(function ($shipment) {
                    $shipment->carrier_name = $shipment->carrier_name;
                    return $shipment;
                });
        }
    }
    
    public static function getShipment( $id ){
        
        return shipping::with('supplier_info')
            ->where('id', $id)
            ->first();
    }

    public static function getCarriers(){
        return self::$carriers;
    }

    public function getCarrierNameAttribute(){
        return self::$carriers[$this->carrier] ?? 'UNKNOWN';
    }

    public static function getCarrier($id_carrier){
        return self::$carriers[$id_carrier];
    }
    
    public static function create( $data ){
        
        $new = new shipping();
        $new->supplier = $data->supplier;
        $new->ready_date = $data->ready_date;
        $new->invoice_number = $data->invoice_number;
        $new->invoice = $data->invoice;
        $new->status = 1;
        $new->incoterm = $data->incoterm;
        $new->comments = $data->comments;
        $new->created_at = date('Y-m-d h:s:i');
        $new->updated_at = date('Y-m-d h:s:i');
        $new->save();
        
        $id_shipping = $new->id;
        
        shipping_package::addPackages($id_shipping, $data->package);
        
        return $id_shipping;
    }
    
    public static function updateData( $id, $data ){
        
        $new= shipping::where('id', $id)->first();
        $new->supplier = $data->supplier;
        $new->ready_date = $data->ready_date;
        $new->invoice = $data->invoice;
        $new->invoice_number = $data->invoice_number;
        $new->carrier = ($data->carrier == "") ? 0 : $data->carrier;
        $new->shipping_quote = $data->shipping_quote;
        $new->validation_date = $data->validation_date;
        $new->packing_list = $data->packing_list;
        $new->customs_docs = $data->customs_docs;
        $new->picking_date = $data->picking_date;
        $new->tracking = $data->tracking;
        $new->status = $data->status;
        $new->comments = $data->comments;
        $new->incoterm = $data->incoterm;
        $new->route = $data->route;
        $new->freight = $data->freight;
        $new->customs_clear = $data->customs_clear;
        $new->import_duties = $data->import_duties;
        $new->delivery_date = $data->delivery_date;
        $new->updated_at = date('Y-m-d h:s:i');
        $new->save();
        
        shipping_package::addPackages($id, $data->package);
        
        if(isset($data->erp) && ( !is_null($data->erp))){
            shipping_erp::saveERPRelation($id, $data->erp);
        }
        
        return $new->id_compat;
    }

    public static function getColorForRacio($racio) {
        
        $minRacio = 0;
        $maxRacio = 1;
        
        $racio = max($minRacio, min($racio, $maxRacio));

        $maxColorIntensity = 150;

        $red = min($maxColorIntensity, max(0, ($racio / $maxRacio) * $maxColorIntensity));  
        $green = min($maxColorIntensity, max(0, (1 - $racio / $maxRacio) * $maxColorIntensity)); 
        $blue = 0;

        return "rgb($red, $green, $blue)";
    }

    public function getRacioAttribute()
    {

        $racio = 0;

        if( $this->invoice > 0){
            $import = $this->freight + $this->customs_clear + $this->import_duties;
            $racio =  ($import / $this->invoice)*100;
        }else{
            $racio = 100;
        }
        
        return $racio;
    }

    public function getColorAttribute()
    {
        $percentage=0;
        if( $this->invoice > 0){
            $import = $this->freight + $this->handling + $this->customs_clear + $this->import_duties + $this->extras + $this->to_door;
            
            $percentage =  ( $import / $this->invoice ) * 100;
        }else{
            $percentage = 100;
        }
        
        
        $percentage = max(0, min(12, $percentage));
    
        // Aplicar uma curva quadrática para acelerar a transição para vermelho
        $adjustedPercentage = pow($percentage / 12, 2) * 100; 
    
        // Cálculo da cor baseada no gradiente verde → vermelho com transição rápida
        $r = min(255, (int)(255 * ($adjustedPercentage / 100))); // Vermelho cresce mais rápido
        $g = min(255, (int)(255 * ((100 - $adjustedPercentage) / 100))); // Verde diminui mais rápido
        $b = 0; // Azul fixo para manter a escala de cores
    
        return sprintf("#%02X%02X%02X", $r, $g, $b);
        
    }

}
