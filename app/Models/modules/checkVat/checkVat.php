<?php

namespace App\Models\modules\checkVat;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class checkVat extends Model
{
    use HasFactory;
    protected $table = "checkVat";
    public $primaryKey = 'id_checkVat';

    public static function verify($data){
        
        self::clean();
        
        $client = new \SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
        
        foreach ($data as $address) {
            
            $country_code = substr($address->vat_number, 0, 2);
            $vat_number = substr($address->vat_number, 2);
            
            $attempts = 0;
            $maxAttempts = 3;
            $success = false;

            while ($attempts < $maxAttempts && !$success) {
                try {

                    $attempts++;

                    $result = $client->checkVat([
                        'countryCode' => $country_code,
                        'vatNumber' => $vat_number,
                    ]);

                    $verified = [
                        'id_customer' => $address->id_customer,
                        'country_code' => $country_code,
                        'vat_number' => $vat_number,
                        'valid' => $result->valid,
                        'attempts' => $attempts,
                        'error' => ''
                    ];
                    
                    checkVat::insert($verified);
                    
                    $success = true;

                } catch (\SoapFault $e) {

                    if ($attempts >= $maxAttempts) {
                        $verified = [
                            'id_customer' => $address->id_customer,
                            'country_code' => $country_code,
                            'vat_number' => $vat_number,
                            'valid' => false,
                            'error' => $e->getMessage(),
                            'attempts' => $attempts,
                        ];
                        
                        checkVat::insert($verified);
                    }
                    sleep(3);
                }
            }
            sleep(2);
        }

        
        return $verified;
    }

    public static function clean(){
        checkVat::truncate();
    }

    public static function getCounters(){

        $by_country = checkVat::select(DB::Raw('count(*) AS counter'), 'country_code')->where('valid', 0)->groupBy('country_code')->get();

        $valid   = checkVat::where('valid', 1)->groupBy('vat_number')->get();
        $invalid = checkVat::where('valid', 0)->groupBy('vat_number')->get();
        $total   = checkVat::groupBy('vat_number')->get();

        return (object)[
            'valid' => count($valid),
            'invalid' => count($invalid),
            'total' => count($total),
            'country_code' => $by_country
        ];
    }
}