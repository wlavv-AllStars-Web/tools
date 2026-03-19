<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Http;

use App\Models\prestashop\orders;
use App\Models\modules\ukoocompat\ukoocompat_compat_asm;

use App\Models\prestashop\orders_details;

use App\Models\modules\compats\compats;
use App\Models\modules\compats\compats_product;
use App\Models\modules\compats\compats_options;

class compatsController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    private static $key = '9860e1da0926ea371e69f2c19bbb1fe9';

    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('compats'), 'url' => route('compats.index')];
        $this->actions[]     = [];

    }

    public function index()
    {
        $data = [
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs,
            'options'    => compats_options::getByType(0, 1),
            'compats'    => compats::getAllCompats()
        ];
        return View::make('customTools/compats/index')->with($data);
    }

    public function getOptions(Request $request)
    {
        $type = $request->type;
        $id_option = $request->id_option;
        
        if($type < 5){
            $options = compats_options::getByType($id_option, $type);
            return view('customTools/compats/includes/options', compact('type', 'options'))->render();
        }
    
        $products = compats_product::getProductIds( (object)$request->all() );
        return view('customTools/compats/includes/products', compact('products'))->render();
        
    }
    
    
    public function getOptionsForModal(Request $request)
    {
        $type = $request->type;
        $options = compats_options::getOptionsOfByType($type);
        return view('customTools/compats/includes/optionsForModal', compact('type', 'options'))->render();
    }

    public function createCompatibilities(Request $request){
        
        compats_product::insertNestedCompat( (object)$request->all() );
        return 1;
        
    }

    public function saveNewRelationship(Request $request){
        compats_options::newOption((object)$request->all());
        return 1;
    }

    public function updateTag(Request $request){
        compats_options::where('id_option', $request->id_option)->update(['slug' => str_replace(' ', '', $request->newTag), 'name' => $request->newTag ]);
        
    }

    public function setData(Request $request){
        compats::where('id_compat', $request->id_compat)->update([$request->type =>$request->value ]);
    }

    public function editImage(Request $request){
        compats_options::updateImage((object)$request->all());
        return 1;
    }

    public function removeCompat(Request $request){
        compats::where('id_compat', $request->id)->delete();
        return 1;
    }

    public function updateMenu(){
        
        $brands = array();
        //$brands_sql = compats::join('compats_options', 'id_option', 'id_brand')->with('brand')->groupBy('id_brand')->orderBy('name', 'ASC')->get();
        $brands_sql = compats::with('brand')->groupBy('id_brand')->orderBy('position', 'ASC')->orderBy('row', 'ASC')->get();

        foreach($brands_sql AS $data){
            
            $models = array();
            $models_sql = compats::with('model')->where('id_brand', $data->id_brand)->groupBy('id_model')->orderBy('position', 'ASC')->orderBy('row', 'ASC')->get();
            
            foreach($models_sql AS $data_model){
            
                $types = array();
                $type_sql = compats::with('type')->where('id_model', $data_model->id_model)->groupBy('id_version')->orderBy('position', 'ASC')->orderBy('row', 'ASC')->get();
                
                foreach($type_sql AS $data_type){

                    $versions = array();
                    $version_sql = compats::with('version')->where('id_type', $data_type->id_type)->orderBy('position', 'ASC')->orderBy('row', 'ASC')->get();
                    
                    foreach($version_sql AS $data_version){
                        $versions[$data_version->id_version] = (object)[
                            'id' => $data_version->id_version,
                            'id_compat' => $data_version->id_compat,
                            'name' => $data_version->version->name,
                            'row' => $data_version->row,
                            'position' => $data_version->position,
                        ];
                    }
                    
                    $types[$data_type->id_type] = (object)[
                        'id' => $data_type->id_type,
                        'id_compat' => $data_type->id_compat,
                        'name' => $data_type->type->name,
                        'row' => $data_type->row,
                        'position' => $data_type->position,
                        'versions' => $versions
                    ];
                    
                }
                
                $models[$data_model->id_model] = (object)[
                    'id' => $data_model->id_model,
                    'id_compat' => $data_model->id_compat,
                    'name' => $data_model->model->name,
                    'row' => $data_model->row,
                    'position' => $data_model->position,
                    'types' => $types,
                ];
            }

            $brands[$data->id_brand] = (object)[
                'id' => $data->id_brand,
                'id_compat' => $data->id_compat,
                'name' => $data->brand->name,
                'row' => $data->row,
                'position' => $data->position,
                'models' => $models,
            ];
                
        }

        $this->breadcrumbs[] = [ 'name' =>  trans('compats.updateMenu'), 'url' => route('compats.updateMenu')];
        
        $data = [
            'breadcrumbs'=> $this->breadcrumbs,
            'structure' => $brands
        ];

        return View::make('customTools/compats/menu')->with($data);
    }

    public function setOrder(Request $request){

        $data = $request->all();

        foreach($data['dataInfo'] AS $element){
            
            if( (  $element['type'] == 'brand' ) || ( $element['type'] == 'model' ) ){
                $compat = compats::where('id_compat', $element['id_compat'])->first();
                
                $id_model = $compat->id_model;
                $compat = compats::where('id_model', $id_model)->update(['row' => $element['row']]);

            }else{
                $compat = compats::where('id_compat', $element['id_compat'])->first();
                $compat->position = $element['row'];
                $compat->save();
            }
        }
        
        return 1;
    }
    
}