<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

Use App\Http\Controllers\Front\suppliersBackordersController;
Use App\Http\Controllers\Front\frontMarketingController;

use App\Models\modules\tv\tv;

Route::get('/', function () { return view('auth.login'); });
Route::get('/tv', function () { 
    $item = tv::where('active', 1)->first();
    return view('tv', compact('item'));
});


Route::get('send/newsletter', [frontMarketingController::class, 'send'])->name('frontMarketingController.send');

Route::get('/dev/clear-all', function () {
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    return 'All caches cleared';
})->middleware('auth');

Route::get(  'suppliersBackorders/check/{id_supplier}/{token}',   [suppliersBackordersController::class, 'index'])->name('frontSuppliersBackorders.index');
Route::post( 'suppliersBackorders/update/backorder',              [suppliersBackordersController::class, 'updateBackorders'])->name('frontSuppliersBackorders.updateBackorders');
Route::post( 'suppliersBackorders/update/comment',                [suppliersBackordersController::class, 'updateComment'])->name('frontSuppliersBackorders.updateComment');
Route::get(  'suppliersBackorders/thanks/{id_supplier}/{token}',  [suppliersBackordersController::class, 'thanks'])->name('frontSuppliersBackorders.thanks');

Auth::routes();
