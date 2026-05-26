<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\compatsController;
use App\Http\Controllers\API\myGarageController;
use App\Http\Controllers\API\erpETAController;
use App\Http\Controllers\API\HomepageApiController;
use App\Http\Controllers\API\AsdAlertApiController;
use App\Http\Controllers\CustomTools\checkVatController;
use App\Http\Controllers\CustomTools\HomepageASDAdminController;

Route::group([
    'middleware' => ['api', 'cors'],
], function ($router) {
    
    /** API for VAT validation **/
    Route::post('/vat/add', [CheckVatController::class, 'apiAdd'])->name('api.vat.add');
    Route::get('/vat/add/{id_customer}/{vat_number}/{country_iso}/{token}', [CheckVatController::class, 'apiAdd'])->whereNumber('id_customer')->name('api.vat.add.get');
    
    /** API to homepage **/
    Route::get('/homepage', [HomepageApiController::class, 'index' ]);  
    
    /** ASD **/
    
    /** Homepage **/
    Route::get('/asd/homepage', [HomepageASDAdminController::class, 'api']);   
    Route::get('/asd/alerts/{iso_code}/{token}', [AsdAlertApiController::class, 'index'])->name('api.asd.alerts');
    
    /** COMPATS API **/
    
        /** FRONT **/
        
            /** COMPATS MENU **/
            
                /** GET BRANDS **/
                Route::get('get/brands/{store}/{token}',[compatsController::class, 'getBrands'])->name('compatsAPI.getBrands');
                Route::get('get/model/{id_brand}/{store}/{token}',[compatsController::class, 'getModels'])->name('compatsAPI.getModels');
                Route::get('get/type/{id_model}/{store}/{token}',[compatsController::class, 'getTypes'])->name('compatsAPI.getTypes');
                Route::get('get/version/{id_type}/{store}/{token}',[compatsController::class, 'getVersions'])->name('compatsAPI.getVersions');
                Route::get('get/compats/{id_brand}/{id_model}/{id_type}/{id_version}/{store}/{token}',[compatsController::class, 'getCompatsFull'])->name('compatsAPI.getCompatsFull');
                
                /** GET ALL COMPAT STRUCTURE FOR STORE **/
                Route::get('get/brand/{id_brand}/{store}/{token}',[compatsController::class, 'getCompats'])->name('compatsAPI.getCompats');
                
                /** GET ALL PRODUCTS IDS FOR SELECTED COMPAT ON A SPECIFIC STORE **/
                Route::get('get/products/{id_compat}/{store}/{token}',[compatsController::class, 'getProducts'])->name('compatsAPI.getProducts');
            
            /** PRODUCT PAGE **/
            
                /** API REQUEST FOR PRODUCT PAGE COMPATS TAB **/
                Route::get('get/product/compats/{id_product}/{store}/{token}',[compatsController::class, 'getProductCompatDetails'])->name('compatsAPI.getProductCompats');
                
                
            /** NEWSLETTER **/
                // Add car to garage
                // Get my garage carros
                // Remove car from my garage

        /** BACKOFFICE **/

            /** GET ALL COMPAT STRUCTURE FOR STORE **/
                Route::get('get/bo/all/compats/{store}/{token}',[compatsController::class, 'getAllCompats'])->name('compatsAPI.getAllCompats');
        
            /** COMPAT REQUEST BRAND **/
                Route::get('get/bo/brands/{store}/{token}',[compatsController::class, 'getBObrands'])->name('compatsAPI.getBObrands');
    
            /** COMPAT REQUEST MODEL **/
                Route::get('get/bo/models/{brand}/{store}/{token}',[compatsController::class, 'getBOmodels'])->name('compatsAPI.getBOmodels');
    
            /** COMPAT REQUEST TYPE **/
                Route::get('get/bo/types/{brand}/{model}/{store}/{token}',[compatsController::class, 'getBOtypes'])->name('compatsAPI.getBOtypes');
    
            /** COMPAT REQUEST VERSION **/
                Route::get('get/bo/versions/{brand}/{model}/{type}/{store}/{token}',[compatsController::class, 'getBOversions'])->name('compatsAPI.getBOversions');
    
            /** GENERATE COMPATS **/
                Route::get('create/bo/compats/{brand}/{model}/{type}/{version}/{product}/{store}/{token}',[compatsController::class, 'createCompats'])->name('compatsAPI.createBOcompats');
                
            /** REMOVE COMPATS **/
                Route::get('remove/bo/compats/{compat}/{product}/{store}/{token}',[compatsController::class, 'removeCompats'])->name('compatsAPI.removeCompats');
                
    /** COMPATS API **/

    /** MY GARAGE API **/
        Route::post('add/car',[myGarageController::class, 'addCar'])->name('myGarageAPI.addCar.post');
        Route::get('add/car/{id_customer}/{id_compat}/{iso_code}/{store}/{token}/{email?}',[myGarageController::class, 'addCar'])->name('myGarageAPI.addCar');
        Route::get('remove/car/{id_customer}/{id_compat}/{store}/{token}',[myGarageController::class, 'removeCar'])->name('myGarageAPI.removeCar');
        Route::get('get/cars/{id_customer}/{store}/{token}',[myGarageController::class, 'getMyGarage'])->name('myGarageAPI.getMyGarage');

        Route::get('get/erp/product/eta/{id_lang}/{id_product}/{id_product_attribute}/{reference?}',[erpETAController::class, 'getProductFromERP'])->name('erpETA.getProductFromERP');
        Route::post('/get/erp/product/eta/batch', [erpETAController::class, 'getEtaBatch'])->name('erpETA.getEtaBatch');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
