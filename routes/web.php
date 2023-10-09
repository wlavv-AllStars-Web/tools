<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Modules\bmsController;
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
use App\Http\Controllers\CustomTools\stockEntryController;

Route::get('/', function () { return view('auth.login'); });

Auth::routes();

/** Departments routes **/
Route::get('/home',            function () { return view('areas.dashboard.index'); })->name('home');
Route::get('/dashboard',       function () { return view('areas.dashboard.index'); })->name('dashboard');
Route::get('/administration',  function () { return view('areas.admin.index');     })->name('administration');
Route::get('/webmaster',       function () { return view('areas.webmaster.index'); })->name('webmastering');
Route::get('/humanResources',  function () { return view('areas.hr.index');        })->name('humanResources');
Route::get('/finance',         function () { return view('areas.finance.index');   })->name('finances');
Route::get('/logistics',       function () { return view('areas.logistics.index'); })->name('logistics');
Route::get('/marketing',       function () { return view('areas.marketing.index'); })->name('marketing');
Route::get('/customerSupport', function () { return view('areas.support.index');   })->name('customerSuport');

/** Main routes **/
Route::resources([ 'suppliers'     => supplierController::class      ]);
Route::resources([ 'manufacturers' => manufacturersController::class ]);
Route::resources([ 'products'      => productsController::class      ]);
Route::resources([ 'customers'     => customersController::class     ]);
Route::resources([ 'orders'        => ordersController::class        ]);
Route::resources([ 'categories'    => categoriesController::class    ]);
Route::resources([ 'carriers'      => carriersController::class      ]);
Route::resources([ 'cms'           => cmsController::class           ]);
Route::resources([ 'employees'     => employeesController::class     ]);
Route::resources([ 'issues'        => issuesController::class        ]);
Route::resources([ 'permissions'   => permissionsController::class   ]);
Route::resources([ 'profiles'      => profilesController::class      ]);

/** Modules routes **/
Route::resources([ 'modules/bmsProcurement'=> bmsController::class           ]);

/** Custom tools routes **/
Route::resources([ 'customTools/stockEntry'=> stockEntryController::class    ]);

Route::post( '/customTools/stockEntry/post', [stockEntryController::class, 'post'])->name('stockEntry.post');
