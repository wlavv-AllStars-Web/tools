<?php

namespace App\Models\modules\productIssues;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;
use App\Models\prestashop\manufacturers;

use Illuminate\Support\Facades\Config;

class productIssues extends Model
{
    use HasFactory;
    protected $table = "productIssues";
    public $primaryKey = 'id_issue';
    
public static function getProductIssues()
{
    $issues = productIssues::orderBy('date', 'DESC')->get();

    $issues = $issues->map(function ($issue) {
        $reference = $issue->reference;
        $path = "productIssues/{$reference}/{$issue->id_order}";
        $disk = Storage::disk('public_uploads');

        // Caminho absoluto
        $fullPath = $disk->path($path);

        // Criação recursiva controlada
        $dirs = explode('/', $path);
        $current = $disk->path('');

        foreach ($dirs as $dir) {
            $current = rtrim($current, '/') . '/' . $dir;
            if (!is_dir($current)) {
                mkdir($current, 0755);
            }
            @chmod($current, 0755);
        }

        // Corrige permissões dos ficheiros dentro da pasta final
        $files = $disk->files($path);
        foreach ($files as $file) {
            $filePath = $disk->path($file);
            if (file_exists($filePath)) {
                @chmod($filePath, 0644);
            }
        }

        // Buscar fabricante
        $product_attribute = product_attribute::select('id_product')
            ->where('reference', $reference)
            ->first();

        if (isset($product_attribute->id_product)) {
            $product = product::select('id_manufacturer')
                ->where('id_product', $product_attribute->id_product)
                ->first();
        } else {
            $product = product::select('id_manufacturer')
                ->where('reference', $reference)
                ->first();
        }

        if (isset($product->id_manufacturer)) {
            $manufacturer = manufacturers::select('name')
                ->where('id_manufacturer', $product->id_manufacturer)
                ->first();
            $manufacturer_name = $manufacturer->name ?? '<span style="color: red;"> UNKNOWN </span>';
        } else {
            $manufacturer_name = '<span style="color: red;"> UNKNOWN </span>';
        }

        $issue->files_count = count($files);
        $issue->files = $files;
        $issue->manufacturer = $manufacturer_name;

        return $issue;
    });

    return $issues;
}
    
    

    public static function saveData($form_data){
        
        $data = new productIssues();
        $data->reference = $form_data['reference'];
        $data->description = $form_data['description'];
        $data->car = $form_data['car'];
        $data->assembly = (isset($form_data['assembly'])) ? $form_data['assembly'] : 0;
        $data->compatibility = (isset($form_data['compatibility'])) ? $form_data['compatibility'] : 0;
        $data->defect = (isset($form_data['defect'])) ? $form_data['defect'] : 0;
        $data->date = $form_data['date'];
        $data->store = $form_data['shop'];
        $data->status = $form_data['status'];
        $data->conclusion = $form_data['conclusion'];
        $data->id_order = $form_data['id_order'];
        $data->save();

        return 1;
    }

    public static function updateData($form_data){
        
        $data = productIssues::where('id_issue', $form_data['id_issue'])->first();
        $data->reference = $form_data['reference'];
        $data->description = $form_data['description'];
        $data->car = $form_data['car'];
        $data->assembly = (isset($form_data['assembly'])) ? $form_data['assembly'] : 0;
        $data->compatibility = (isset($form_data['compatibility'])) ? $form_data['compatibility'] : 0;
        $data->defect = (isset($form_data['defect'])) ? $form_data['defect'] : 0;
        $data->date = $form_data['date'];
        $data->store = $form_data['shop'];
        $data->status = $form_data['status'];
        $data->conclusion = $form_data['conclusion'];
        $data->id_order = $form_data['id_order'];
        $data->save();
        
        return 1;
    }

    public static function getIssue($id){
        return productIssues::where('id_issue', $id)->first();
    }

    public static function dashboard_product_issues($type){

        $data = array();

        $bd_data = self::select('id_order', 'reference', 'description')->where('status', '<>', 'SOLVED')->get();

        foreach($bd_data AS $item) $data[] = ['id_order' => $item->id_order, 'reference' => $item->reference, 'description' => $item->description];
        
        return [
            'name'              => 'PRODUCT ISSUES',
            'col'               => 4,
            'item_id'           => $type . '_product_issues',
            'columns'           => ['id_order', 'reference', 'description'],
            'prestashop'        => ( isset ( Config::get('token')->AdminProducts ) ) ? [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ] : [],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }
    
}