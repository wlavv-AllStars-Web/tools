<?php

namespace App\Models\modules\price_map;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class price_map extends Model
{
    use HasFactory;
    protected $table = "price_map";

    public static function getAll( ){ 
        return price_map::get();
    }

    public static function emptyTableFor( $id_manufacturer ){ 
        return price_map::where('id_manufacturer', $id_manufacturer)->delete();
    }

    public static function getIDsManufacturer( ){ 
        $ids = price_map::groupBy('id_manufacturer')->pluck('id_manufacturer','id');
        
        $maxKey = $ids->keys()->max();
        $ids->forget($maxKey);
        
        return $ids;
    }
    
    public static function saveData( $id_manufacturer, $manufacturer, $data ){ 
        
        $price_map = new price_map();
        $price_map->id_manufacturer = $id_manufacturer;
        $price_map->manufacturer = $manufacturer;
        $price_map->reference = $data['asm_reference'];
        $price_map->asm_price = $data['asm_price'];
        $price_map->asm_wholesale_price = $data['asm_wholesale_price'];
        $price_map->asm_active = $data['asm_active'];
        $price_map->asm_deprecated = $data['asm_deprecated'];
        $price_map->asm_discount = $data['asm_discount'];
        $price_map->asm_racio = (!isset( $product_compare['asm_racio'] )) ? 0 : $data['asm_racio'];
        $price_map->asd_price = $data['asd_price'];
        $price_map->asd_wholesale_price = $data['asd_wholesale_price'];
        $price_map->asd_active = $data['asd_active'];
        $price_map->asd_deprecated = $data['asd_deprecated'];
        $price_map->asd_discount = $data['asd_discount'];
        $price_map->asd_racio = $data['asd_racio'];
        $price_map->save();

        return 1;
    }
}
