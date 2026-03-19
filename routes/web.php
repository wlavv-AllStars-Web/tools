<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use App\Models\modules\tv\tv;


Use App\Http\Controllers\Front\suppliersBackordersController;

Route::get('/', function () { return view('auth.login'); });
Route::get('/tv', function () { 
    
    $item = tv::where('active', 1)->first();
    return view('tv', compact('item'));
    
});

Route::get( 'suppliersBackorders/check/{id_supplier}/{token}',  [suppliersBackordersController::class, 'index'])->name('frontSuppliersBackorders.index');
Route::post('suppliersBackorders/update/backorder',             [suppliersBackordersController::class, 'updateBackorders'])->name('frontSuppliersBackorders.updateBackorders');
Route::post('suppliersBackorders/update/comment',               [suppliersBackordersController::class, 'updateComment'])->name('frontSuppliersBackorders.updateComment');
Route::get('suppliersBackorders/thanks/{id_supplier}/{token}',  [suppliersBackordersController::class, 'thanks'])->name('frontSuppliersBackorders.thanks');




Auth::routes();

/**
Route::get('/getPassword/{locale}', function (string $locale) {
    echo Hash::make('temp@2025P@ssword');
});
**/

/**
Route::get('/try/tracking', function () {
    
    $trackingNumber = '7490/11125643';

    $url = "https://parcelsapp.com/api/v3/shipments/tracking";
    $data = [
        "shipments" => [
            [
                "trackingId" => $trackingNumber,
                "destinationCountry" => "Spain"
            ]
        ],
        "language" => "en",
        "apiKey" => "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1aWQiOiJlYzI4ZDZlMC04ZDY4LTExZjAtYWM1My0xZjkwNTRkNWJkODkiLCJzdWJJZCI6IjY4YzAwMzRlODA0YjA2NTUxYzAyZTc4NSIsImlhdCI6MTc1NzQxNDIyMn0.TvmwNYsZCXDS74xi6nZ4Da8lncTWldCcGEAnV8-37YU"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    
    dd( $result );
    
    $uuid = $result['shipments'][0]['uuid'] ?? null;

    if (!$uuid) {
        dd("Erro: não foi devolvido uuid", $result);
    }

    $urlDetails = "https://parcelsapp.com/api/v3/shipments?uuids[]=" . $uuid;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlDetails);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: application/json"]);
    $detailsResponse = curl_exec($ch);
    curl_close($ch);

    $details = json_decode($detailsResponse, true);

    dd($details);
});
**/



/**
Route::get('/language/{locale}', function (string $locale) {

    app()->setLocale($locale);
    session()->put('locale', $locale);
    return redirect()->back();

});
**/