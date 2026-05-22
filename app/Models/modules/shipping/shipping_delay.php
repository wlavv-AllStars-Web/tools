<?php

namespace App\Models\modules\shipping;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class shipping_delay extends Model
{
    use HasFactory;
    protected $table = "shipping_delay";
    public $primaryKey = 'id_shipping';

    public static function getDelays( $id_shipping ){
        return shipping_delay::where('id_shipping', $id_shipping)->orderBy('id', 'DESC')->get();
    }

    public static function delayExist( $id_shipping, $position ){
        $delay = shipping_delay::select('id')->where('id_shipping', $id_shipping)->where('position', $position)->first();
        
        return (isset($delay->id)) ? $delay->id : 0;
    }
    
    public static function addDelay( $id_shipping, $eta, $delay_position ){
        
        foreach($eta AS $key => $item){
            
            $exist = shipping_delay::delayExist( $id_shipping, $delay_position[$key] );
            
            if( $exist ){
                shipping_delay::updateDelay( $exist, $eta[$key], $delay_position[$key] );
            }else{
                if( strlen( $delay_position[$key] ) > 0 ) shipping_delay::newDelay( $id_shipping, $eta[$key], $delay_position[$key] );
            }   
        }

        return 1;
    }

    public static function addNewDelay( $id_shipping, $eta ){
        
        $lastDelay = shipping_delay::where('id_shipping', $id_shipping)->orderBy('position', 'desc')->first();
        
        $position = (isset($lastDelay->position)) ? ((int) $lastDelay->position + 1) : 1;
        
        shipping_delay::newDelay( $id_shipping, $eta, $position );
        
    }
    
    public static function newDelay( $id_shipping, $delay, $position ){
        
        $new = new shipping_delay();
        $new->id_shipping   = $id_shipping;
        $new->position      = $position;
        $new->date          = $delay;
        $new->created_at    = date('Y-m-d h:s:i');
        $new->updated_at    = date('Y-m-d h:s:i');
        $new->save();   
                
    }

    public static function updateDelay( $id, $delay, $position ){

        $package = shipping_delay::where('id', $id)->update(
            [
                'position'      => $position,
                'date'          => $delay,
                'updated_at'    => date('Y-m-d h:s:i')
            ]
        );
                
    }
    
}
