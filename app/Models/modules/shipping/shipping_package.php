<?php

namespace App\Models\modules\shipping;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class shipping_package extends Model
{
    use HasFactory;
    protected $table = "shipping_package";
    public $primaryKey = 'id_shipping';

    public static function getPackages( $id_shipping ){
        return shipping_package::where('id_shipping', $id_shipping)->get();
    }
    
    public static function addPackages( $id_shipping, $data ){
        
        $i=0;
        foreach($data AS $item){
            if(isset($data['quantity'][$i])){
                
                if(isset($data['id'][$i])){
                    shipping_package::updatePackage( $i, $data );
                }else{
                    shipping_package::newPackage( $i, $id_shipping, $data );
                }   
            }
            
            $i++;
        }

        return 1;
    }

    public static function newPackage( $i, $id_shipping, $data ){
        
        $new = new shipping_package();
        $new->id_shipping   = $id_shipping;
        $new->type          = $data['type'][$i];
        $new->quantity      = $data['quantity'][$i];
        $new->width         = $data['width'][$i]+0;
        $new->height        = $data['height'][$i]+0;
        $new->depth         = $data['depth'][$i]+0;
        $new->weight        = $data['weight'][$i]+0;
        $new->created_at    = date('Y-m-d h:s:i');
        $new->updated_at    = date('Y-m-d h:s:i');
        $new->save();   
                
    }

    public static function updatePackage( $i, $data ){

        $package = shipping_package::where('id', $data['id'][$i])->update(
            [
                'type'          => $data['type'][$i],
                'quantity'      => $data['quantity'][$i],
                'width'         => $data['width'][$i]+0,
                'height'        => $data['height'][$i]+0,
                'depth'         => $data['depth'][$i]+0,
                'weight'        => $data['weight'][$i]+0,
                'updated_at'    => date('Y-m-d h:s:i')
            ]
        );
                
    }
    
}
