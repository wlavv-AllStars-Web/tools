<?php

/** Main routes **/
use App\Http\Controllers\Prestashop\supplierController;
use App\Http\Controllers\Prestashop\manufacturersController;
use App\Http\Controllers\Prestashop\productsController;
use App\Http\Controllers\Prestashop\customersController;
use App\Http\Controllers\Prestashop\ordersController;
use App\Http\Controllers\Prestashop\categoriesController;
use App\Http\Controllers\Prestashop\carriersController;
use App\Http\Controllers\Prestashop\cmsController;
use App\Http\Controllers\Prestashop\employeesController;
use App\Http\Controllers\Prestashop\issuesController;
use App\Http\Controllers\Prestashop\permissionsController;
use App\Http\Controllers\Prestashop\profilesController;
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
Route::resources([ 'home'           => dashboardController::class      ]);
Route::resources([ 'dashboard'      => dashboardController::class      ]);

Route::post( 'customTools/dashboard/post',              [dashboardController::class, 'post'])->name('dashboard.post');
Route::post( 'customTools/dashboard/daily_stats',       [dashboardController::class, 'daily_stats'])->name('dashboard.daily_stats');
Route::post( 'customTools/dashboard/shipping_report',   [dashboardController::class, 'shipping_report'])->name('dashboard.shipping_report');
Route::get(  'customTools/dashboard/kpi',               [dashboardController::class, 'kpi'])->name('dashboard.kpi');

Route::resources([ 'administration' => adminController::class   ]);
Route::resources([ 'web'            => webController::class     ]);

Route::resources([ 'hr'             => hrController::class      ]);
Route::resources([ 'finance'        => financeController::class ]);

Route::resources([ 'data'           => dataController::class    ]);


Route::get(  'finance/documents/inventory', [financeController::class, 'download_inventory'])->name('finance.download_inventory');
Route::get(  'finance/documents/intrastat', [financeController::class, 'download_intrastat'])->name('finance.download_intrastat');
Route::post( 'finance/documents/intrastat/importacao', [financeController::class, 'intrastat_import'])->name('finance.download_intrastat_import');
Route::post( 'finance/documents/intrastat/exportacao', [financeController::class, 'intrastat_export'])->name('finance.download_intrastat_export');
Route::post( 'finance/documents/intrastat/saveCurrencyRate', [financeController::class, 'save_currency_rate'])->name('finance.save_currency_rate');

Route::resources([ 'logistics'      => logisticsController::class      ]);
Route::resources([ 'marketing'      => marketingController::class      ]);
Route::post( 'customTools/marketing/post', [marketingController::class, 'post'])->name('marketing.post');
Route::get( 'marketing/ASD/missingImages',[marketingController::class, 'getASDMissingImages'])->name('marketing.asdMissingImages');

Route::resources([ 'customer'       => customerSupportController::class]);

Route::resources([ 'sales'          => salesController::class]);
Route::resources([ 'purchase'       => purchaseController::class]);

/** Controllers **/
Route::resources([ 'addresses'      => addressesController::class     ]);
Route::resources([ 'suppliers'      => supplierController::class      ]);
Route::resources([ 'manufacturers'  => manufacturersController::class ]);
Route::get(  'manufacturer/resources',      [manufacturersController::class, 'resources'])->name('manufacturers.resources');
Route::post( 'manufacturer/ressourcesPost', [manufacturersController::class, 'ressourcesPost'])->name('manufacturers.ressourcesPost');

Route::resources([ 'products'       => productsController::class      ]);
Route::resources([ 'customers'      => customersController::class     ]);
Route::resources([ 'orders'         => ordersController::class        ]);
Route::resources([ 'categories'     => categoriesController::class    ]);
Route::resources([ 'carriers'       => carriersController::class      ]);
Route::resources([ 'cms'            => cmsController::class           ]);
Route::resources([ 'employees'      => employeesController::class     ]);
Route::resources([ 'issues'         => issuesController::class        ]);
Route::resources([ 'permissions'    => permissionsController::class   ]);
Route::resources([ 'profiles'       => profilesController::class      ]);

Route::post( 'orders/sendReviewedEmail', [ordersController::class, 'sendReviewedEmail'])->name('orders.sendReviewedEmail');

