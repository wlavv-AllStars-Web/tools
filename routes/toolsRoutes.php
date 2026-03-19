<?php

/** Custom tools routes **/
Use App\Http\Controllers\CustomTools\barcodeController;
Use App\Http\Controllers\CustomTools\stockEntryController;
Use App\Http\Controllers\CustomTools\autoOrdersController;
Use App\Http\Controllers\CustomTools\backordersController;
Use App\Http\Controllers\CustomTools\suppliersBackordersController;
Use App\Http\Controllers\CustomTools\uploadsController;

Use App\Http\Controllers\CustomTools\dpdController;
Use App\Http\Controllers\CustomTools\documentsManagerController;
Use App\Http\Controllers\CustomTools\housingController;
Use App\Http\Controllers\CustomTools\pickingController;
Use App\Http\Controllers\CustomTools\carrierIssuesController;
Use App\Http\Controllers\CustomTools\carrierReturnController;

Use App\Http\Controllers\CustomTools\compatsController;
Use App\Http\Controllers\CustomTools\checkVatController;
Use App\Http\Controllers\CustomTools\shippingController;
Use App\Http\Controllers\CustomTools\suppliersIssuesController;
Use App\Http\Controllers\CustomTools\suppliersMapController;

Use App\Http\Controllers\CustomTools\dashboardController;
Use App\Http\Controllers\CustomTools\searchController;
Use App\Http\Controllers\CustomTools\priceMapController;
Use App\Http\Controllers\CustomTools\refundController;

Use App\Http\Controllers\CustomTools\tvController;
Use App\Http\Controllers\CustomTools\basePriceController;


Use App\Http\Controllers\CustomTools\dataMigrationController;

Use App\Http\Controllers\CustomTools\productIssuesController;
Use App\Http\Controllers\CustomTools\pricingConvertionController;


use App\Http\Controllers\CustomTools\checklistManagerController;
use App\Http\Controllers\CustomTools\employeeChecklistController;
use App\Http\Controllers\CustomTools\translationPhraseController;

use App\Http\Controllers\CustomTools\returnsController;
use App\Http\Controllers\CustomTools\warrantiesController;
use App\Http\Controllers\CustomTools\erpController;

//TO REMOVE
//use App\Http\Controllers\CustomTools\purchaseRequestController;


use App\Http\Controllers\CustomTools\quotesController;

use App\Http\Controllers\CustomTools\Tasks\taskController;
use App\Http\Controllers\CustomTools\Tasks\managerTaskController;
use App\Http\Controllers\CustomTools\Tasks\userTaskController;
use App\Http\Controllers\CustomTools\Tasks\taskFileController;
use App\Http\Controllers\CustomTools\Tasks\productivityController;

use App\Models\modules\checklist\daily_checklist;

use App\Http\Controllers\CustomTools\logsController;


use App\Http\Controllers\CustomTools\AsgTasksController;

use App\Http\Controllers\CustomTools\purchasePriceController;



Use Carbon\Carbon;

    /**ASD**/
    
    /**
    Route::get('/asd/comparar-ids', [DataMigrationController::class, 'compareTableIDsASD'])->name('comparar.compareTableIDsASD');
    Route::get('/associar-colunas/{table}', [dataMigrationController::class, 'showMappingPage'])->name('associar.colunas');
    Route::get('/comparar-dados', [DataMigrationController::class, 'compareTableData'])->name('comparar.dados');
    Route::get('/comparar-ids', [DataMigrationController::class, 'compareTableIDs'])->name('comparar.ids');
    Route::get('/import-compats', [DataMigrationController::class, 'compatsImport'])->name('comparar.compatsImport');
    **/

    Route::get(  'customTools/dashboard/cron',              [dashboardController::class, 'cron_update'])->name('dashboard.cron_update');

    Route::get( 'barcode/product/generate/{id_product}/{id_product_attribute}', [barcodeController::class, 'generateProductBarcode'])->name('barcode.generateProductBarcode');
    Route::get( 'barcode/product/{barcode}/{id_product}/{id_product_attribute}', [barcodeController::class, 'checkAndShowBarcode'])->name('barcode.checkAndShowBarcode');
    Route::get( 'barcode/product/print/{id_product}/{id_product_attribute}/{repeat}', [barcodeController::class, 'printProductBarcode'])->name('barcode.printProductBarcode');

Route::middleware(['auth'])->group(function () {

    Route::get('/tasks',                                    [AsgTasksController::class, 'index'])->name('asg_tasks.index');
    Route::post('/tasks',                                   [AsgTasksController::class, 'store'])->name('asg_tasks.store');
    Route::patch('/tasks/{task}/inline',                    [AsgTasksController::class, 'inlineUpdate'])->name('asg_tasks.inline');

    Route::get( 'customTools/base/price',                   [basePriceController::class, 'index'])->name('basePrice.index');
    Route::post('customTools/base/price/save',              [basePriceController::class, 'store'])->name('basePrice.store');
    Route::post('customTools/base/price/execute',           [basePriceController::class, 'execute'])->name('basePrice.execute');
    Route::post('customTools/base/price/updatePricing',     [basePriceController::class, 'updatePricing'])->name('basePrice.updatePricing');


    Route::get( 'customTools/refunds',                      [refundController::class, 'index'])->name('refund.index');
    Route::post('customTools/refunds/new',                  [refundController::class, 'newRefund'])->name('refund.newRefund');
    Route::post('customTools/refunds/get/info',             [refundController::class, 'getInfo'])->name('refund.getInfo');
    Route::post('customTools/refunds/edit',                 [refundController::class, 'editRefund'])->name('refund.editRefund');
    Route::post('customTools/refunds/update',               [refundController::class, 'updateRefund'])->name('refund.updateRefund');
    
    
    
    Route::get( 'customTools/priceMap',                     [priceMapController::class, 'index'])->name('priceMap.index');
    Route::post( 'customTools/getPriceMapOfBrand',          [priceMapController::class, 'getPriceMapOfBrand'])->name('priceMap.getPriceMapOfBrand');
    Route::get( 'customTools/priceMap/cron/{part}',         [priceMapController::class, 'cron_priceMap'])->name('priceMap.cron_priceMap');
    
    
    
    Route::post( 'customTools/search',                      [searchController::class, 'globalSearch'])->name('search.globalSearch');
    Route::get(  'customTools/search',                      [searchController::class, 'globalSearchGet'])->name('search.globalSearchGet');
        
    Route::get(  'customTools/dashboard/index',             [dashboardController::class, 'index'])->name('dashboard.index');
    Route::post( 'customTools/dashboard/counters/content',  [dashboardController::class, 'getCountersContent'])->name('dashboard.getCountersContent');
    
    Route::get(  'customTools/suppliers/map',               [suppliersMapController::class, 'index'])->name('suppliersMap.index');
    Route::post(  'customTools/suppliers/map/store',        [suppliersMapController::class, 'store'])->name('suppliersMap.store');
    Route::post(  'customTools/suppliers/map/modal',        [suppliersMapController::class, 'modal'])->name('suppliersMap.modal');
    
    Route::get(  'customTools/suppliers/issues/{type}',     [suppliersIssuesController::class, 'index'])->name('suppliersIssues.index');
    
    Route::post( 'customTools/suppliers/issues/delivery/new',   [suppliersIssuesController::class, 'newDeliveryIssue'])->name('suppliersIssues.newDeliveryIssue');
    Route::post( 'customTools/suppliers/issues/delivery/update',[suppliersIssuesController::class, 'updateDeliveryIssue'])->name('suppliersIssues.updateDeliveryIssue');
    Route::post( 'customTools/suppliers/issues/delivery/close', [suppliersIssuesController::class, 'closeDeliveryIssue'])->name('suppliersIssues.closeDeliveryIssue');
    
    Route::post( 'customTools/suppliers/issues/warranty/new',   [suppliersIssuesController::class, 'newWarrantyIssue'])->name('suppliersIssues.newWarrantyIssue');
    Route::post( 'customTools/suppliers/issues/warranty/update',[suppliersIssuesController::class, 'updateWarrantyIssue'])->name('suppliersIssues.updateWarrantyIssue');
    Route::post( 'customTools/suppliers/issues/warranty/close', [suppliersIssuesController::class, 'closeWarrantyIssue'])->name('suppliersIssues.closeWarrantyIssue');
    
    Route::post( 'customTools/suppliers/issues/new',[suppliersIssuesController::class, 'newSupplierIssue'])->name('suppliersIssues.newSupplierIssue');
    Route::post( 'customTools/suppliers/issues/update',[suppliersIssuesController::class, 'updateSupplierIssue'])->name('suppliersIssues.updateSupplierIssue');
    
    
    Route::get(  'customTools/shipping',[shippingController::class, 'index'])->name('shipping.index');
    Route::post( 'customTools/shipping/add/eta/delay',[shippingController::class, 'addDelay'])->name('shipping.addDelay');
    Route::get(  'customTools/shipping/add',[shippingController::class, 'add'])->name('shipping.add');
    Route::post( 'customTools/shipping/save',[shippingController::class, 'store'])->name('shipping.store');
    Route::get(  'customTools/shipping/edit/{id}',[shippingController::class, 'edit'])->name('shipping.edit');
    Route::post( 'customTools/shipping/update/{id}',[shippingController::class, 'update'])->name('shipping.update');
    Route::post( 'customTools/shipping/downloadData',[shippingController::class, 'downloadData'])->name('shipping.downloadData');
    Route::get('/export-csv', [shippingController::class, 'exportCsv']);

    Route::get('customTools/shipping/packingList', [shippingController::class, 'packingList'])->name('shipping.packingList');
    Route::post('customTools/shipping/packingList/export-xls', [shippingController::class, 'exportPackingListXls'])->name('shipping.packingList.exportXls');

    Route::get( 'customTools/vat/check',[checkVatController::class, 'index'])->name('checkVat.index');
    Route::get( 'customTools/vat/verify',[checkVatController::class, 'verify'])->name('checkVat.verify');
    
    Route::get( 'customTools/compats/index',[compatsController::class, 'index'])->name('compats.index');
    Route::get( 'customTools/compats/add',[compatsController::class, 'add'])->name('compats.add');
    Route::post('customTools/compats/options/edit',[compatsController::class, 'updateTag'])->name('compats.updateTag');
    Route::post('customTools/compats/get/options',[compatsController::class, 'getOptions'])->name('compats.getOptions');
    Route::post('customTools/compats/get/options/modal',[compatsController::class, 'getOptionsForModal'])->name('compats.getOptionsForModal');
    Route::post('customTools/compats/create/compatibilities',[compatsController::class, 'createCompatibilities'])->name('compats.createCompatibilities');
    Route::post('customTools/compats/create/relationship',[compatsController::class, 'saveNewRelationship'])->name('compats.saveNewRelationship');
    Route::post('customTools/compats/edit/logo',[compatsController::class, 'editImage'])->name('compats.editImage');
    Route::post('customTools/compats/options/edit/options',[compatsController::class, 'setData'])->name('compats.setData');
    
    Route::post('customTools/compats/remove/comapt',[compatsController::class, 'removeCompat'])->name('compats.removeCompat');
    
    Route::get('customTools/compats/menu/updateMenu',[compatsController::class, 'updateMenu'])->name('compats.updateMenu');
    Route::post('customTools/compats/menu/setOrder',[compatsController::class, 'setOrder'])->name('compats.setOrder');

    Route::get('customTools/tv',                        [tvController::class, 'index'])->name('tv.index');
    Route::post('customTools/tv/save',                  [tvController::class, 'store'])->name('tv.store');
    Route::post('customTools/tv/toggle-active/{id}',    [tvController::class, 'toggleActive'])->name('tv.toggleActive');
    Route::post('customTools/tv/update_text',           [tvController::class, 'changeText'])->name('tv.changeText');     

    Route::get( 'customTools/carrier/return/index',[carrierReturnController::class, 'index'])->name('carrierReturn.index');
    Route::post( 'customTools/carrier/return/add',[carrierReturnController::class, 'store'])->name('carrierReturn.store');
    Route::post( 'customTools/carrier/return/update',[carrierReturnController::class, 'update'])->name('carrierReturn.update');
    Route::post( 'customTools/carrier/return/archive',[carrierReturnController::class, 'archive'])->name('carrierReturn.archive');

    Route::get( 'customTools/carrier/verification/index',[carrierIssuesController::class, 'verificationIndex'])->name('carrierIssues.verification.index');
    Route::post( 'customTools/carrier/verification/upload',[carrierIssuesController::class, 'verificationUpload'])->name('carrierIssues.verification.upload');
    Route::post( 'customTools/carrier/verification/check',[carrierIssuesController::class, 'carrierVerify'])->name('carrierIssues.verification.carrierVerify');
    
    
    Route::post( 'customTools/carrier/issues/archive',[carrierIssuesController::class, 'archive'])->name('carrierIssues.archive');
    Route::post( 'customTools/carrier/issues/update', [carrierIssuesController::class, 'update'])->name('carrierIssues.update');
    Route::post( 'customTools/carrier/issues/edit',   [carrierIssuesController::class, 'edit'])->name('carrierIssues.edit');
    Route::post( 'customTools/carrier/issues/destroy',[carrierIssuesController::class, 'destroy'])->name('carrierIssues.destroy');
    Route::get(  'customTools/carrier/issues/index',  [carrierIssuesController::class, 'index'])->name('carrierIssues.index');
    Route::post(  'customTools/carrier/issues/save',  [carrierIssuesController::class, 'store'])->name('carrierIssues.store');
    
    Route::get(  'customTools/picking/index', [pickingController::class, 'index'])->name('picking.index');
    Route::post( 'customTools/picking/rowDone', [pickingController::class, 'rowDone'])->name('picking.rowDone');
    Route::post( 'customTools/picking/getEAN', [pickingController::class, 'getEAN'])->name('picking.confirmEAN');
    
    Route::get(  'customTools/housing/index', [housingController::class, 'index'])->name('housing.index');
    Route::post( 'customTools/housing/requestData', [housingController::class, 'requestData'])->name('housing.requestData');
    Route::post( 'customTools/housing/saveData', [housingController::class, 'saveData'])->name('housing.saveData');
    Route::post( 'customTools/housing/editLocation', [housingController::class, 'editLocation'])->name('housing.editLocation');
    Route::post( 'customTools/housing/editMeasures', [housingController::class, 'editMeasures'])->name('housing.editMeasures');
    
    Route::get( 'customTools/documentManager', [documentsManagerController::class, 'index'])->name('documentsManager.index');
    Route::get( 'customTools/documentManager/{category}/{element}', [documentsManagerController::class, 'listDocuments'])->name('documentsManager.listDocuments');
    Route::get( 'customTools/documentManager/add', [documentsManagerController::class, 'addDocument'])->name('documentsManager.addDocument');
    Route::post( 'customTools/documentManager/save', [documentsManagerController::class, 'store'])->name('documentsManager.store');
    Route::post( 'customTools/documentManager/search', [documentsManagerController::class, 'search'])->name('documentsManager.search');
    Route::post( 'customTools/documentManager/loadFile', [documentsManagerController::class, 'loadFile'])->name('documentsManager.loadFile');
    Route::post( 'customTools/documentManager/listSearch', [documentsManagerController::class, 'listSearch'])->name('documentsManager.listSearch');
    Route::post( 'customTools/documentManager/destroy', [documentsManagerController::class, 'destroy'])->name('documentsManager.destroy');
    
    
    /** DPD **/
    Route::get( 'dpd/csv/generate/{id_order}/{weight}', [dpdController::class, 'generateCSV'])->name('dpd.generateCSV');
    /** DPD **/
    
    /** BARCODE **/
    Route::get( 'barcode/example', [barcodeController::class, 'example'])->name('barcode.example');
    Route::get( 'barcode/order/print/{id_order}', [barcodeController::class, 'printOrderBarcode'])->name('barcode.printOrderBarcode');
    Route::get( 'barcode/order/generate/{id_order}', [barcodeController::class, 'generateOrderBarcode'])->name('barcode.generateOrderBarcode');
    
    Route::get( 'barcode/erp/print/{id_order}', [barcodeController::class, 'printERPOrderBarcode'])->name('barcode.printERPOrderBarcode');
    Route::get( 'barcode/erp/print/{id_product}/{id_product_attribute}', [barcodeController::class, 'printProductStand'])->name('barcode.printProductStand');
    /**Route::get( 'barcode/stand/print/{id_product}/{id_product_attribute}', [barcodeController::class, 'printProductStand'])->name('barcode.printProductStand');**/
    Route::get( 'barcode/stand/print/{id_product}/{id_product_attribute}', [barcodeController::class, 'printProductStandString'])->name('barcode.printProductStandString');
    
    Route::get( 'barcode/stand/cell/print/{tag}', [barcodeController::class, 'printProductStandCell'])->name('barcode.printProductStandCell');
    
    //Route::get( 'barcode/product/print/{id_product}/{id_product_attribute}/{repeat}', [barcodeController::class, 'printProductBarcode'])->name('barcode.printProductBarcode');
    
    //Route::get( 'barcode/product/generate/{id_product}/{id_product_attribute}', [barcodeController::class, 'generateProductBarcode'])->name('barcode.generateProductBarcode');
    //Route::get( 'barcode/product/{barcode}/{id_product}/{id_product_attribute}', [barcodeController::class, 'checkAndShowBarcode'])->name('barcode.checkAndShowBarcode');
    /** BARCODE **/
    
    Route::get( 'customTools/stockEntry/listToRemove', [stockEntryController::class, 'listToRemove'])->name('stockEntry.listToRemove');
    Route::post( 'customTools/stockEntry/post',        [stockEntryController::class, 'post'])->name('stockEntry.post');
    
    Route::post( 'customTools/autoOrders/setAsOrdered',     [autoOrdersController::class, 'setAsOrdered'])->name('autoOrders.setAsOrdered');
    Route::post( 'customTools/autoOrders/getProductInfo',   [autoOrdersController::class, 'getProductInfo'])->name('autoOrders.getProductInfo');
    Route::post( 'customTools/autoOrders/getProductsInfo',  [autoOrdersController::class, 'getProductsInfo'])->name('autoOrders.getProductsInfo');
    Route::post( 'customTools/autoOrders/add',              [autoOrdersController::class, 'addToOrder'])->name('autoOrders.addToOrder');
    Route::post( 'customTools/autoOrders/update',           [autoOrdersController::class, 'updateOrder'])->name('autoOrders.updateOrder');
    Route::post( 'customTools/autoOrders/createOrder',      [autoOrdersController::class, 'saveOrder'])->name('autoOrders.saveOrder');
    
    Route::post( 'customTools/autoOrders/clean/brand/items',[autoOrdersController::class, 'cleanBranditems'])->name('autoOrders.cleanBranditems');
    Route::post( 'customTools/autoOrders/export/CSV',       [autoOrdersController::class, 'exportCSV'])->name('autoOrders.exportCSV');
    
    Route::post( 'customTools/autoOrders/load/products',    [autoOrdersController::class, 'loadProducts'])->name('autoOrders.loadProducts');
    Route::post( 'customTools/autoOrders/load/attributes',  [autoOrdersController::class, 'loadAttributes'])->name('autoOrders.loadAttributes');
    Route::post( 'customTools/autoOrders/new/order/scratch',[autoOrdersController::class, 'saveNewOrderFromScratch'])->name('autoOrders.saveNewOrderFromScratch');
    
    Route::post( 'customTools/uploads/upload',              [uploadsController::class, 'upload'])->name('uploads.upload');
    
    Route::post( 'customTools/suppliersBackorders/getSuppliersBackorders',      [suppliersBackordersController::class, 'getSuppliersBackorders'])->name('suppliersBackorders.getSuppliersBackorders');
    Route::get(  'customTools/suppliersBackorders/send/{id_supplier}/{token}',  [suppliersBackordersController::class, 'send_report'])->name('suppliersBackorders.send_report');
    
    Route::resources([ 'customTools/stockEntry'=>           stockEntryController::class]);
    Route::resources([ 'customTools/autoOrders'=>           autoOrdersController::class]);
    Route::resources([ 'customTools/backorders'=>           backordersController::class]);

    Route::post( 'customTools/backorders/getBackorderDetail', [backordersController::class, 'getOrderDetails'])->name('backorders.getBackorderDetail');
    
    Route::post( 'customTools/backorders/updateInfo',      [backordersController::class, 'updateInfo'])->name('backorders.updateInfo');
    Route::post( 'customTools/backorders/getProductInfo',  [backordersController::class, 'getProductInfo'])->name('backorders.getProductInfo');
    Route::post( 'customTools/backorders/setRowColor',     [backordersController::class, 'setRowColor'])->name('backorders.setRowColor');
    
    Route::resources([ 'customTools/suppliersBackorders'=>  suppliersBackordersController::class]);
    Route::resources([ 'customTools/uploads'=>              uploadsController::class]);
    
    
    Route::get(  'customTools/products/issues/index',       [productIssuesController::class, 'index']  )->name('productIssues.index');
    Route::post( 'customTools/products/issues/save',        [productIssuesController::class, 'store']  )->name('productIssues.store');
    Route::get(  'customTools/products/issues/edit/{id}',   [productIssuesController::class, 'edit']   )->name('productIssues.edit');
    Route::post( 'customTools/products/issues/update',      [productIssuesController::class, 'update'] )->name('productIssues.update');

    Route::get('/returns/{id?}', [returnsController::class, 'index'])->name('returns.index');
    Route::get('/returns/modal/{id}', [returnsController::class, 'getModal'])->name('returns.getModal');
    Route::post('/returns/changeStatus', [returnsController::class, 'changeStatus'])->name('returns.changeStatus');    
    
    Route::get('/warranties/{id?}', [warrantiesController::class, 'index'])->name('warranties.index');
    Route::get('/warranties/modal/{id}', [warrantiesController::class, 'getModal'])->name('warranties.getModal');
    Route::post('/warranties/changeStatus', [warrantiesController::class, 'changeStatus'])->name('warranties.changeStatus');
    
    /** pricing data **/
    Route::get( 'customTools/pricing/index',               [pricingConvertionController::class, 'index'] )->name('pricing.index');

    Route::get('/translations', [translationPhraseController::class, 'create'])->name('translation.index');
    Route::post('/translations', [translationPhraseController::class, 'store'])->name('translations.store');

    Route::get(  'customTools/erp/index/{list}',       [erpController::class, 'index']  )->name('erp.index');
    Route::post( 'customTools/erp/get/orders',  [erpController::class, 'ordersOfSupplier']  )->name('erp.ordersOfSupplier');
    Route::get(  'customTools/erp/get/order/{po_id}',  [erpController::class, 'getOrderDetailsOf']  )->name('erp.getOrderDetailsOf');

    /*PRICE REQUESTS*/

    Route::get('customTools/quotes/', [quotesController::class, 'index'])->name('quotes.index');
    Route::get('customTools/quotes/data', [quotesController::class, 'data'])->name('quotes.data');

    Route::post('customTools/quotes/', [quotesController::class, 'store'])->name('quotes.store');
    Route::put('customTools/quotes/{id}', [quotesController::class, 'update'])->name('quotes.update');
    Route::delete('customTools/quotes/{id}', [quotesController::class, 'destroy'])->name('quotes.destroy');


    Route::get('/web/logs', [logsController::class, 'index'])->name('logs.index');
    Route::get('/web/logs/{id}', [logsController::class, 'show'])->name('logs.show');

    Route::get('customTools/purchasePrice/list', [purchasePriceController::class, 'index'])->name('purchasePrice.index');
    Route::post('customTools/purchasePrice/update', [purchasePriceController::class, 'update'])->name('purchasePrice.update');
    Route::post('customTools/purchasePrice/updateAll', [purchasePriceController::class, 'updateAll'])->name('purchasePrice.updateAll');

    // Admin
    Route::middleware('role:admin')->prefix('web/tasks/admin')->name('tasks.admin.')->group(function(){
        Route::get('/', [taskController::class,'index'])->name('index');
        Route::get('/create', [taskController::class,'create'])->name('create');
        Route::post('/store', [taskController::class,'store'])->name('store');
        Route::get('/{id}', [taskController::class,'show'])->name('show');
        Route::get('/{id}/edit', [taskController::class,'edit'])->name('edit');
        Route::post('/{id}/update', [taskController::class,'update'])->name('update');
        Route::post('/{id}/validate-extra', [taskController::class,'validateExtra'])->name('validateExtra');
    
        // ✅ novo: excel inline update
        Route::post('/{id}/field', [taskController::class,'updateField'])->name('field');
    
        // ✅ novo: comentários/histórico para expand inline
        Route::get('/{id}/comments', [taskController::class,'comments'])->name('comments');

Route::get('/stats/month', [taskController::class,'statsMonth'])->name('stats.month');
Route::get('/stats/year', [taskController::class,'statsYear'])->name('stats.year');
        
    });


    // Manager
    Route::middleware('role:manager')->prefix('web/tasks/manager')->name('tasks.manager.')->group(function(){
        Route::get('/', [managerTaskController::class,'index'])->name('index');
        Route::get('/{id}', [managerTaskController::class,'show'])->name('show');
        Route::post('/{id}/assign', [managerTaskController::class,'assignUser'])->name('assign');
        Route::post('/{id}/status', [managerTaskController::class,'updateStatus'])->name('status');
        Route::post('/{id}/observations', [managerTaskController::class,'updateObservations'])->name('observations');
    });

    // User
    Route::middleware('role:user')->prefix('web/tasks/user')->name('tasks.user.')->group(function(){
        Route::get('/', [userTaskController::class,'index'])->name('index');
        Route::get('/{id}', [userTaskController::class,'show'])->name('show');
        Route::post('/{id}/status', [userTaskController::class,'updateStatus'])->name('status');
        Route::post('/{id}/comment', [userTaskController::class,'addComment'])->name('comment');
        Route::post('/{id}/upload', [taskFileController::class,'upload'])->name('upload');
    });

    // Files download (shared; authorization inside policy)
    Route::get('/files/{fileId}/download', [taskFileController::class,'download'])->name('tasks.files.download');

    // Reports
    Route::middleware('role:admin,manager')->prefix('reports')->name('tasks.reports.')->group(function(){
        Route::get('/monthly', [productivityController::class,'monthly'])->name('monthly');
        Route::get('/annual', [productivityController::class,'annual'])->name('annual');
    });
    


});


/** Checklist **/
/** Checklist App Employee **/
Route::get(  'customTools/checklist/history/{department}',     [employeeChecklistController::class, 'history'])->name('checklist.history');
Route::get(  'customTools/checklist/today',                    [employeeChecklistController::class, 'today'])->name('checklist.today');
Route::patch('customTools/checklist/{task}/status',            [employeeChecklistController::class, 'updateStatus'])->name('checklist.updateStatus');
Route::patch('customTools/checklist/note',                     [employeeChecklistController::class, 'updateNote'])->name('checklist.updateNote');

Route::get(   'customTools/checklist',                         [checklistManagerController::class, 'index'])->name('checklist.index');
Route::get(   'customTools/checklist/create',                  [checklistManagerController::class, 'create'])->name('checklist.create');
Route::post(  'customTools/checklist/',                        [checklistManagerController::class, 'store'])->name('checklist.store');
Route::get(   'customTools/checklist/{id}/{template}/edit',    [checklistManagerController::class, 'edit'])->name('checklist.edit');
Route::put(   'customTools/checklist/{template}',              [checklistManagerController::class, 'update'])->name('checklist.update');
Route::delete('customTools/checklist/{template}',              [checklistManagerController::class, 'destroy'])->name('checklist.destroy');

/** Checklist admin assignment**/
Route::get('customTools/checklist/assignEmployees', [checklistManagerController::class, 'assignEmployees'])->name('checklist.assignEmployees');
Route::post('customTools/checklist/assignEmployees', [checklistManagerController::class, 'updateEmployeeAdmins'])->name('checklist.updateEmployeeAdmins');

Route::get('/checklist-carry-over', function() {
    // link para passar tasks pending para o dia de hoje.


    $today = Carbon::today();
    $yesterdayOrEarlier = $today->copy()->subDay();
    

    $mainTasksToCarry = daily_checklist::where('main_task', true)
        ->get();

    $carriedCount = 0;

    foreach ($mainTasksToCarry as $task) {
        // Check if this main task already exists for today
        $exists = daily_checklist::where('template_id', $task->template_id)
            ->where('department_id', $task->department_id)
            ->whereDate('for_date', $today)
            ->exists();

        if (!$exists) {
            // Optional: adjust priority? You can keep same or reduce like before
            // $newPriority = $task->state_priority == 3 ? 2 : ($task->state_priority == 2 ? 1 : 1);

            daily_checklist::create([
                'for_date'       => $today,
                'template_id'    => $task->template_id,
                'admin_id'       => $task->admin_id,
                'employee_id'    => $task->employee_id,
                'department_id'    => $task->department_id,
                'status'         => 'pending', 
                // 'notes'          => $task->notes,
                // 'state_priority' => $newPriority, 
                'main_task'      => false, 
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $carriedCount++;
        }
    }

    return response()->json([
        'message' => $carriedCount . ' main tasks carried over to ' . $today->toDateString(),
        'count' => $carriedCount
    ]);
});

/**

Route::get('/db-comparision', function () {
    
    $oldConnection = 'mysql2'; // Definir no config/database.php
    $newConnection = 'mysql3'; // Conex達o padr達o do Laravel
    
    $oldTables = DB::connection($oldConnection)->select("SHOW TABLES");
    $newTables = DB::connection($newConnection)->select("SHOW TABLES");
    
    

    $oldTableNames = array_map('current', json_decode(json_encode($oldTables), true));
    $newTableNames = array_map('current', json_decode(json_encode($newTables), true));

    $tableDifferences = [
        'only_in_old' => array_diff($oldTableNames, $newTableNames),
        'only_in_new' => array_diff($newTableNames, $oldTableNames),
        'common' => array_intersect($oldTableNames, $newTableNames)
    ];

    $columnDifferences = [];
    foreach ($tableDifferences['common'] as $table) {
        $oldColumns = DB::connection($oldConnection)->select("SHOW COLUMNS FROM `$table`");
        $newColumns = DB::connection($newConnection)->select("SHOW COLUMNS FROM `$table`");
        
        $oldColumnNames = array_column($oldColumns, 'Field');
        $newColumnNames = array_column($newColumns, 'Field');
        
        $columnDifferences[$table] = [
            'only_in_old' => array_diff($oldColumnNames, $newColumnNames),
            'only_in_new' => array_diff($newColumnNames, $oldColumnNames),
            'modified' => []
        ];

        foreach ($oldColumns as $oldColumn) {
            foreach ($newColumns as $newColumn) {
                if ($oldColumn->Field === $newColumn->Field && $oldColumn->Type !== $newColumn->Type) {
                    $columnDifferences[$table]['modified'][$oldColumn->Field] = [
                        'old' => $oldColumn->Type,
                        'new' => $newColumn->Type
                    ];
                }
            }
        }
    }
    
    return view('bd_compare', compact('tableDifferences', 'columnDifferences'));
});
*/