<?php

namespace App\Models\modules\supplier_map;

use App\Models\prestashop\suppliers;
use App\Models\prestashop\manufacturers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class supplier_map extends Model
{
    use HasFactory;
    protected $table = "supplier_map";

    public function supplier(){
        return $this->hasOne(suppliers::class, "id_supplier", 'id_supplier'); 
    }

    public function manufacturer(){
        return $this->hasOne(manufacturers::class, "id_manufacturer", 'id_manufacturer'); 
    }

    public static function getAll( ){ 
        
        $manufacturers = manufacturers::orderBy('name', 'ASC')->get();

        $manufacturers_list = array();
        
        $id_supplier_map = array();
        
        foreach($manufacturers AS $manufacturer){
            
            $supplier_map = supplier_map::with('supplier', 'manufacturer')->where('id_manufacturer', $manufacturer->id_manufacturer)->first();

            if(!is_null($supplier_map)){
                $id_supplier_map[] = $supplier_map->id;
                $manufacturers_list[] = [
                    'id_manufacturer' => $manufacturer->id_manufacturer,
                    'name' =>$manufacturer->name,
                    'supplier_map' => $supplier_map,
                ];
            }
        }
        
        $supplier_map_extra = supplier_map::with('supplier', 'manufacturer')->whereNotIn('id', $id_supplier_map)->get();
        
        foreach($supplier_map_extra AS $manufacturer){
            
            $supplier_map = supplier_map::with('supplier', 'manufacturer')->where('id', $manufacturer->id)->first();
            if (is_null($supplier_map) || is_null($supplier_map->supplier)) {
                continue;
            }
            $id_supplier_map[] = $supplier_map->id;
            $manufacturers_list[] = [
                'id_manufacturer' => 0,
                'name' =>$supplier_map->supplier->name,
                'supplier_map' => $supplier_map,
            ];
        }
    
        
        return $manufacturers_list;
    }
    
    public static function getData( $request ){ 
        return supplier_map::where('id', $request->id)->first();
    }
    
    public static function saveData( $request ){ 
        
        $supplier_map = supplier_map::where('id_manufacturer', $request->id_manufacturer)->where('id_supplier', $request->id_supplier)->first();
        
        if(!isset($supplier_map)){
            $supplier_map = new supplier_map();
            $supplier_map->id_manufacturer = $request->id_manufacturer;
            $supplier_map->id_supplier  = $request->id_supplier ;
        }
            
        $supplier_map->manufacturer_125 = (isset($request->manufacturer_125)) ? $request->manufacturer_125 : 0;
        $supplier_map->manufacturer_600 = (isset($request->manufacturer_600)) ? $request->manufacturer_600 : 0;
        $supplier_map->supplier_125 = (isset($request->supplier_125)) ? $request->supplier_125 : 0;
        $supplier_map->supplier_600 = (isset($request->manufacturer_600)) ? $request->supplier_600 : 0;
        $supplier_map->video = (isset($request->video)) ? $request->video : 0;
        
        $supplier_map->asm = (isset($request->asm)) ? $request->asm : 0;
        $supplier_map->asd = (isset($request->asd)) ? $request->asd : 0;
        $supplier_map->em = (isset($request->em)) ? $request->em : 0;
        $supplier_map->er = (isset($request->er)) ? $request->er : 0;
        
        $supplier_map->warranty = (isset($request->warranty)) ? $request->warranty : 0;
        $supplier_map->description = (isset($request->description)) ? $request->description : 0;
        $supplier_map->ean13 = (isset($request->ean13)) ? $request->ean13 : 0;
        
        
        $supplier_map->dealer_website = (isset($request->dealer_website)) ? $request->dealer_website : 'N/D';
        $supplier_map->contact = $request->contact;
        $supplier_map->email = $request->email;
        $supplier_map->address = $request->address;
        $supplier_map->country = $request->country;
        $supplier_map->phone = $request->phone;
        $supplier_map->website = $request->website;
        $supplier_map->b2b_link = $request->b2b_link;
        $supplier_map->b2b_username = $request->b2b_username;
        $supplier_map->b2b_password = $request->b2b_password;
        $supplier_map->pics = $request->pics;
        $supplier_map->catalogue = $request->catalogue;
        $supplier_map->instructions = $request->instructions;
        $supplier_map->inventory = $request->inventory;
        $supplier_map->incoterm = $request->incoterm;
        $supplier_map->currency = $request->currency;
        $supplier_map->discount = $request->discount;
        $supplier_map->terms = $request->terms;
        $supplier_map->iban = $request->iban;
        $supplier_map->swift = $request->swift;
        
        $supplier_map->save();

        return 1;
    }
}

