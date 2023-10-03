<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\stockEntryController;

Route::get('/', function () { return view('auth.login'); });

Auth::routes();

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

Route::get('/home',       function () { return view('areas.dashboard.index'); })->name('dashboard');
Route::get('/dashboard',       function () { return view('areas.dashboard.index'); })->name('dashboard');
Route::get('/administration',  function () { return view('areas.admin.index'); })->name('adminPanels');
Route::get('/webmaster',       function () { return view('areas.webmaster.index'); })->name('webmasterPanels');
Route::get('/humanResources',  function () { return view('areas.hr.index'); })->name('hrPanels');
Route::get('/finance',         function () { return view('areas.finance.index'); })->name('financePanels');
Route::get('/logistics',       function () { return view('areas.logistics.index'); })->name('logisticsPanels');
Route::get('/marketing',       function () { return view('areas.marketing.index'); })->name('marketingPanels');
Route::get('/customerSupport', function () { return view('areas.support.index'); })->name('customerSuportPanels');

Route::resources([
    'stockEntry' => stockEntryController::class,
]);