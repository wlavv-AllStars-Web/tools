<?php

namespace App\Models\modules\compats;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use App\Models\modules\compats\compats_options;
use App\Models\modules\compats\compats_product;

class compats extends Model
{
    use HasFactory;
    protected $table = "compats";
    protected $fillable = [
        'position',
    ];
    
    public $primaryKey = 'id_compat';

    public function brand(){        return $this->hasOne(compats_options::class, "id_option", 'id_brand'   ); }
    public function model(){        return $this->hasOne(compats_options::class, "id_option", 'id_model'   ); }
    public function type(){         return $this->hasOne(compats_options::class, "id_option", 'id_type'    ); }
    public function version(){      return $this->hasOne(compats_options::class, "id_option", 'id_version' ); }
    
    
    public static function getRelationshipFromCompat( $id_compat ){ return compats::where('id_compat', $id_compat)->get(); }

    public static function getCompatDetail( $id_compat ){ return compats::with('brand', 'model', 'type', 'version')->where('id_compat', $id_compat)->first(); }
    
    public static function getIdCompat( $id_brand, $id_model, $id_type, $id_version ){ return compats::select('id_compat')->where('id_brand', $id_brand)-where('id_compat', $id_model)-where('id_type', $id_type)-where('id_version', $id_version)->pluck('id_compat'); }
    
    public static function getAllCompats( ){ return compats::with('brand', 'model', 'type', 'version')->get(); }
    
    public static function createCompat( $id_brand, $id_model, $id_type, $id_version, $store=6 ){
        
        $new = new compats();
        $new->store = $store;
        $new->id_brand = $id_brand;
        $new->id_model = $id_model;
        $new->id_type = $id_type;
        $new->id_version = $id_version;
        $new->created_at = date('Y-m-d h:s:i');
        $new->updated_at = date('Y-m-d h:s:i');
        $new->save();
        
        return $new->id_compat;
    }


    /** API Functions **/
    public static function getBrands($store=0){
        
        $brands = [];

        $data = compats::select('compats.id_brand', 'compats_options.name')->join('compats_options', 'compats.id_brand', 'compats_options.id_option')->where('store', $store)->groupBy('id_brand')->orderBy('row', 'ASC')->orderBy('position', 'ASC')->get();
    
        foreach($data AS $brand){
        
            $brand_item = [
                'id_brand'          => $brand->id_brand,
                'name'              => $brand->name,
                'brand_logo'        => 'https://webtools.all-stars-motorsport.com/uploads/compats/brand/' . $brand->id_brand . '.png',
                'brand_hover_logo'  => 'https://webtools.all-stars-motorsport.com/uploads/compats/brand_hover/' . $brand->id_brand . '.png',                
            ];
            
            $brands[] = $brand_item;
            
        }
        
        return $brands;
    }

    public static function getModels($brand, $store=0){
        
        $models = [];
        
        $data = compats::select('compats.id_model', 'compats.row', 'compats.position', 'compats_options.name')->join('compats_options', 'compats.id_model', 'compats_options.id_option')->where('id_brand', $brand)->where('store', $store)->groupBy('id_model')->orderBy('position', 'ASC')->get();

        foreach($data AS $model){
        
            $model_item = [
                'id_brand'          => $brand,
                'id_model'          => $model->id_model,
                'row'               => $model->row,
                'position'          => $model->position,
                'name'              => $model->name               
            ];
            
            $models[] = $model_item;
            
        }
        
        return $models;
    }

    public static function getTypes($model, $store=0){
        
        $types = [];
        
        $data = compats::select('compats.id_type', 'compats_options.name')->join('compats_options', 'compats.id_type', 'compats_options.id_option')->where('id_model', $model)->where('store', $store)->groupBy('id_type')->get();

        foreach($data AS $type){
        
            $type_item = [
                'id_model'          => $model,
                'id_type'           => $type->id_type,
                'name'              => $type->name               
            ];
            
            $types[] = $type_item;
            
        }
        
        return $types;
    }

    public static function getVersions($type, $store=0){
        
        $versions = [];
        
        $data = compats::select('compats.id_version', 'compats_options.name')->join('compats_options', 'compats.id_version', 'compats_options.id_option')->where('id_type', $type)->where('store', $store)->groupBy('id_version')->get();

        foreach($data AS $version){
        
            $version_item = [
                'id_type'           => $type,
                'id_version'        => $version->id_version,
                'name'              => $version->name               
            ];
            
            $versions[] = $version_item;
            
        }
        
        return $versions;
    }

    public static function getCompatsFull($id_brand, $id_model, $id_type, $id_version, $store=0){
        
        $compats = [];
        
        $data = compats::with('brand', 'model', 'type', 'version')->where('id_brand', $id_brand)->where('id_model', $id_model)->where('id_type', $id_type)->where('id_version', $id_version)->where('store', $store)->orderBy('row')->orderBy('position')->get();

        foreach($data AS $compat){
            
            $item = [
                'id_compat' => $compat->id_compat,
                'row'       => $compat->row,
                'position'  => $compat->position,
                'id_brand'  => $id_brand,
                'brand'     => $compat['brand']['name'],
                'id_model'  => $id_model,
                'model'     => $compat['model']['name'],
                'type'      => $compat['type']['name'],
                'version'   => $compat['version']['name'],
                'brand_logo'=> 'https://webtools.all-stars-motorsport.com/uploads/compats/brand/' . $compat["brand"]['id_option'].'.png',
                'brand_hover_logo'=> 'https://webtools.all-stars-motorsport.com/uploads/compats/brand_hover/' . $compat["brand"]['id_option'].'.png',
                'cartoon'   => 'https://webtools.all-stars-motorsport.com/uploads/compats/compat/' . $compat->id_compat.'.png'
            ];
            
            $compats[] = $item;
        }

        return $compats;
    }
    
    public static function getProductCompatDetails($id_product, $store=0){

        $compats = compats_product::select('id_compat')->where('id_product', $id_product)->where('store', $store)->get();
        
        $compat_array = array();
        
        foreach($compats AS $compat){

            $data = compats::with('brand', 'model', 'type', 'version')->where('id_compat', $compat->id_compat)->where('store', $store)->first();
    
            $compat_array[] = (object)[
                'id_compat' => $data->id_compat,
                'row'       => $data->row,
                'position'  => $data->position,
                'id_brand'  => $data->brand->id_option,
                'id_model'  => $data->model->id_option,
                'id_type'   => $data->type->id_option,
                'id_version'=> $data->version->id_option,
                'brand'  => $data->brand->name,
                'model'  => $data->model->name,
                'type'   => $data->type->name,
                'version'=> $data->version->name,
                'brand_logo'=> 'https://webtools.all-stars-motorsport.com/uploads/compats/brand/' . $data->brand->id_option .'.png',
                'brand_hover_logo'=> 'https://webtools.all-stars-motorsport.com/uploads/compats/brand_hover/' . $data->brand->id_option .'.png',
                'cartoon'   => 'https://webtools.all-stars-motorsport.com/uploads/compats/compat/' . $compat->id_compat.'.png'
            ];
            
        }
        
        return $compat_array;
    }
    
    public static function getCompats($id_brand, $store=0){
        
        $compats = [];
        
        $data = compats::with('brand', 'model', 'type', 'version')->where('id_brand', $id_brand)->where('store', $store)->orderBy('row')->orderBy('position')->get();

        foreach($data AS $compat){
            
            $item = [
                'id_compat' => $compat->id_compat,
                'row'       => $compat->row,
                'position'  => $compat->position,
                'id_brand'  => $id_brand,
                'id_model'  => $compat['model']['id_option'],
                'brand'     => $compat['brand']['name'],
                'model'     => $compat['model']['name'],
                'type'      => $compat['type']['name'],
                'version'   => $compat['version']['name'],
                'brand_logo'=> 'https://webtools.all-stars-motorsport.com/uploads/compats/brand/' . $compat["brand"]['id_option'].'.png',
                'brand_hover_logo'=> 'https://webtools.all-stars-motorsport.com/uploads/compats/brand_hover/' . $compat["brand"]['id_option'].'.png',
                'cartoon'   => 'https://webtools.all-stars-motorsport.com/uploads/compats/compat/' . $compat->id_compat.'.png'
            ];
            
            $compats[] = $item;
        }

        return $compats;
    }
    
    public static function getAllCompatsBO($store=0){
        
        $compats = [];
        
        $data = compats::with('brand', 'model', 'type', 'version')->where('store', $store)->orderBy('position')->get();

        foreach($data AS $compat){
            
            $item = [
                'id_compat' => $compat->id_compat,
                'brand'     => $compat['brand']['name'],
                'model'     => $compat['model']['name'],
                'type'      => $compat['type']['name'],
                'version'   => $compat['version']['name'],
                'name'      => $compat['brand']['name'] . ' | ' . $compat['model']['name'] . ' | ' . $compat['type']['name'] . ' | ' . $compat['version']['name']
            ];
            
            $compats[] = $item;
        }
        
        return $compats;
    }
    
    public static function getCompatInfo($id_compat, $store=0){

        $compat = compats::with('brand', 'model', 'type', 'version')->where('store', $store)->where('id_compat', $id_compat)->first();
    
        return [
            'id_compat' => $id_compat,
            'brand'     => $compat->brand->name,
            'model'     => $compat->model->name,
            'type'      => $compat->type->name,
            'version'   => $compat->version->name,
            'brand_logo'=> 'https://webtools.all-stars-motorsport.com/uploads/compats/brand/' . $compat->id_brand . '.png',
            'brand_hover_logo'=> 'https://webtools.all-stars-motorsport.com/uploads/compats/brand_hover/' . $compat->id_brand . '.png',
            'cartoon'   => 'https://webtools.all-stars-motorsport.com/uploads/compats/compat/' . $id_compat . '.png'
        ];

    }
    
    public static function getBObrands($store=0){
        
        $brands = [];
        $data = compats::select('compats.id_brand', 'compats_options.name')->join('compats_options', 'compats.id_brand', 'compats_options.id_option')->where('store', $store)->groupBy('id_brand')->get();
    
        foreach($data AS $brand){ $brands[] = [ 'id_brand' => $brand->id_brand, 'name' => $brand->name ]; }
        return $brands;
    }
    
    public static function getBOmodels($brand, $store=0){
        
        $models = [];
        $data = compats::select('compats.id_model', 'compats_options.name')->join('compats_options', 'compats.id_model', 'compats_options.id_option')->where('store', $store)->where('id_brand', $brand)->groupBy('id_model')->get();
    
        foreach($data AS $model){ $models[] = [ 'id_model' => $model->id_model, 'name' => $model->name ]; }
        
        return $models;
    }
    
    public static function getBOtypes($brand, $model, $store=0){
        
        $types = [];
        $data = compats::select('compats.id_type', 'compats_options.name')->join('compats_options', 'compats.id_type', 'compats_options.id_option')->where('store', $store)->where('id_brand', $brand)->where('id_model', $model)->groupBy('id_type')->get();
    
        foreach($data AS $type){ $types[] = [ 'id_type' => $type->id_type, 'name' => $type->name ]; }
        return $types;
    }
    
    public static function getBOversions($brand, $model, $type, $store=0){
        
        $versions = [];
        $data = compats::select('compats.id_version', 'compats_options.name')->join('compats_options', 'compats.id_version', 'compats_options.id_option')->where('store', $store)->where('id_brand', $brand)->where('id_model', $model)->where('id_type', $type)->groupBy('id_version')->get();
    
        foreach($data AS $version){ $versions[] = [ 'id_version' => $version->id_version, 'name' => $version->name ]; }
        return $versions;
    }
    
    public static function getAllCompatsFromFilter($brand, $model, $type, $version, $store){

        $compats = compats::where('store', $store);
        
        if($brand > 0) $compats->where('id_brand', $brand);
        if($model > 0) $compats->where('id_model', $model);
        if($type > 0) $compats->where('id_type', $type);
        if($version > 0) $compats->where('id_version', $version);
        
        return $compats->get();
    }
    
}
