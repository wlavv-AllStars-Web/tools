<?php

namespace App\Models\modules\documents_manager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class documents_manager extends Model
{
    use HasFactory;
    protected $table = "documents_manager";
    public $timestamps = false;
    public $primaryKey = 'id_document';
        
    public static function all($id_category = null, $id_element = null){
        
        if( (!is_null($id_category)) && ( is_null($id_element) ))  return self::where('category', $id_category)->orderBy('id_document', 'DESC')->get();
        if( (is_null($id_category)) && ( !is_null($id_element) ))  return self::where('element', $id_element)->orderBy('id_document', 'DESC')->get();
        if( (!is_null($id_category)) && ( !is_null($id_element) )) return self::where('element', $id_element)->where('category', $id_category)->orderBy('id_document', 'DESC')->get();
        
        return self::take(30)->get();
    }
        
    public static function saveData($data){
        
        $from = base_path('public/uploads/documents/upload.pdf');
        
        $number = '';
        
        if( strlen($data['number']) > 0){
            $number = str_replace('/', '|', $data['number']);
        }else{
            $number = $data['category'];
        }
        
        if ( isset($data[$data['category']]) ){
            $to = base_path('public/uploads/documents/' . $data['category'] . '/' . str_replace('.', '/', str_replace(' ', '', $data[$data['category']])) . '/' . $number . '_' . $data['year'] . '_' . $data['month'] . '_' . $data['day'] . '.pdf');
        }else{
            $to = base_path('public/uploads/documents/' . $data['category'] . '/' . $number . '_' . $data['year'] . '_' . $data['month'] . '_' . $data['day'] . '.pdf');
        }
        self::mycopy($from, $to);    

        $element = '';
        if( $data['category'] == 'manifest'){
            $element = 'others';
        }else{
            $element = (isset($data[$data['category']])) ? $data[$data['category']] : '';
        }
        
        $document = new documents_manager();
        $document->name = $data['name'];
        $document->document_number = $data['number'];
        $document->year = $data['year'];
        $document->month = $data['month'];
        $document->day = $data['day'];
        $document->total_ex_vat = str_replace(' ', '', $data['totalExVAT']);
        $document->total_vat = str_replace(' ', '', $data['vat']);
        $document->total = str_replace(' ', '', $data['total']);
        $document->category = $data['category'];
        $document->element = addslashes( str_replace( ['č'], 'c', $element));
        $document->document_type = $data['document_type'];
        $document->document = $data['number'] . '_' . $data['year'] . '_' . $data['month'] . '_' . $data['day'] . '.pdf';
        $document->notes = $data['notes'];
        $document->dpd = $data['dpd'] + 0;
        $document->tnt = $data['tnt'] + 0;
        $document->gls = $data['gls'] + 0;
        $document->ups = $data['ups'] + 0;
        $document->nacex = $data['nacex'] + 0;
        $document->save();
        
        if (file_exists($from)) unlink($from);

        return 1;
    }
    
    private static function mycopy($s1, $s2) {
        
        $path = pathinfo($s2);
        if (!file_exists($path['dirname'])) mkdir($path['dirname'], 0777, true);
        
        if (copy($s1, $s2)) unlink($s1);
        
    }
    
    public static function getFilterStructure(){
        
        $filters = array();
        
        $categories = self::select('category')->groupBy('category')->orderBy('category', 'ASC')->get();
        
        foreach( $categories AS $category){

            $elements = self::select('element')->where('category', $category->category)->groupBy('element')->orderBy('element', 'ASC')->get();
            
            $element_array = array();
            foreach($elements AS $element){
                if( ( strlen( $element->element ) > 0)) $element_array[] = $element->element;
            }
            
            $filters[] = [
                'category' => $category->category,
                'elements' => $element_array
            ];
            
        }
        
        return $filters;
    }
    
    public static function search($data){
        return self::where('name', 'LIKE', '%' . $data['tag'] . '%')->orWhere('notes', 'LIKE', '%' . $data['tag'] . '%')->orWhere('document_number', $data['tag'])->orWhere('element', $data['tag'])->orWhere(DB::raw('CONCAT(year, "-", month,"-", day)'), $data['tag'])->take(30)->get();
    }
    
    public static function listSearchData($data){
        
        $search = self::where('id_document', '>', 0);
        
        if( isset($data['name']) && ( strlen($data['name']) > 0) ) $search->where('name', 'LIKE', '%' . $data['name'] . '%');
        if( isset($data['number']) && ( strlen($data['number']) > 0) ) $search->where('document_number', 'LIKE', '%' . $data['number'] . '%');
        if( isset($data['category']) && ( strlen($data['category']) > 0) ) $search->where('category', 'LIKE', '%' . $data['category'] . '%');
        if( isset($data['date']) && ( strlen($data['date']) > 0) ) $search->where(DB::raw('CONCAT(year, "-", month,"-", day)'), 'LIKE', '%' . $data['date'] . '%');

        return $search->take(30)->get();
    }

    public static function loadDocumentData($id_document){
        return self::where('id_document', $id_document)->first();
    }

    public static function destroyData($data){
        
        $document = self::where('id_document', $data['id_document'])->first();
        
        if ( ( strlen($document->element) > 0 ) && ( $document->element != 'others' ) ){
            $from = base_path('public/uploads/documents/' . $document->category . '/' . str_replace('.', '/', str_replace(' ', '', $document->element)) . '/' . $document->number . '_' . $document->year . '_' . $document->month . '_' . $document->day . '.pdf');
        }else{
            $from = base_path('public/uploads/documents/' . $document->category . '/' . $document->number . '_' . $document->year . '_' . $document->month . '_' . $document->day . '.pdf');
        }
         
        if (file_exists($from)) unlink($from); 
        
        $document->delete();
        
        return 1;
    }
    
    
}