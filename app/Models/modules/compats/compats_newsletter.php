<?php

namespace App\Models\modules\compats;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use App\Models\modules\compats\compats_options;

class compats_newsletter extends Model
{
    use HasFactory;
    protected $table = "compats_newsletter";
    public $primaryKey = 'id';
    public $timestamps = false;

    public static function saveMyCar( $id_customer, $iso_code, $store, $compat ){

        if( is_numeric($id_customer) ){
            $email = '';
        }else{
            $email = $id_customer;
            $id_customer = 0;
        }
        
        $new = new compats_newsletter();
        $new->id_compat = $compat->id_compat;
        $new->store = $store;
        $new->id_customer = $id_customer;
        $new->id_brand = $compat->id_brand;
        $new->brand = $compat->brand->name;
        $new->id_model = $compat->id_model;
        $new->model = $compat->model->name;
        $new->id_type = $compat->id_type;
        $new->type = $compat->type->name;
        $new->id_version = $compat->id_version;
        $new->version = $compat->version->name;
        $new->email = $email;
        $new->iso_code = $iso_code;
        $new->newsletter = 1;
        $new->save();
        
        return $new->id;

    }

    public static function getMyGarage($id_customer){
        
        $data =  compats_newsletter::where('id_customer', $id_customer)->get();
        
        $cars = array();
        
        foreach($data AS $compat){
            
            $cars[] = [
                'id_compat' => $compat->id_compat,
                'brand'     => $compat->brand,
                'model'     => $compat->model,
                'type'      => $compat->type,
                'version'   => $compat->version,
                'brand_logo'=> 'https://webtools.all-stars-motorsport.com/uploads/compats/brand/' . $compat->id_brand . '.png',
                'brand_hover_logo'=> 'https://webtools.all-stars-motorsport.com/uploads/compats/brand_hover/' . $compat->id_brand . '.png',
                'cartoon'   => 'https://webtools.all-stars-motorsport.com/uploads/compats/compat/' . $compat->id_compat.'.png'
            ];
            
        }
        
        return $cars;
    }

    public static function removeCarFromMyGarage($id_customer, $id_compat, $store){
        
        $data =  compats_newsletter::where('id_compat', $id_compat)->where('id_customer', $id_customer)->where('store', $store)->first();
        
        if( isset( $data->id_compat) ){
            $data->delete();
            return 1;
        }
        
        return 0;
    }
    
}
