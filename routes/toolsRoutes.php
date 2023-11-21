<?php

/** Custom tools routes **/
Use App\Http\Controllers\CustomTools\stockEntryController;
Use App\Http\Controllers\CustomTools\autoOrdersController;


Route::get( 'customTools/stockEntry/listToRemove', [stockEntryController::class, 'listToRemove'])->name('stockEntry.listToRemove');
Route::post( 'customTools/stockEntry/post',        [stockEntryController::class, 'post'])->name('stockEntry.post');
Route::resources([ 'customTools/stockEntry'=>        stockEntryController::class]);
Route::resources([ 'customTools/autoOrders'=>        autoOrdersController::class]);
