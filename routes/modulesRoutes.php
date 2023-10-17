<?php

use App\Http\Controllers\Modules\modulesController;
use App\Http\Controllers\Modules\bmsProcurement\bmsProcurementController;
use App\Http\Controllers\Modules\bmsProcurement\bmsProcurementPurchaseOrderController;

Route::resources([ 'modules'=> modulesController::class           ]);
Route::resources([ 'modules/bmsProcurement'=> bmsProcurementController::class           ]);