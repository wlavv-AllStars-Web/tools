<?php

/** Main routes **/
use App\Http\Controllers\Areas\dashboardController;
use App\Http\Controllers\Areas\adminController;
use App\Http\Controllers\Areas\webController;
use App\Http\Controllers\Areas\hrController;
use App\Http\Controllers\Areas\financeController;
use App\Http\Controllers\Areas\logisticsController;
use App\Http\Controllers\Areas\marketingController;
use App\Http\Controllers\Areas\customerSupportController;
use App\Http\Controllers\Areas\salesController;
use App\Http\Controllers\Areas\purchaseController;

use App\Http\Controllers\Areas\dataController;

/** AREAS **/
Route::get('home', [dashboardController::class, 'index'])->name('home.index');
Route::get('dashboard', [dashboardController::class, 'index'])->name('dashboard.index');

Route::post( 'customTools/dashboard/post',              [dashboardController::class, 'post'])->name('dashboard.post');
Route::post( 'customTools/dashboard/shipping_report',   [dashboardController::class, 'shipping_report'])->name('dashboard.shipping_report');

Route::get('administration', [adminController::class, 'index'])->name('administration.index');
Route::get('web', [webController::class, 'index'])->name('web.index');

Route::resources([ 'hr'             => hrController::class      ]);
Route::get('finance', [financeController::class, 'index'])->name('finance.index');

Route::get('data', [dataController::class, 'index'])->name('data.index');


Route::get(  'finance/documents/inventory', [financeController::class, 'download_inventory'])->name('finance.download_inventory');
Route::get(  'finance/documents/intrastat', [financeController::class, 'download_intrastat'])->name('finance.download_intrastat');
Route::post( 'finance/documents/intrastat/importacao', [financeController::class, 'intrastat_import'])->name('finance.download_intrastat_import');
Route::post( 'finance/documents/intrastat/exportacao', [financeController::class, 'intrastat_export'])->name('finance.download_intrastat_export');
Route::post( 'finance/documents/intrastat/saveCurrencyRate', [financeController::class, 'save_currency_rate'])->name('finance.save_currency_rate');

Route::get('logistics', [logisticsController::class, 'index'])->name('logistics.index');
Route::get('marketing', [marketingController::class, 'index'])->name('marketing.index');
Route::post( 'customTools/marketing/post', [marketingController::class, 'post'])->name('marketing.post');
Route::get( 'marketing/ASD/missingImages',[marketingController::class, 'getASDMissingImages'])->name('marketing.asdMissingImages');

Route::get('customer', [customerSupportController::class, 'index'])->name('customer.index');

Route::get('sales', [salesController::class, 'index'])->name('sales.index');
Route::get('purchase', [purchaseController::class, 'index'])->name('purchase.index');

/** Controllers **/
Route::post( 'orders/sendReviewedEmail', [salesController::class, 'sendReviewedEmail'])->name('orders.sendReviewedEmail');
