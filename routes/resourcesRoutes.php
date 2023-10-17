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

/** AREAS **/
Route::resources([ 'home'           => dashboardController::class      ]);
Route::resources([ 'dashboard'      => dashboardController::class      ]);
Route::resources([ 'administration' => adminController::class          ]);
Route::resources([ 'web'            => webController::class            ]);
Route::resources([ 'hr'             => hrController::class             ]);
Route::resources([ 'finance'        => financeController::class        ]);
Route::resources([ 'logistics'      => logisticsController::class      ]);
Route::resources([ 'marketing'      => marketingController::class      ]);
Route::resources([ 'customer'       => customerSupportController::class]);

/** Controllers **/
Route::resources([ 'addresses'      => addressesController::class     ]);
Route::resources([ 'suppliers'      => supplierController::class      ]);
Route::resources([ 'manufacturers'  => manufacturersController::class ]);
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
