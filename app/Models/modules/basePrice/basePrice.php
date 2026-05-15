<?php

namespace App\Models\modules\basePrice;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class basePrice extends Model{     

    use HasFactory;
    protected $table = 'basePrice';
    protected $fillable = [
        'id_manufacturer',
        'reference',
        'eur',
        'usd',
        'pounds',
        'yen',
    ];
    
    public static function getRows( ){
        $manufacturerTable = env('DB2_DB_prefix', env('DB2_prefix', 'ps_')) . 'manufacturer';

        return BasePrice::select(
                'm.id_manufacturer',
                'm.name',
                'm.currency',
                DB::raw('COUNT(*) AS nr_products'),
                DB::raw('COUNT(CASE WHEN eur IS NOT NULL AND eur != "" THEN 1 END) AS nr_eur'),
                DB::raw('COUNT(CASE WHEN usd IS NOT NULL AND usd != "" THEN 1 END) AS nr_usd'),
                DB::raw('COUNT(CASE WHEN pounds IS NOT NULL AND pounds != "" THEN 1 END) AS nr_pounds'),
                DB::raw('COUNT(CASE WHEN yen IS NOT NULL AND yen != "" THEN 1 END) AS nr_yen'),
                'created_at',
                'updated_at'
            )
            ->join($manufacturerTable . ' as m', 'm.id_manufacturer', '=', 'basePrice.id_manufacturer')
            ->groupBy('m.id_manufacturer', 'm.name', 'm.currency', 'created_at', 'updated_at')
            ->orderBy('m.name', 'ASC')
            ->get();
    }
    
    public static function addUpdate( $id_manufacturer ){
   
        $filePath = public_path('uploads/files/basePrices/' . $id_manufacturer . '.csv');
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Ficheiro não encontrado!');
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle, 0, ';');
            
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                
                basePrice::updateOrCreate(
                    ['reference' => $data[0]],
                    [
                        'id_manufacturer'    => $id_manufacturer,
                        'eur'    => trim($data[1]) === '' ? null : $data[1],
                        'usd'    => trim($data[2]) === '' ? null : $data[2],
                        'pounds' => trim($data[3]) === '' ? null : $data[3],
                        'yen'    => trim($data[4]) === '' ? null : $data[4],
                    ]
                );
            }
        
            fclose($handle);
        }
    }
}
