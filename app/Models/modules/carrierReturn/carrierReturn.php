<?php

namespace App\Models\modules\carrierReturn;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class carrierReturn extends Model
{
    use HasFactory;
    protected $table = "carrierReturn";
    public $primaryKey = 'id';
    protected $fillable = [ 'archived', 'carrier' ];

    public static function getCarrierReturn($active = 1){
        
        if( $active == 1){
            return self::where('archived', 0)->orderBy('created_at', 'DESC')->get();
        }else{
            return self::where('archived', 1)->orderBy('created_at', 'DESC')->get();
        }
    }
    
    public static function getIssue($id){
        return carrierReturn::where('id', $id)->first();
    }
    
    public static function saveData($form_data){
        
        $data = new carrierReturn();
        
        $data->id_order = $form_data->id_order;
        $data->carrier = $form_data->carrier;
        $data->tracking = $form_data->tracking;
        $data->date = $form_data->date;
        $data->issue = $form_data->issue;
        $data->notes = $form_data->notes;
        $data->save();
        return 1;
    }

    public static function updateData($form_data){
        
        $data = carrierReturn::where('id', $form_data->id)->first();
        $data->id_order = $form_data->id_order;
        $data->date = $form_data->date;
        $data->carrier = $form_data->carrier;
        $data->tracking = $form_data->tracking;
        $data->issue = $form_data->issue;
        $data->notes = $form_data->notes;
        $data->save();
        return 1;
    }

    public static function archive($id){
        $archived = carrierReturn::where('id', $id)->value('archived');
        return carrierReturn::where('id', $id)->update(['archived' => !$archived]);
    }
    
}