<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\country;
use Illuminate\Support\Facades\DB;

class ASM_ukoo_customer extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."ASM_ukoo_customer";
    }

    public static function dashboard_newsletter_registration($type){

        $data = array();

        $bd_data = self::select( DB::RAW('*'), DB::RAW('count(*) AS customers_by_version'))
            ->where('newsletter', 1)
            ->whereNotIn('email', [ 'bruno.fernandes.asm@gmail.com', '%@all-stars-motorsport.com%', '%@all-stars-distribution.com' ] )
            ->groupBy('version')
            ->orderBy('customers_by_version', 'DESC')
            ->get();

        foreach($bd_data AS $item) $data[] = ['name' => $item->brand . ' | ' . $item->model . ' | ' . $item->type . ' | ' . $item->version . ' | ', 'customers_by_version' => $item->customers_by_version ];
        
        return [
            'name'              => trans("dashboard.Newsletter registrations"),
            'col'               => 4,
            'item_id'           => $type . '_newsletters_registrations',
            'columns'           => ['name', 'customers_by_version'],
            'counter'           => self::whereNotIn('email', [ 'bruno.fernandes.asm@gmail.com', '%@all-stars-motorsport.com%', '%@all-stars-distribution.com' ] )->count(),
            'info'              => true,
            'data'              => $data
        ];        
    }

    public static function getEmailsOfTheCompats($detail, $iso){
       
        $emails = array();
        $str_eamils = '';
        
        return self::where('id_brand', $detail->brand)->where('id_model', $detail->model)->where('id_version', $detail->version)->where('id_type', $detail->type)->where('iso_code', $iso)->where('newsletter', 1)->pluck('email');
    }
}
