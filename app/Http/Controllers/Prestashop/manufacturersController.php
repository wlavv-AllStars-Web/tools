<?php

namespace App\Http\Controllers\Prestashop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Models\prestashop\manufacturers;
use App\Models\prestashop\manufacturers_lang;
use App\Models\prestashop\country;
use App\Models\prestashop\language;
use App\Models\prestashop\currency;

class manufacturersController extends Controller
{

    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('messages.Manufacturers'), 'url' => route('manufacturers.index')];
    }

    public function index()
    {
        $manufacturers = manufacturers::get();
        
        $this->actions[]     = [ 'name' => trans('messages.Add manufacturer'), 'icon' => '<i class="fa fa-add"></i>', 'url' => '#', 'class' => "btn btn-success"];

        $data = [
            'manufacturers'   => $manufacturers,
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs
        ];

        return View::make('prestashop/manufacturers/index')->with($data);
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->actions[]     = [ 'name' => 'Edit', 'icon' => '<i class="fa fa-edit"></i>', 'url' => route('manufacturers.edit', $id), 'class' => "btn btn-warning"];
        $this->breadcrumbs[] = [ 'name' => 'Manufacturers info', 'url' => route('manufacturers.show', $id)];

        $manufacturer = manufacturers::with('lang')->where('id_manufacturer', $id)->first();

        $data = [
            'manufacturer'=> $manufacturer,
            'actions'     => $this->actions,
            'breadcrumbs' => $this->breadcrumbs
        ];

        return View::make('prestashop/manufacturers/show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
 
        $this->actions[]     = [ 'name' => 'Save', 'icon' => '<i class="fa fa-save"></i>', 'url' => "", 'class' => "btn btn-primary", 'onclick' => "$('#manufacturersForm').submit()"];
        $this->breadcrumbs[] = [ 'name' => 'Manufacturers edit', 'url' => route('manufacturers.store')];

        $manufacturer = manufacturers::with('lang')->where('id_manufacturer', $id)->first();
        $countries = country::get();
        $language = language::get();
        $currencies = currency::get();
        
        $data = [
            'manufacturer' => $manufacturer,
            'countries'    => $countries,
            'language'     => $language,
            'currencies'   => $currencies,
            'actions'      => $this->actions,
            'breadcrumbs'  => $this->breadcrumbs
        ];

        return View::make('prestashop/manufacturers/edit')->with($data);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        manufacturers::where('id_manufacturer', $id)->update(
            [
                'name'          => $request->get('name'),
                'active'        => $request->get('active'),
                'warranty'      => $request->get('warranty'),
                'country_code'  => $request->get('country_code'),
                'currency'      => $request->get('currency'),
                'date_upd'      => date('Y-m-d H:i:s')
            ]
        );
        
        $languages = language::get();

        foreach( $languages AS $language){

            manufacturers_lang::where('id_manufacturer', $id)->where('id_lang', $language->id_lang)->update(
                [
                    'description'      => $request->get('description')[$language->id_lang],
                    'short_description'=> $request->get('short_description')[$language->id_lang],
                    'meta_title'       => $request->get('meta_title')[$language->id_lang],
                    'meta_keywords'    => $request->get('meta_keywords')[$language->id_lang],
                    'meta_description' => $request->get('meta_description')[$language->id_lang]
                ]
            );

        }

        return redirect(route('manufacturers.show', $id));
    }

    private function cleanTempFolder(){
        
        $folder_path = "/home/allstar1/webtools/public/uploads/manufacturer/temp"; 

        $files = glob($folder_path.'/*');  
           
        foreach($files as $file) { 
            if(is_file($file))  unlink($file);  
        } 

        if (file_exists($folder_path . '/images/')) {
            $files = glob($folder_path.'/images/*');  
               
            foreach($files as $file) { 
                if(is_file($file))  unlink($file);  
            }
            
            rmdir($folder_path . '/images');
        }
    }
    
    public function resources(){
        
        self::cleanTempFolder();
        
        $manufacturers = manufacturers::orderBy('name', 'asc')->get();
        
        $this->actions[]     = [ 'name' => trans('messages.Add manufacturer'), 'icon' => '<i class="fa fa-add"></i>', 'url' => '#', 'class' => "btn btn-success"];
        
        $stores = (object)[
            'ASD' => (object)[
                'name' => 'ALL STARS DISTRIBUTION',
                'color' => 'dodgerblue',
                'domain' => 'https://www.all-stars-distribution.com',
                'logo' => '/images/logos/asd.png',
                'disabled' => 0
                ],
            /**'EMP' => (object)[
                'name' => 'EURO MUSCLE PARTS',
                'color' => 'blue',
                'domain' => 'https://www.euromuscleparts.com',
                'logo' => '/images/logos/emp.png',
                'disabled' => 0
                ],
            'ER' => (object)[
                'name' => 'EURO RIDER',
                'color' => 'darkgreen',
                'domain' => 'https://www.euro-rider.com',
                'logo' => '/images/logos/er.png',
                'disabled' => 0
                ],
            'ASM' => (object)[
                'name' => 'ALL STARS MOTORSPORT <br>( upload on product creation )',
                'color' => 'red',
                'domain' => '#',
                'logo' => '/images/logos/asm.png',
                'disabled' => 1
                ],**/
        ];

        $data = [
            'manufacturers'   => $manufacturers,
            'stores'          => $stores,
            'htmlAfterUpload' => ''
        ];

        return View::make('prestashop/manufacturers/resources')->with($data);

    }
    
    private function clean($string) {
        $string = str_replace(' ', '', $string);
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
        
        return preg_replace('/-+/', '-', $string);
    }

    public function ressourcesPost(Request $request)
    {
        $folder_path = "/home/allstar1/webtools/public/uploads/manufacturer"; 
        $temp_folder_path = "/home/allstar1/webtools/public/uploads/manufacturer/temp"; 
        
        $manufacturer = manufacturers::where('id_manufacturer', $request->manufacturer)->first();
        
        $name = self::clean($manufacturer->name);

        if (!file_exists($folder_path . '/' . $request->store . '/' . $name)) {
            mkdir($folder_path . '/' . $request->store . '/' . $name, 0777, true);
            mkdir($folder_path . '/' . $request->store . '/' . $name . '/thumb/', 0777, true);
            mkdir($folder_path . '/' . $request->store . '/' . $name . '/600/', 0777, true);
        }
        
        $manufacturer_path = $folder_path . '/' . $request->store . '/' . $name;
        
        if (file_exists($temp_folder_path . '/catalogue.xlsx')) {
            rename($temp_folder_path . '/catalogue.xlsx', $manufacturer_path . '/' . $name .'.xlsx');
        }
        
        if (file_exists($temp_folder_path . '/csv.csv')) {
            rename($temp_folder_path . '/csv.csv', $manufacturer_path . '/' . $name .'.csv');
        }
        
        if (file_exists($temp_folder_path . '/images.zip')) {
            
            exec('unzip ' . $temp_folder_path . '/images.zip -d ' . $temp_folder_path . '/images/');
            
            $files = glob($temp_folder_path . "/images/*.*");
            foreach($files as $file){
                
                $path = str_replace("/temp/","/" .$request->store . '/'. $name . "/",$file);
                $path_600 = str_replace("/images/","/600/",$path);
                $path_thumb = str_replace("/images/","/thumb/",$path);
                copy($file, $path_600);
                copy($file, $path_thumb);
                
            }
            
            $thumb_images = glob($folder_path . '/' . $request->store . '/' . $name . '/thumb/*.*');
            foreach ($thumb_images as $filename) {

                $source = imagecreatefromjpeg($filename);
                list($width, $height) = getimagesize($filename);

                $thumb = imagecreatetruecolor(125, 125);
                imagecopyresized($thumb, $source, 0, 0, 0, 0, 125, 125, $width, $height);
                imagejpeg($thumb, $filename, 100);
            }

            rename($temp_folder_path . '/images.zip', $manufacturer_path . '/' . $name . '_images.zip');
        }
        
        if (file_exists($temp_folder_path . '/logos.zip')) {
            rename($temp_folder_path . '/logos.zip', $manufacturer_path . '/' . $name . '_logos.zip');
        }
        
        exit;
        
    }
}
