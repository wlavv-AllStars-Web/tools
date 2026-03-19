<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ASD_missing_images extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'ASD_missing_images';
    
    public function __construct(){ }

    public static function getExceptions($board)
    {
        return self::select('id_product')->where('board', $board)->get();
    }

    public static function addMissingImages()
    {

        $data = [];
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://www.all-stars-distribution.com/custom/front/getASDMissingImages.php');

        if($response->getStatusCode() == 200){
            
            $data = json_decode($response->getBody(), true);
            
            ASD_missing_images::truncate();
            
            foreach( $data AS $row){
                
                $row_missing_image = new ASD_missing_images();
                $row_missing_image->manufacturer = $row['manufacturer'];
                $row_missing_image->reference    = $row['reference'];
                $row_missing_image->save();
                
            }
        }
        
        return back();
    }

    public static function dashboard_missing_images($type){

        $data = array();
        $bd_data = ASD_missing_images::get();

        foreach($bd_data AS $item) $data[] = ['reference' => $item->reference, 'brand' => $item->manufacturer];
        
        return [
            'name'              => trans('dashboard.ASD missing images'),
            'col'               => 4,
            'item_id'           => $type . '_asd_missing_images',
            'columns'           => ['reference', 'brand'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }
    
    
}
