<?php

namespace App\Models\modules\documents_manager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class documents_manager extends Model
{
    use HasFactory;
    protected $table = "documents_manager";
    public $timestamps = false;
    public $primaryKey = 'id_document';
        
    public static function forCategoryElement($id_category = null, $id_element = null){
        
        if( (!is_null($id_category)) && ( is_null($id_element) ))  return self::where('category', $id_category)->orderBy('id_document', 'DESC')->get();
        if( (is_null($id_category)) && ( !is_null($id_element) ))  return self::where('element', $id_element)->orderBy('id_document', 'DESC')->get();
        if( (!is_null($id_category)) && ( !is_null($id_element) )) return self::where('element', $id_element)->where('category', $id_category)->orderBy('id_document', 'DESC')->get();
        
        return self::orderBy('id_document', 'DESC')->take(30)->get();
    }
        
    public static function saveData($data){
        
        $from = base_path('public/uploads/documents/upload.pdf');
        
        $number = '';
        
        if( strlen($data['number']) > 0){
            $number = str_replace('/', '|', $data['number']);
        }else{
            $number = $data['category'];
        }
        
        $element = '';
        if( $data['category'] == 'manifest'){
            $element = 'others';
        }else{
            $element = (isset($data[$data['category']])) ? $data[$data['category']] : '';
        }

        $storedDocumentName = $data['number'] . '_' . $data['year'] . '_' . $data['month'] . '_' . $data['day'] . '.pdf';
        $to = self::documentAbsolutePath((object) [
            'category' => $data['category'],
            'element' => $element,
            'document' => $storedDocumentName,
        ]);

        self::mycopy($from, $to);    

        $document = new documents_manager();
        $document->name = $data['name'];
        $document->document_number = $data['number'];
        $document->year = $data['year'];
        $document->month = $data['month'];
        $document->day = $data['day'];
        $document->total_ex_vat = str_replace(' ', '', $data['totalExVAT'] ?? 0);
        $document->total_vat = str_replace(' ', '', $data['vat'] ?? 0);
        $document->total = str_replace(' ', '', $data['total'] ?? 0);
        $document->category = $data['category'];
        $document->element = addslashes( str_replace( ['č'], 'c', $element));
        $document->document_type = $data['document_type'];
        $document->document = $storedDocumentName;
        $document->notes = $data['notes'] ?? '';
        $document->DPD = ($data['dpd'] ?? 0) + 0;
        $document->TNT = ($data['tnt'] ?? 0) + 0;
        $document->GLS = ($data['gls'] ?? 0) + 0;
        $document->UPS = ($data['ups'] ?? 0) + 0;
        $document->NACEX = ($data['nacex'] ?? 0) + 0;
        $document->date_add = now();
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
        
        $rows = self::query()
            ->select('category', 'element')
            ->groupBy('category', 'element')
            ->orderBy('category', 'ASC')
            ->orderBy('element', 'ASC')
            ->get();
        
        foreach($rows->groupBy('category') AS $category => $elements){
            $element_array = $elements
                ->pluck('element')
                ->filter(fn ($element) => strlen((string) $element) > 0)
                ->values()
                ->all();
            
            $filters[] = [
                'category' => $category,
                'elements' => $element_array
            ];
            
        }
        
        return $filters;
    }
    
    public static function search($data){
        $tag = trim((string) ($data['tag'] ?? ''));

        if ($tag === '') {
            return collect();
        }

        return self::where('name', 'LIKE', '%' . $tag . '%')
            ->orWhere('notes', 'LIKE', '%' . $tag . '%')
            ->orWhere('document_number', 'LIKE', '%' . $tag . '%')
            ->orWhere('element', 'LIKE', '%' . $tag . '%')
            ->orWhere(DB::raw('CONCAT(year, "-", month,"-", day)'), 'LIKE', '%' . $tag . '%')
            ->orderBy('id_document', 'DESC')
            ->take(30)
            ->get();
    }
    
    public static function listSearchData($data){
        
        $search = self::where('id_document', '>', 0);
        
        if( isset($data['name']) && ( strlen($data['name']) > 0) ) $search->where('name', 'LIKE', '%' . $data['name'] . '%');
        if( isset($data['number']) && ( strlen($data['number']) > 0) ) $search->where('document_number', 'LIKE', '%' . $data['number'] . '%');
        if( isset($data['category']) && ( strlen($data['category']) > 0) ) $search->where('category', 'LIKE', '%' . $data['category'] . '%');
        if( isset($data['date']) && ( strlen($data['date']) > 0) ) $search->where(DB::raw('CONCAT(year, "-", month,"-", day)'), 'LIKE', '%' . $data['date'] . '%');

        return $search->orderBy('id_document', 'DESC')->take(30)->get();
    }

    public static function loadDocumentData($id_document){
        return self::where('id_document', $id_document)->first();
    }

    public static function destroyData($data){
        
        $document = self::where('id_document', $data['id_document'])->first();

        if (!$document) {
            return 0;
        }

        $from = self::documentAbsolutePath($document);
         
        if (file_exists($from)) unlink($from); 
        
        $document->delete();
        
        return 1;
    }

    public static function documentPublicPath($document): string
    {
        $category = trim((string) $document->category, '/');
        $element = (string) $document->element;
        $filename = self::legacyDocumentFilename((string) $document->document);

        if ($category === 'manifest' && $element === 'others') {
            $manifestFilename = str_starts_with($filename, 'manifest') ? $filename : 'manifest' . $filename;
            return '/uploads/documents/manifest/' . $manifestFilename;
        }

        if (strlen($element) > 0 && $element !== 'others') {
            $candidatePaths = [
                '/uploads/documents/' . $category . '/' . self::legacyElementPath($element) . '/' . $filename,
                '/uploads/documents/' . $category . '/' . self::legacyElementPath($element) . '/' . self::sanitizePathSegment((string) $document->document),
                '/uploads/documents/' . $category . '/' . self::sanitizeElementPath($element) . '/' . $filename,
                '/uploads/documents/' . $category . '/' . self::sanitizeElementPath($element) . '/' . self::sanitizePathSegment((string) $document->document),
            ];

            foreach ($candidatePaths as $candidatePath) {
                if (file_exists(public_path(ltrim($candidatePath, '/')))) {
                    return $candidatePath;
                }
            }

            return $candidatePaths[0];
        }

        $legacyPath = '/uploads/documents/' . str_replace('.', '/', $category) . '/' . $filename;
        if (file_exists(public_path(ltrim($legacyPath, '/')))) {
            return $legacyPath;
        }

        $fallbackPath = '/uploads/documents/' . str_replace('.', '/', $category) . '/' . self::sanitizePathSegment((string) $document->document);
        if (file_exists(public_path(ltrim($fallbackPath, '/')))) {
            return $fallbackPath;
        }

        return $legacyPath;
    }

    public static function documentAbsolutePath($document): string
    {
        return public_path(ltrim(self::documentPublicPath($document), '/'));
    }

    private static function sanitizePathSegment(string $value): string
    {
        return str_replace(['/', '\\', '|'], '_', $value);
    }

    private static function legacyDocumentFilename(string $value): string
    {
        return str_replace(['/', '\\'], ['|', '_'], $value);
    }

    private static function sanitizeElementPath(string $value): string
    {
        $value = str_replace(' ', '', $value);
        $value = str_replace(['\\', '|'], ['/', '_'], $value);

        return str_replace('.', '/', $value);
    }

    private static function legacyElementPath(string $value): string
    {
        return str_replace('.', '/', str_replace(' ', '', $value));
    }
    
    
}
