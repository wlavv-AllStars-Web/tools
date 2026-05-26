<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use App\Models\modules\compats\compats;
use App\Models\modules\compats\compats_newsletter;

class myGarageController extends Controller
{
    private static function validateToken($token)
    {
        $expectedToken = (string) config('allstars.api.tokens.my_garage');

        if($expectedToken === '' || !hash_equals($expectedToken, (string) $token)){
            $data =[
                'status' => 'FAIL',
                'message' => 'API token invalid'
                ];
                
            echo json_encode($data);
            exit;
        }
    }

    public function addCar(Request $request)
    {
        self::validateToken($request->token);
        
        $compat = compats::getCompatDetail( $request->id_compat );

        compats_newsletter::saveMyCar(
            $request->id_customer,
            $request->iso_code,
            $request->store,
            $compat,
            $request->input('email', $request->input('customer_email'))
        );
        
        $data = [
            'status'    => 'SUCCESS',
            'message'   => "CAR ADDED TO CUSTOMER'S GARAGE"
        ];
        
        echo json_encode($data);
        exit;
    }
    
    public function getMyGarage(Request $request)
    {
        self::validateToken($request->token);
        
        $cars = compats_newsletter::getMyGarage($request->id_customer);

        if( count($cars) > 0 ){
            $data = [ 'status' => 'SUCCESS', 'message' => "LIST OF CUSTOMER'S CARS!", 'data' => $cars];
        }else{
            $data = [ 'status' => 'SUCCESS',    'message' => 'NO CARS TO RETURN!' ];
        }
        
        echo json_encode($data);
        exit;
    }
    
    public function removeCar(Request $request)
    {
        self::validateToken($request->token);
        
        $deleted = compats_newsletter::removeCarFromMyGarage($request->id_customer, $request->id_compat, $request->store);

        if( $deleted == 1 ){
            $data = [ 'status' => 'SUCCESS', 'message' => 'CAR REMOVED FROM MY GARAGE!' ];
        }else{
            $data = [ 'status' => 'FAIL',    'message' => "CAR IS NOT AVAILABLE ON CUSTOMER'S GARAGE!" ];
        }
        
        echo json_encode($data);
        exit;
    }
}
