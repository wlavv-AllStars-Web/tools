<?php

namespace App\Models\modules\quotes;

use Illuminate\Database\Eloquent\Model;

use App\Models\prestashop\asm_dashboard;

class quotes extends Model
{
    protected $table = 'quotes';

    protected $fillable = [
        'referencia',
        'brand',
        'notas_front',
        'price',
        'lead',
        'notas_back',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public static function dashboard_quote_backoffice($type){

        $data = array();

        $ids_exceptions = [];
        $bd_data = self::select('id', 'brand', 'referencia', 'notas_front')->where('status', 'new')->get();
        $exceptions = asm_dashboard::getExceptions('quotes_back');

        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }    

        foreach($bd_data AS $item){
            if( !in_array($item['id'], $ids_exceptions) ) $data[] = ['clean' => $item['id'], 'id' => $item->id, 'brand' => $item->brand, 'reference' => $item->referencia, 'status' => strtoupper(str_replace('_', ' ', $item->status))];
        }
        
        return [
            'name'              => "QUOTE",
            'col'               => 4,
            'item_id'           => $type . '_product_requests_front',
            'columns'           => ['clean', 'id', 'brand', 'reference'],
            'exception_fields'  => ['quotes_back', 'id', 'brand', 'reference'],  
            'link'              => route('quotes.index', [1]),
            'prestashop'        => null,
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_quote_frontoffice($type){

        $data = array();

        $ids_exceptions = [];
        $bd_data = self::select('id', 'brand', 'referencia', 'notas_back')->whereNot('status', 'new')->get();
        $exceptions = asm_dashboard::getExceptions('quotes_front');


        foreach($exceptions AS $exception){
            $ids_exceptions[] = $exception->id_product;
        }    

        foreach($bd_data AS $item){
            if( !in_array($item['id'], $ids_exceptions) ) $data[] = ['clean' => $item['id'], 'id' => $item->id, 'brand' => $item->brand, 'reference' => $item->referencia, 'status' => strtoupper(str_replace('_', ' ', $item->status))];
        }
        
        return [
            'name'              => "PRODUCT REQUEST'S ( Frontoffice )",
            'col'               => 4,
            'item_id'           => $type . '_product_requests_back',
            'columns'           => ['clean', 'id', 'brand', 'reference'],
            'exception_fields'  => ['quotes_front', 'id', 'brand', 'reference'],  
            'link'              => route('quotes.index', [1]),
            'prestashop'        => null,
            'counter'           => count($data),
            'data'              => $data
        ];        
    }
    
}
