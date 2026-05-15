<?php

namespace App\Models\modules\ASD_missing_images;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Concerns\BuildsDashboardPanels;
class ASD_missing_images extends Model
{
    
    use BuildsDashboardPanels;
use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'ASD_missing_images';
    public $timestamps = false;

    protected $fillable = [
        'manufacturer',
        'reference',
    ];

    public static function addMissingImages(array $rows = [])
    {
        self::truncate();

        if (!empty($rows)) {
            self::insert($rows);
        }

        return 1;
    }

    public static function dashboard_missing_images($type)
    {
        $data = [];
        $bd_data = self::orderBy('manufacturer')->orderBy('reference')->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'reference' => $item->reference,
                'brand' => $item->manufacturer
            ];
        }

        return [
            'name' => trans('dashboard.ASD missing images'),
            'col' => 4,
            'item_id' => $type . '_asd_missing_images',
            'columns' => ['reference', 'brand'],
            'counter' => count($data),
            'data' => $data
        ];
    }
}