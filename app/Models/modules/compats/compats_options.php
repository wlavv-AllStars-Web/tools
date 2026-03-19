<?php

namespace App\Models\modules\compats;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\modules\compats\compats;

class compats_options extends Model
{
    use HasFactory;
    protected $table = "compats_options";
    public $primaryKey = 'id_option';

    public static function checkForSlug($slug, $type){
        
        $exist = compats_options::where('slug', $slug)->where('type', $type)->count();
        
        if($exist > 0){
            $data = compats_options::where('slug', $slug)->where('type', $type)->first()->toArray();
            $exist = true;
        }else{
            $data = null;
            $exist = false;
        }
        
        return [ 'exist' => $exist, 'data'  => $data ];
    }
    
    public static function getByType($id_option, $type){
        return compats_options::where('id_parent', $id_option)->where('type', $type)->orderBy('name', 'ASC')->get();
    }
    
    public static function getOptionsOfByType($type){
        return compats_options::where('type', $type)->get();
    }
    
    public static function newOption($data){

        $option = new compats_options();
        $option->id_parent = (isset($data->id_parent)) ? $data->id_parent : 0;
        $option->type = $data->type + 1;
        $option->slug = str_replace(' ', '', $data->en);
        $option->name = $data->en;
        $option->created_at = date('Y-m-d h:s:i');
        $option->updated_at = date('Y-m-d h:s:i');
        $option->save();

        if($data->type == 0){
            $from = base_path('public/uploads/compats/image.png');
            $to = base_path('public/uploads/compats/brand/' . $option->id_option . '.png');
            
            self::mycopy($from, $to);             
        }
        
        if($data->type == 3){
            
            $type = compats_options::select('id_option', 'id_parent')->where('id_option', '=', $data->id_parent)->first();
            $model = compats_options::select('id_option', 'id_parent')->where('id_option', '=', $type->id_parent)->first();
            $brand = compats_options::select('id_option', 'id_parent')->where('id_option', '=', $model->id_parent)->first();
            $compat = compats::createCompat( $brand->id_option, $model->id_option, $type->id_option, $option->id_option );

            $from = base_path('public/uploads/compats/image.png');
            $to = base_path('public/uploads/compats/compat/' . $compat . '.png');
            
            self::mycopy($from, $to);    
            
        }
        
        return 1;
    }
    
    public static function updateImage($data){
        
        $from = base_path('public/uploads/compats/image.png');
        $to = '';

        if($data->element == 'logo')    $to = base_path('public/uploads/compats/brand/' . $data->id . '.png');
        if($data->element == 'hover')   $to = base_path('public/uploads/compats/brand_hover/' . $data->id . '.png');
        if($data->element == 'cartoon') $to = base_path('public/uploads/compats/compat/' . $data->id . '.png');
        
        if( strlen( $to ) > 1) self::mycopy($from, $to);             
        
        return 1;
    }
    
    private static function mycopy($s1, $s2) {
        $path = pathinfo($s2);
        if (!file_exists($path['dirname'])) mkdir($path['dirname'], 0777, true);
        if (copy($s1, $s2)) unlink($s1);
    }
}