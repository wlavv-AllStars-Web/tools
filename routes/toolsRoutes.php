<?php

use App\Http\Controllers\CustomTools\statsController;


use App\Http\Controllers\CustomTools\barcodeController;
use App\Http\Controllers\CustomTools\stockEntryController;
use App\Http\Controllers\CustomTools\autoOrdersController;
use App\Http\Controllers\CustomTools\backordersController;
use App\Http\Controllers\CustomTools\suppliersBackordersController;
use App\Http\Controllers\CustomTools\uploadsController;
use App\Http\Controllers\CustomTools\dpdController;
use App\Http\Controllers\CustomTools\documentsManagerController;
use App\Http\Controllers\CustomTools\housingController;
use App\Http\Controllers\CustomTools\pickingController;
use App\Http\Controllers\CustomTools\carrierIssuesController;
use App\Http\Controllers\CustomTools\carrierReturnController;
use App\Http\Controllers\CustomTools\compatsController;
use App\Http\Controllers\CustomTools\checkVatController;
use App\Http\Controllers\CustomTools\shippingController;
use App\Http\Controllers\CustomTools\suppliersIssuesController;
use App\Http\Controllers\CustomTools\suppliersMapController;
use App\Http\Controllers\CustomTools\dashboardController;
use App\Http\Controllers\CustomTools\searchController;
use App\Http\Controllers\CustomTools\priceMapController;
use App\Http\Controllers\CustomTools\refundController;
use App\Http\Controllers\CustomTools\tvController;
use App\Http\Controllers\CustomTools\basePriceController;
use App\Http\Controllers\CustomTools\productIssuesController;
use App\Http\Controllers\CustomTools\pricingConvertionController;
use App\Http\Controllers\CustomTools\checklistManagerController;
use App\Http\Controllers\CustomTools\employeeChecklistController;
use App\Http\Controllers\CustomTools\translationPhraseController;
use App\Http\Controllers\CustomTools\returnsController;
use App\Http\Controllers\CustomTools\warrantiesController;
use App\Http\Controllers\CustomTools\erpController;
use App\Http\Controllers\CustomTools\SiteSeoCompareController;
use App\Http\Controllers\CustomTools\SiteTextSideBySideController;
use App\Http\Controllers\CustomTools\ChangeTrackerController;
use App\Http\Controllers\CustomTools\quotesController;
use App\Http\Controllers\CustomTools\Tasks\taskController;
use App\Http\Controllers\CustomTools\Tasks\managerTaskController;
use App\Http\Controllers\CustomTools\Tasks\userTaskController;
use App\Http\Controllers\CustomTools\Tasks\taskFileController;
use App\Http\Controllers\CustomTools\Tasks\productivityController;
use App\Http\Controllers\CustomTools\logsController;
use App\Http\Controllers\CustomTools\AsgTasksController;
use App\Http\Controllers\CustomTools\purchasePriceController;
use App\Http\Controllers\CustomTools\SafetyCheckController;
use App\Http\Controllers\CustomTools\CarrierExpeditionCheckController;
use App\Http\Controllers\CustomTools\CarrierEndOfDayDocumentController;
use App\Http\Controllers\CustomTools\LogisticsRmaCheckController;
use App\Http\Controllers\CustomTools\LogisticsInventoryController;
use App\Http\Controllers\CustomTools\HomepageAdminController;
use App\Http\Controllers\CustomTools\HomepageASDAdminController;
use App\Http\Controllers\CustomTools\AsdResourcesController;
use App\Http\Controllers\CustomTools\asmResourcesController;
use App\Http\Controllers\CustomTools\asgCarsController;
use App\Http\Controllers\CustomTools\PaymentLinkRequestController;
use App\Http\Controllers\CustomTools\AsdAlertController;
use App\Http\Controllers\CustomTools\ToolsMigrationController;

use App\Http\Controllers\CustomTools\CurrencyVariationController;
use App\Http\Controllers\Modules\oms\DashboardController as OmsDashboardController;
use App\Http\Controllers\Modules\oms\OrderNoteController;
use App\Http\Controllers\Modules\oms\BilledOrderController;
use App\Http\Controllers\Modules\oms\BillingController;
use App\Http\Controllers\Modules\oms\ReceptionController;
use App\Http\Controllers\Modules\oms\SupplierInvoiceController;
use App\Http\Controllers\Modules\oms\SupplierTermLevelController;
use App\Http\Controllers\Modules\oms\LogisticContainerController;
use App\Http\Controllers\Modules\oms\HistoryController;

use App\Models\modules\checklist\daily_checklist;

Use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Canonical area tool routes
|--------------------------------------------------------------------------
| These routes expose the tools by area/tool/action while the historical
| customTools routes remain available for compatibility.
*/
Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('dashboard')->name('dashboard.tools.')->group(function () {
        Route::get('/daily', [dashboardController::class, 'index'])->name('daily');
        Route::get('/kpi', [statsController::class, 'kpi'])->name('kpi');
        Route::get('/changes', [ChangeTrackerController::class, 'index'])->name('changes');
    });

    Route::prefix('web')->name('web.tools.')->group(function () {
        Route::get('/tracking', [translationPhraseController::class, 'create'])->name('tracking.index');
        Route::post('/tracking', [translationPhraseController::class, 'store'])->name('tracking.store');
        Route::get('/seo', [SiteSeoCompareController::class, 'index'])->name('seo.index');
        Route::post('/seo', [SiteSeoCompareController::class, 'compare'])->name('seo.compare');
        Route::get('/raw-text', [SiteTextSideBySideController::class, 'index'])->name('raw_text.index');
        Route::post('/raw-text', [SiteTextSideBySideController::class, 'compare'])->name('raw_text.compare');

        Route::prefix('changes')->name('changes.')->group(function () {
            Route::get('/', [ChangeTrackerController::class, 'index'])->name('index');
            Route::get('/create', [ChangeTrackerController::class, 'create'])->name('create');
            Route::post('/store', [ChangeTrackerController::class, 'store'])->name('store');
            Route::get('/{id}', [ChangeTrackerController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [ChangeTrackerController::class, 'edit'])->name('edit');
            Route::post('/{id}/update', [ChangeTrackerController::class, 'update'])->name('update');
            Route::get('/{projectId}/files/{fileId}/download', [ChangeTrackerController::class, 'downloadFile'])->name('files.download');
            Route::post('/{projectId}/errors/store', [ChangeTrackerController::class, 'storeError'])->name('errors.store');
            Route::post('/{projectId}/errors/{errorId}/update', [ChangeTrackerController::class, 'updateError'])->name('errors.update');
            Route::post('/{projectId}/files/{fileId}/delete', [ChangeTrackerController::class, 'deleteFile'])->name('files.delete');
        });

        Route::prefix('db-migration')->name('db_migration.')->group(function () {
            Route::get('/', [ToolsMigrationController::class, 'index'])->name('index');
            Route::get('/table/{table}', [ToolsMigrationController::class, 'table'])->name('table')->where('table', '[A-Za-z0-9_]+');
            Route::get('/table/{table}/row/{id}', [ToolsMigrationController::class, 'row'])->name('row')->where('table', '[A-Za-z0-9_]+');
            Route::post('/table/{table}/sync', [ToolsMigrationController::class, 'sync'])->name('sync')->where('table', '[A-Za-z0-9_]+');
            Route::post('/table/{table}/clear', [ToolsMigrationController::class, 'clear'])->name('clear')->where('table', '[A-Za-z0-9_]+');
            Route::post('/table/{table}/row/{id}/sync', [ToolsMigrationController::class, 'syncRow'])->name('sync_row')->where('table', '[A-Za-z0-9_]+');
        });
    });

    Route::prefix('admin')->name('admin.tools.')->group(function () {
        Route::get('/tasks', [AsgTasksController::class, 'index'])->name('asg_tasks.index');
        Route::post('/tasks', [AsgTasksController::class, 'store'])->name('asg_tasks.store');
        Route::patch('/tasks/{task}/inline', [AsgTasksController::class, 'inlineUpdate'])->name('asg_tasks.inline');

        Route::middleware('role:admin')->prefix('tasks/admin')->name('tasks.admin.')->group(function(){
            Route::get('/', [taskController::class,'index'])->name('index');
            Route::post('/store', [taskController::class,'store'])->name('store');
            Route::post('/{id}/update', [taskController::class,'update'])->name('update');
            Route::post('/{id}/field', [taskController::class,'updateField'])->name('field');
            Route::get('/{id}/comments', [taskController::class,'comments'])->name('comments');
        });

        Route::middleware('role:manager')->prefix('tasks/manager')->name('tasks.manager.')->group(function(){
            Route::get('/', [managerTaskController::class,'index'])->name('index');
            Route::get('/{id}', [managerTaskController::class,'show'])->name('show');
            Route::post('/{id}/assign', [managerTaskController::class,'assignUser'])->name('assign');
            Route::post('/{id}/status', [managerTaskController::class,'updateStatus'])->name('status');
            Route::post('/{id}/observations', [managerTaskController::class,'updateObservations'])->name('observations');
        });

        Route::middleware('role:user')->prefix('tasks/user')->name('tasks.user.')->group(function(){
            Route::get('/', [userTaskController::class,'index'])->name('index');
            Route::get('/{id}', [userTaskController::class,'show'])->name('show');
            Route::post('/{id}/status', [userTaskController::class,'updateStatus'])->name('status');
            Route::post('/{id}/comment', [userTaskController::class,'addComment'])->name('comment');
            Route::post('/{id}/upload', [taskFileController::class,'upload'])->name('upload');
        });

        Route::get('/tasks/files/{fileId}/download', [taskFileController::class,'download'])->name('tasks.files.download');

        Route::middleware('role:admin,manager')->prefix('tasks/reports')->name('tasks.reports.')->group(function(){
            Route::get('/monthly', [productivityController::class,'monthly'])->name('monthly');
            Route::get('/annual', [productivityController::class,'annual'])->name('annual');
        });

        Route::prefix('asd-alerts')->name('asd_alerts.')->group(function () {
            Route::get('/', [AsdAlertController::class, 'index'])->name('index');
            Route::get('/create', [AsdAlertController::class, 'create'])->name('create');
            Route::post('/', [AsdAlertController::class, 'store'])->name('store');
            Route::get('/{asdAlert}/edit', [AsdAlertController::class, 'edit'])->name('edit');
            Route::put('/{asdAlert}', [AsdAlertController::class, 'update'])->name('update');
            Route::delete('/{asdAlert}', [AsdAlertController::class, 'destroy'])->name('destroy');
        });

        Route::redirect('/oms/logistic-containers', '/logistics/oms/logistic-containers');

        Route::prefix('oms')->name('oms.')->group(function () {
            Route::get('/', [OmsDashboardController::class, 'index'])->name('dashboard');
            Route::get('/fragments/documents', [OmsDashboardController::class, 'documentsFragment'])->name('dashboard.fragments.documents');
            Route::get('/fragments/summary', [OmsDashboardController::class, 'summaryFragment'])->name('dashboard.fragments.summary');
            Route::get('/fragments/stats', [OmsDashboardController::class, 'statsFragment'])->name('dashboard.fragments.stats');
            Route::get('/dashboard/export/csv', [OmsDashboardController::class, 'exportCsv'])->name('dashboard.export.csv');

            Route::get('/order-notes', [OrderNoteController::class, 'index'])->name('order_notes.index');
            Route::get('/order-notes/create', [OrderNoteController::class, 'create'])->name('order_notes.create');
            Route::post('/order-notes/create-from-supplier/{supplierId}', [OrderNoteController::class, 'createFromSupplier'])->name('order_notes.create_from_supplier');
            Route::post('/order-notes', [OrderNoteController::class, 'store'])->name('order_notes.store');
            Route::get('/order-notes/{orderNote}', [OrderNoteController::class, 'show'])->name('order_notes.show');
            Route::get('/order-notes/{orderNote}/edit', [OrderNoteController::class, 'edit'])->name('order_notes.edit');
            Route::put('/order-notes/{orderNote}', [OrderNoteController::class, 'update'])->name('order_notes.update');
            Route::delete('/order-notes/{orderNote}', [OrderNoteController::class, 'destroy'])->name('order_notes.destroy');
            Route::post('/order-notes/{orderNote}/lines', [OrderNoteController::class, 'addLine'])->name('order_notes.lines.store');
            Route::patch('/order-notes/{orderNote}/lines/{line}', [OrderNoteController::class, 'updateLine'])->name('order_notes.lines.update');
            Route::delete('/order-notes/{orderNote}/lines/{line}', [OrderNoteController::class, 'destroyLine'])->name('order_notes.lines.destroy');
            Route::get('/order-notes/{orderNote}/supplier-products', [OrderNoteController::class, 'supplierProducts'])->name('order_notes.supplier_products');
            Route::post('/order-notes/{orderNote}/import/csv/preview', [OrderNoteController::class, 'importCsvPreview'])->name('order_notes.import.preview');
            Route::get('/order-notes/{orderNote}/import/csv/verify', [OrderNoteController::class, 'importCsvVerify'])->name('order_notes.import.verify');
            Route::post('/order-notes/{orderNote}/import/csv/confirm', [OrderNoteController::class, 'importCsvConfirm'])->name('order_notes.import.confirm');
            Route::post('/order-notes/{orderNote}/notes', [OrderNoteController::class, 'saveNotes'])->name('order_notes.notes.save');
            Route::post('/order-notes/{orderNote}/lines/{line}/notes', [OrderNoteController::class, 'saveLineNotes'])->name('order_notes.lines.notes.save');
            Route::get('/order-notes/{orderNote}/export/csv', [OrderNoteController::class, 'exportCsv'])->name('order_notes.export.csv');
            Route::get('/order-notes/{orderNote}/export/pdf', [OrderNoteController::class, 'exportPdf'])->name('order_notes.export.pdf');
            Route::get('/order-notes/{orderNote}/invoice', [SupplierInvoiceController::class, 'create'])->name('invoices.create');
            Route::post('/order-notes/{orderNote}/invoice', [SupplierInvoiceController::class, 'store'])->name('invoices.store');
            Route::post('/order-notes/{orderNote}/bill', [BillingController::class, 'store'])->name('billing.store');

            Route::get('/billed-orders', [BilledOrderController::class, 'index'])->name('billed_orders.index');
            Route::get('/billed-orders/{billedOrder}', [BilledOrderController::class, 'show'])->name('billed_orders.show');
            Route::post('/billed-orders/{billedOrder}/notes', [BilledOrderController::class, 'saveNotes'])->name('billed_orders.notes.save');
            Route::post('/billed-orders/{billedOrder}/lines/{line}/notes', [BilledOrderController::class, 'saveLineNotes'])->name('billed_orders.lines.notes.save');
            Route::post('/billed-orders/{billedOrder}/shipment', [BilledOrderController::class, 'saveShipmentRelation'])->name('billed_orders.shipment.save');
            Route::get('/billed-orders/{billedOrder}/export/csv', [BilledOrderController::class, 'exportCsv'])->name('billed_orders.export.csv');
            Route::get('/billed-orders/{billedOrder}/export/pdf', [BilledOrderController::class, 'exportPdf'])->name('billed_orders.export.pdf');

            Route::get('/receptions', [ReceptionController::class, 'index'])->name('receptions.index');
            Route::post('/billed-orders/{billedOrder}/receive', [ReceptionController::class, 'store'])->name('receptions.store');
            Route::get('/billed-orders/{billedOrder}/receptions', [ReceptionController::class, 'history'])->name('receptions.history');
            Route::get('/invoices/{invoice}/receptions', [ReceptionController::class, 'invoiceHistory'])->name('receptions.invoice_history');
            Route::get('/receptions/export/csv', [ReceptionController::class, 'exportCsv'])->name('receptions.export.csv');

            Route::get('/invoices', [SupplierInvoiceController::class, 'index'])->name('invoices.index');
            Route::get('/invoices/{invoice}', [SupplierInvoiceController::class, 'show'])->name('invoices.show');
            Route::post('/invoices/{invoice}/shipment', [SupplierInvoiceController::class, 'saveShipmentRelation'])->name('invoices.shipment.save');
            Route::post('/invoices/{invoice}/close', [SupplierInvoiceController::class, 'close'])->name('invoices.close');
            Route::post('/invoices/{invoice}/cancel', [SupplierInvoiceController::class, 'cancel'])->name('invoices.cancel');
            Route::get('/invoices/{invoice}/export/csv', [SupplierInvoiceController::class, 'exportCsv'])->name('invoices.export.csv');
            Route::get('/invoices/{invoice}/export/pdf', [SupplierInvoiceController::class, 'exportPdf'])->name('invoices.export.pdf');

            Route::get('/supplier-terms/{supplierId}', [SupplierTermLevelController::class, 'index'])->name('supplier_terms.index');
            Route::post('/supplier-terms/{supplierId}', [SupplierTermLevelController::class, 'store'])->name('supplier_terms.store');
            Route::put('/supplier-terms/{level}', [SupplierTermLevelController::class, 'update'])->name('supplier_terms.update');
            Route::delete('/supplier-terms/{level}', [SupplierTermLevelController::class, 'destroy'])->name('supplier_terms.destroy');

            Route::prefix('logistic-containers')->name('logistic_containers.')->group(function () {
                Route::get('/', fn () => redirect()->route('logistics.tools.oms.logistic_containers.index'))->name('index');
                Route::get('/create', fn () => redirect()->route('logistics.tools.oms.logistic_containers.index'))->name('create');
                Route::post('/', fn () => redirect()->route('logistics.tools.oms.logistic_containers.index'))->name('store');
                Route::get('/{id}/edit', fn () => redirect()->route('logistics.tools.oms.logistic_containers.index'))->name('edit');
                Route::put('/{id}', fn () => redirect()->route('logistics.tools.oms.logistic_containers.index'))->name('update');
                Route::delete('/{id}', fn () => redirect()->route('logistics.tools.oms.logistic_containers.index'))->name('destroy');
            });

            Route::prefix('history')->name('history.')->group(function () {
                Route::get('/prices', [HistoryController::class, 'prices'])->name('prices');
                Route::get('/stock', [HistoryController::class, 'stock'])->name('stock');
                Route::get('/invoice/{billedOrderId}/prices', [HistoryController::class, 'pricesByInvoice'])->name('invoice.prices');
                Route::get('/reception/{receptionId}/stock', [HistoryController::class, 'stockByReception'])->name('reception.stock');
            });
        });

        Route::get('/compats', [compatsController::class, 'index'])->name('compats.index');
        Route::post('/compats/options/edit', [compatsController::class, 'updateTag'])->name('compats.update_tag');
        Route::post('/compats/get/options', [compatsController::class, 'getOptions'])->name('compats.get_options');
        Route::post('/compats/get/options/modal', [compatsController::class, 'getOptionsForModal'])->name('compats.get_options_for_modal');
        Route::post('/compats/create/compatibilities', [compatsController::class, 'createCompatibilities'])->name('compats.create_compatibilities');
        Route::post('/compats/create/relationship', [compatsController::class, 'saveNewRelationship'])->name('compats.save_new_relationship');
        Route::post('/compats/edit/logo', [compatsController::class, 'editImage'])->name('compats.edit_image');
        Route::post('/compats/options/edit/options', [compatsController::class, 'setData'])->name('compats.set_data');
        Route::post('/compats/remove/compat', [compatsController::class, 'removeCompat'])->name('compats.remove_compat');
        Route::get('/compats/menu/update-menu', [compatsController::class, 'updateMenu'])->name('compats.update_menu');
        Route::post('/compats/menu/set-order', [compatsController::class, 'setOrder'])->name('compats.set_order');
    });

    Route::prefix('finance')->name('finance.tools.')->group(function () {
        Route::get('/intrastat', [\App\Http\Controllers\Areas\financeController::class, 'download_intrastat'])->name('intrastat.index');
        Route::post('/intrastat/importacao', [\App\Http\Controllers\Areas\financeController::class, 'intrastat_import'])->name('intrastat.import');
        Route::post('/intrastat/exportacao', [\App\Http\Controllers\Areas\financeController::class, 'intrastat_export'])->name('intrastat.export');
        Route::post('/intrastat/save-currency-rate', [\App\Http\Controllers\Areas\financeController::class, 'save_currency_rate'])->name('intrastat.save_currency_rate');

        Route::get('/carrier-check', [carrierIssuesController::class, 'verificationIndex'])->name('carrier_check.index');
        Route::post('/carrier-check/upload', [carrierIssuesController::class, 'verificationUpload'])->name('carrier_check.upload');
        Route::post('/carrier-check/check', [carrierIssuesController::class, 'carrierVerify'])->name('carrier_check.verify');

        Route::get('/carrier-returns', [carrierReturnController::class, 'index'])->name('carrier_returns.index');
        Route::post('/carrier-returns/add', [carrierReturnController::class, 'store'])->name('carrier_returns.store');
        Route::post('/carrier-returns/update', [carrierReturnController::class, 'update'])->name('carrier_returns.update');
        Route::post('/carrier-returns/archive', [carrierReturnController::class, 'archive'])->name('carrier_returns.archive');

        Route::get('/refunds', [refundController::class, 'index'])->name('refunds.index');
        Route::post('/refunds/new', [refundController::class, 'newRefund'])->name('refunds.new');
        Route::post('/refunds/get-info', [refundController::class, 'getInfo'])->name('refunds.get_info');
        Route::post('/refunds/edit', [refundController::class, 'editRefund'])->name('refunds.edit');
        Route::post('/refunds/update', [refundController::class, 'updateRefund'])->name('refunds.update');

        Route::get('/vat', fn () => redirect()->route('finance.tools.vat.check'))->name('vat.index');
        Route::get('/vat/check', [checkVatController::class, 'index'])->name('vat.check');
        Route::get('/vat/verify', [checkVatController::class, 'verify'])->name('vat.verify');

        Route::prefix('payment-links')->name('payment_links.')->group(function () {
            Route::get('/', [PaymentLinkRequestController::class, 'financeIndex'])->name('index');
            Route::get('/archive', [PaymentLinkRequestController::class, 'financeArchive'])->name('archive');
            Route::get('/{paymentLinkRequest}', [PaymentLinkRequestController::class, 'show'])->name('show');
            Route::post('/{paymentLinkRequest}/approve', [PaymentLinkRequestController::class, 'approve'])->name('approve');
        });
    });

    Route::prefix('logistics')->name('logistics.tools.')->group(function () {
        Route::get('/stats', [dashboardController::class, 'index'])->name('stats.index');

        Route::get('/shipping', [shippingController::class, 'index'])->name('shipping.index');
        Route::post('/shipping/add/eta/delay', [shippingController::class, 'addDelay'])->name('shipping.add_delay');
        Route::get('/shipping/add', [shippingController::class, 'add'])->name('shipping.add');
        Route::post('/shipping/save', [shippingController::class, 'store'])->name('shipping.store');
        Route::get('/shipping/edit/{id}', [shippingController::class, 'edit'])->name('shipping.edit');
        Route::post('/shipping/update/{id}', [shippingController::class, 'update'])->name('shipping.update');
        Route::post('/shipping/downloadData', [shippingController::class, 'downloadData'])->name('shipping.download_data');
        Route::get('/shipping/packingList', [shippingController::class, 'packingList'])->name('shipping.packing_list');
        Route::post('/shipping/packingList/export-xls', [shippingController::class, 'exportPackingListXls'])->name('shipping.packing_list.export_xls');

        Route::prefix('carrier-check')->name('carrier_check.')->group(function () {
            Route::get('/', [CarrierExpeditionCheckController::class, 'index'])->name('index');
            Route::post('/store', [CarrierExpeditionCheckController::class, 'store'])->name('store');
            Route::get('/history', [CarrierExpeditionCheckController::class, 'history'])->name('history');
            Route::get('/export', [CarrierExpeditionCheckController::class, 'exportCsv'])->name('export');
        });

        Route::prefix('shipments-check')->name('shipments_check.')->group(function () {
            Route::get('/', [CarrierExpeditionCheckController::class, 'index'])->name('index');
            Route::post('/store', [CarrierExpeditionCheckController::class, 'store'])->name('store');
            Route::get('/history', [CarrierExpeditionCheckController::class, 'history'])->name('history');
            Route::get('/export', [CarrierExpeditionCheckController::class, 'exportCsv'])->name('export');
        });

        Route::prefix('carrier-end-of-day')->name('carrier_end_of_day.')->group(function () {
            Route::get('/', [CarrierEndOfDayDocumentController::class, 'index'])->name('index');
            Route::post('/documents', [CarrierEndOfDayDocumentController::class, 'store'])->name('store');
            Route::get('/documents/{document}', [CarrierEndOfDayDocumentController::class, 'show'])->name('show');
            Route::get('/documents/{document}/print', [CarrierEndOfDayDocumentController::class, 'print'])->name('print');
            Route::get('/documents/{document}/pdf', [CarrierEndOfDayDocumentController::class, 'pdf'])->name('pdf');
        });

        Route::prefix('rma-check')->name('rma_check.')->group(function () {
            Route::get('/', [LogisticsRmaCheckController::class, 'index'])->name('index');
            Route::post('/check', [LogisticsRmaCheckController::class, 'check'])->name('check');
        });

        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [LogisticsInventoryController::class, 'index'])->name('index');
            Route::get('/prepare', [LogisticsInventoryController::class, 'prepare'])->name('prepare');
            Route::post('/prepare', [LogisticsInventoryController::class, 'prepareStore'])->name('prepare.store');
            Route::get('/work', [LogisticsInventoryController::class, 'work'])->name('work');
            Route::post('/work', [LogisticsInventoryController::class, 'workStore'])->name('work.store');
            Route::get('/count/{schedule}', [LogisticsInventoryController::class, 'count'])->name('count');
            Route::post('/count/{schedule}', [LogisticsInventoryController::class, 'countStore'])->name('count.store');

            Route::middleware('role:admin,manager')->prefix('admin')->name('admin.')->group(function () {
                Route::get('/', [LogisticsInventoryController::class, 'admin'])->name('index');
                Route::get('/map', [LogisticsInventoryController::class, 'map'])->name('map');
                Route::get('/map/columns', [LogisticsInventoryController::class, 'mapColumns'])->name('map.columns');
                Route::get('/map/cells', [LogisticsInventoryController::class, 'mapCells'])->name('map.cells');
                Route::get('/map/products', [LogisticsInventoryController::class, 'mapProducts'])->name('map.products');
                Route::post('/schedule', [LogisticsInventoryController::class, 'scheduleStore'])->name('schedule.store');
                Route::delete('/schedule/{schedule}', [LogisticsInventoryController::class, 'scheduleDestroy'])->name('schedule.destroy');
                Route::get('/verification', [LogisticsInventoryController::class, 'verification'])->name('verification');
                Route::post('/verification/recount/{count}', [LogisticsInventoryController::class, 'requestRecount'])->name('verification.recount');
                Route::post('/verification/comment/{count}', [LogisticsInventoryController::class, 'saveVerificationComment'])->name('verification.comment');
                Route::post('/verification/{schedule}', [LogisticsInventoryController::class, 'verify'])->name('verification.verify');
                Route::get('/report', [LogisticsInventoryController::class, 'report'])->name('report');
                Route::get('/report/csv', [LogisticsInventoryController::class, 'reportCsv'])->name('report.csv');
                Route::get('/report/pdf', [LogisticsInventoryController::class, 'reportPdf'])->name('report.pdf');
            });
        });

        Route::get('/picking', [pickingController::class, 'index'])->name('picking.index');
        Route::get('/picking/index', [pickingController::class, 'index'])->name('picking.legacy_index');
        Route::post('/picking/row-done', [pickingController::class, 'rowDone'])->name('picking.row_done');
        Route::post('/picking/container', [pickingController::class, 'saveContainer'])->name('picking.container');
        Route::post('/picking/get-ean', [pickingController::class, 'getEAN'])->name('picking.get_ean');

        Route::get('/housing', [housingController::class, 'index'])->name('housing.index');
        Route::get('/housing/index', [housingController::class, 'index'])->name('housing.legacy_index');
        Route::post('/housing/request-data', [housingController::class, 'requestData'])->name('housing.request_data');
        Route::post('/housing/save-data', [housingController::class, 'saveData'])->name('housing.save_data');
        Route::post('/housing/edit-location', [housingController::class, 'editLocation'])->name('housing.edit_location');
        Route::post('/housing/edit-measures', [housingController::class, 'editMeasures'])->name('housing.edit_measures');
        Route::post('/housing/edit-reference', [housingController::class, 'editReference'])->name('housing.edit_reference');
        Route::post('/housing/edit-ean13', [housingController::class, 'editEan13'])->name('housing.edit_ean13');
        Route::post('/housing/edit-stock', [housingController::class, 'editStock'])->name('housing.edit_stock');
        Route::post('/housing/edit-stock-arrive', [housingController::class, 'editStockArrive'])->name('housing.edit_stock_arrive');
        Route::post('/housing/bulk-lookup-product', [housingController::class, 'bulkLookupProduct'])->name('housing.bulk_lookup_product');
        Route::post('/housing/bulk-save-housing', [housingController::class, 'bulkSaveHousing'])->name('housing.bulk_save_housing');

        Route::get('/stock-entry/list-to-remove', [stockEntryController::class, 'listToRemove'])->name('stock_entry.list_to_remove');
        Route::post('/stock-entry/post', [stockEntryController::class, 'post'])->name('stock_entry.post');
        Route::resource('/stock-entry', stockEntryController::class)->only(['show', 'update', 'destroy'])->names('stock_entry');
        Route::get('/stockEntry/{stockEntry}', [stockEntryController::class, 'show'])->name('stockEntry.show');
        Route::put('/stockEntry/{stockEntry}', [stockEntryController::class, 'update'])->name('stockEntry.update');
        Route::delete('/stockEntry/{stockEntry}', [stockEntryController::class, 'destroy'])->name('stockEntry.destroy');

        Route::prefix('safety-check')->name('safety_check.')->group(function () {
            Route::get('/', [SafetyCheckController::class, 'index'])->name('index');
            Route::post('/store', [SafetyCheckController::class, 'store'])->name('store');
            Route::get('/history', [SafetyCheckController::class, 'history'])->name('history');
            Route::get('/export', [SafetyCheckController::class, 'exportCsv'])->name('export');
        });

        Route::get('/carrier-issues', [carrierIssuesController::class, 'index'])->name('carrier_issues.index');
        Route::get('/carrier/issues/index', [carrierIssuesController::class, 'index'])->name('carrier_issues.legacy_index');
        Route::post('/carrier-issues/save', [carrierIssuesController::class, 'store'])->name('carrier_issues.store');
        Route::post('/carrier-issues/archive', [carrierIssuesController::class, 'archive'])->name('carrier_issues.archive');
        Route::post('/carrier-issues/update', [carrierIssuesController::class, 'update'])->name('carrier_issues.update');
        Route::post('/carrier-issues/edit', [carrierIssuesController::class, 'edit'])->name('carrier_issues.edit');
        Route::post('/carrier-issues/destroy', [carrierIssuesController::class, 'destroy'])->name('carrier_issues.destroy');

        Route::get('/suppliers/issues/{type}', [suppliersIssuesController::class, 'index'])->name('suppliers.issues.index');
        Route::post('/suppliers/issues/delivery/new', [suppliersIssuesController::class, 'newDeliveryIssue'])->name('suppliers.issues.delivery.new');
        Route::post('/suppliers/issues/delivery/update', [suppliersIssuesController::class, 'updateDeliveryIssue'])->name('suppliers.issues.delivery.update');
        Route::post('/suppliers/issues/delivery/close', [suppliersIssuesController::class, 'closeDeliveryIssue'])->name('suppliers.issues.delivery.close');
        Route::post('/suppliers/issues/warranty/new', [suppliersIssuesController::class, 'newWarrantyIssue'])->name('suppliers.issues.warranty.new');
        Route::post('/suppliers/issues/warranty/update', [suppliersIssuesController::class, 'updateWarrantyIssue'])->name('suppliers.issues.warranty.update');
        Route::post('/suppliers/issues/warranty/close', [suppliersIssuesController::class, 'closeWarrantyIssue'])->name('suppliers.issues.warranty.close');
        Route::post('/suppliers/issues/new', [suppliersIssuesController::class, 'newSupplierIssue'])->name('suppliers.issues.new');
        Route::post('/suppliers/issues/update', [suppliersIssuesController::class, 'updateSupplierIssue'])->name('suppliers.issues.update');

        Route::prefix('/oms/logistic-containers')->name('oms.logistic_containers.')->group(function () {
            Route::get('/', [LogisticContainerController::class, 'index'])->name('index');
            Route::get('/create', [LogisticContainerController::class, 'create'])->name('create');
            Route::post('/', [LogisticContainerController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [LogisticContainerController::class, 'edit'])->name('edit');
            Route::put('/{id}', [LogisticContainerController::class, 'update'])->name('update');
            Route::delete('/{id}', [LogisticContainerController::class, 'destroy'])->name('destroy');
        });

    });

    Route::prefix('marketing')->name('marketing.tools.')->group(function () {
        Route::get('/tv', [tvController::class, 'index'])->name('tv.index');
        Route::post('/tv/save', [tvController::class, 'store'])->name('tv.store');
        Route::post('/tv/toggle-active/{id}', [tvController::class, 'toggleActive'])->name('tv.toggle_active');
        Route::post('/tv/update-text', [tvController::class, 'changeText'])->name('tv.change_text');

        Route::get('/homepage/asm', [HomepageAdminController::class, 'index'])->name('homepage.asm.index');
        Route::get('/homepage/asd', [HomepageASDAdminController::class, 'index'])->name('homepage.asd.index');
        Route::get('/resources/asm', [asmResourcesController::class, 'index'])->name('resources.asm.index');
        Route::get('/resources/asd', [AsdResourcesController::class, 'index'])->name('resources.asd.index');
        Route::get('/resources/asd/{id_manufacturer}', [AsdResourcesController::class, 'edit'])->name('resources.asd.edit');
        Route::post('/resources/asd/{id_manufacturer}/update', [AsdResourcesController::class, 'update'])->name('resources.asd.update');
        Route::get('/resources/asd/{id_manufacturer}/images', [AsdResourcesController::class, 'images'])->name('resources.asd.images');

        Route::resource('/car-gallery', asgCarsController::class)->names('car_gallery');
    });

    Route::prefix('backoffice')->name('backoffice.tools.')->group(function () {
        Route::resource('/auto-orders', autoOrdersController::class)->except(['update', 'destroy'])->names('auto_orders');
        Route::post('/auto-orders/set-as-ordered', [autoOrdersController::class, 'setAsOrdered'])->name('auto_orders.set_as_ordered');
        Route::post('/auto-orders/get-product-info', [autoOrdersController::class, 'getProductInfo'])->name('auto_orders.get_product_info');
        Route::post('/auto-orders/get-products-info', [autoOrdersController::class, 'getProductsInfo'])->name('auto_orders.get_products_info');
        Route::post('/auto-orders/add', [autoOrdersController::class, 'addToOrder'])->name('auto_orders.add');
        Route::post('/auto-orders/update-order', [autoOrdersController::class, 'updateOrder'])->name('auto_orders.update_order');
        Route::post('/auto-orders/create-order', [autoOrdersController::class, 'saveOrder'])->name('auto_orders.save_order');

        Route::get('/suppliers/map', [suppliersMapController::class, 'index'])->name('suppliers.map.index');
        Route::post('/suppliers/map/store', [suppliersMapController::class, 'store'])->name('suppliers.map.store');
        Route::post('/suppliers/map/modal', [suppliersMapController::class, 'modal'])->name('suppliers.map.modal');

        Route::get('/price-map', [priceMapController::class, 'index'])->name('price_map.index');
        Route::post('/price-map/brand', [priceMapController::class, 'getPriceMapOfBrand'])->name('price_map.brand');
        Route::get('/price-map/cron/{part}', [priceMapController::class, 'cron_priceMap'])->name('price_map.cron');

        Route::get('/suppliers/issues/{type}', [suppliersIssuesController::class, 'index'])->name('suppliers.issues.index');
        Route::post('/suppliers/issues/delivery/new', [suppliersIssuesController::class, 'newDeliveryIssue'])->name('suppliers.issues.delivery.new');
        Route::post('/suppliers/issues/delivery/update', [suppliersIssuesController::class, 'updateDeliveryIssue'])->name('suppliers.issues.delivery.update');
        Route::post('/suppliers/issues/delivery/close', [suppliersIssuesController::class, 'closeDeliveryIssue'])->name('suppliers.issues.delivery.close');
        Route::post('/suppliers/issues/warranty/new', [suppliersIssuesController::class, 'newWarrantyIssue'])->name('suppliers.issues.warranty.new');
        Route::post('/suppliers/issues/warranty/update', [suppliersIssuesController::class, 'updateWarrantyIssue'])->name('suppliers.issues.warranty.update');
        Route::post('/suppliers/issues/warranty/close', [suppliersIssuesController::class, 'closeWarrantyIssue'])->name('suppliers.issues.warranty.close');
        Route::post('/suppliers/issues/new', [suppliersIssuesController::class, 'newSupplierIssue'])->name('suppliers.issues.new');
        Route::post('/suppliers/issues/update', [suppliersIssuesController::class, 'updateSupplierIssue'])->name('suppliers.issues.update');

        Route::resource('/suppliers/backorders', suppliersBackordersController::class)->names('suppliers.backorders');
        Route::post('/suppliers/backorders/get', [suppliersBackordersController::class, 'getSuppliersBackorders'])->name('suppliers.backorders.get');
        Route::get('/suppliers/backorders/send/{id_supplier}/{token}', [suppliersBackordersController::class, 'send_report'])->name('suppliers.backorders.send');
        Route::resource('/suppliersBackorders', suppliersBackordersController::class)->names('suppliersBackorders');
        Route::post('/suppliersBackorders/getSuppliersBackorders', [suppliersBackordersController::class, 'getSuppliersBackorders'])->name('suppliersBackorders.get');
        Route::get('/suppliersBackorders/send/{id_supplier}/{token}', [suppliersBackordersController::class, 'send_report'])->name('suppliersBackorders.send');

        Route::get('/quotes', [quotesController::class, 'index'])->name('quotes.index');
        Route::get('/quotes/data', [quotesController::class, 'data'])->name('quotes.data');
        Route::post('/quotes', [quotesController::class, 'store'])->name('quotes.store');
        Route::put('/quotes/{id}', [quotesController::class, 'update'])->name('quotes.update');
        Route::delete('/quotes/{id}', [quotesController::class, 'destroy'])->name('quotes.destroy');

    });

    Route::prefix('frontoffice')->name('frontoffice.tools.')->group(function () {
        Route::resource('/backorders', backordersController::class)->only(['index'])->names('backorders');
        Route::post('/backorders/order-detail', [backordersController::class, 'getOrderDetails'])->name('backorders.order_detail');
        Route::post('/backorders/update-info', [backordersController::class, 'updateInfo'])->name('backorders.update_info');
        Route::post('/backorders/product-info', [backordersController::class, 'getProductInfo'])->name('backorders.product_info');
        Route::post('/backorders/row-color', [backordersController::class, 'setRowColor'])->name('backorders.row_color');

        Route::get('/product-issues', [productIssuesController::class, 'index'])->name('product_issues.index');
        Route::post('/product-issues/save', [productIssuesController::class, 'store'])->name('product_issues.store');
        Route::get('/product-issues/edit/{id}', [productIssuesController::class, 'edit'])->name('product_issues.edit');
        Route::post('/product-issues/update', [productIssuesController::class, 'update'])->name('product_issues.update');

        Route::get('/quotes', [quotesController::class, 'index'])->name('quotes.index');
        Route::get('/quotes/data', [quotesController::class, 'data'])->name('quotes.data');
        Route::post('/quotes', [quotesController::class, 'store'])->name('quotes.store');
        Route::put('/quotes/{id}', [quotesController::class, 'update'])->name('quotes.update');
        Route::delete('/quotes/{id}', [quotesController::class, 'destroy'])->name('quotes.destroy');

        Route::get('/returns/{id?}', [returnsController::class, 'index'])->name('returns.index');
        Route::get('/returns/modal/{id}', [returnsController::class, 'getModal'])->name('returns.modal');
        Route::post('/returns/change-status', [returnsController::class, 'changeStatus'])->name('returns.change_status');

        Route::get('/warranties/{id?}', [warrantiesController::class, 'index'])->name('warranties.index');
        Route::get('/warranties/modal/{id}', [warrantiesController::class, 'getModal'])->name('warranties.modal');
        Route::post('/warranties/change-status', [warrantiesController::class, 'changeStatus'])->name('warranties.change_status');
    });

    Route::prefix('purchase')->name('purchase.tools.')->group(function () {
        Route::resource('/auto-orders', autoOrdersController::class)->except(['update', 'destroy'])->names('auto_orders');
        Route::resource('/suppliersBackorders', suppliersBackordersController::class)->names('suppliersBackorders');
        Route::post('/suppliersBackorders/getSuppliersBackorders', [suppliersBackordersController::class, 'getSuppliersBackorders'])->name('suppliersBackorders.get');
        Route::get('/suppliersBackorders/send/{id_supplier}/{token}', [suppliersBackordersController::class, 'send_report'])->name('suppliersBackorders.send');

        Route::get('/suppliers/map', [suppliersMapController::class, 'index'])->name('suppliers.map.index');
        Route::post('/suppliers/map/store', [suppliersMapController::class, 'store'])->name('suppliers.map.store');
        Route::post('/suppliers/map/modal', [suppliersMapController::class, 'modal'])->name('suppliers.map.modal');

        Route::get('/price-map', [priceMapController::class, 'index'])->name('price_map.index');
        Route::post('/price-map/brand', [priceMapController::class, 'getPriceMapOfBrand'])->name('price_map.brand');
        Route::get('/price-map/cron/{part}', [priceMapController::class, 'cron_priceMap'])->name('price_map.cron');

        Route::get('/suppliers/issues/{type}', [suppliersIssuesController::class, 'index'])->name('suppliers.issues.index');
        Route::post('/suppliers/issues/delivery/new', [suppliersIssuesController::class, 'newDeliveryIssue'])->name('suppliers.issues.delivery.new');
        Route::post('/suppliers/issues/delivery/update', [suppliersIssuesController::class, 'updateDeliveryIssue'])->name('suppliers.issues.delivery.update');
        Route::post('/suppliers/issues/delivery/close', [suppliersIssuesController::class, 'closeDeliveryIssue'])->name('suppliers.issues.delivery.close');
        Route::post('/suppliers/issues/warranty/new', [suppliersIssuesController::class, 'newWarrantyIssue'])->name('suppliers.issues.warranty.new');
        Route::post('/suppliers/issues/warranty/update', [suppliersIssuesController::class, 'updateWarrantyIssue'])->name('suppliers.issues.warranty.update');
        Route::post('/suppliers/issues/warranty/close', [suppliersIssuesController::class, 'closeWarrantyIssue'])->name('suppliers.issues.warranty.close');
        Route::post('/suppliers/issues/new', [suppliersIssuesController::class, 'newSupplierIssue'])->name('suppliers.issues.new');
        Route::post('/suppliers/issues/update', [suppliersIssuesController::class, 'updateSupplierIssue'])->name('suppliers.issues.update');

        Route::get('/quotes', [quotesController::class, 'index'])->name('quotes.index');
        Route::get('/quotes/data', [quotesController::class, 'data'])->name('quotes.data');
        Route::post('/quotes', [quotesController::class, 'store'])->name('quotes.store');
        Route::put('/quotes/{id}', [quotesController::class, 'update'])->name('quotes.update');
        Route::delete('/quotes/{id}', [quotesController::class, 'destroy'])->name('quotes.destroy');
    });

    Route::prefix('sales')->name('sales.tools.')->group(function () {
        Route::resource('/backorders', backordersController::class)->only(['index'])->names('backorders');
        Route::get('/product-issues', [productIssuesController::class, 'index'])->name('product_issues.index');
        Route::get('/quotes', [quotesController::class, 'index'])->name('quotes.index');
        Route::get('/quotes/data', [quotesController::class, 'data'])->name('quotes.data');
        Route::post('/quotes', [quotesController::class, 'store'])->name('quotes.store');
        Route::put('/quotes/{id}', [quotesController::class, 'update'])->name('quotes.update');
        Route::delete('/quotes/{id}', [quotesController::class, 'destroy'])->name('quotes.destroy');
        Route::get('/returns/{id?}', [returnsController::class, 'index'])->name('returns.index');
        Route::get('/warranties/{id?}', [warrantiesController::class, 'index'])->name('warranties.index');

        Route::prefix('payment-links')->name('payment_links.')->group(function () {
            Route::get('/', [PaymentLinkRequestController::class, 'salesIndex'])->name('index');
            Route::get('/sent', [PaymentLinkRequestController::class, 'salesSent'])->name('sent');
            Route::get('/create', [PaymentLinkRequestController::class, 'create'])->name('create');
            Route::post('/', [PaymentLinkRequestController::class, 'store'])->name('store');
            Route::get('/dashboard/{storeCode}', fn () => redirect()->route('sales.tools.payment_links.index'))->name('dashboard');
            Route::post('/{paymentLinkRequest}/send-email', [PaymentLinkRequestController::class, 'sendEmail'])->name('send_email');
        });
    });

    Route::prefix('documentManager')->name('documentsManager.clean.')->group(function () {
        Route::get('/', [documentsManagerController::class, 'index'])->name('index');
        Route::get('/add', [documentsManagerController::class, 'addDocument'])->name('addDocument');
        Route::get('/{category}/{element}', [documentsManagerController::class, 'listDocuments'])->name('listDocuments');
        Route::post('/save', [documentsManagerController::class, 'store'])->name('store');
        Route::post('/search', [documentsManagerController::class, 'search'])->name('search');
        Route::post('/loadFile', [documentsManagerController::class, 'loadFile'])->name('loadFile');
        Route::post('/listSearch', [documentsManagerController::class, 'listSearch'])->name('listSearch');
        Route::post('/destroy', [documentsManagerController::class, 'destroy'])->name('destroy');
    });
});

Route::middleware(['web', 'auth'])
    ->prefix('customTools/erp/oms')
    ->name('erp.oms.')
    ->group(function () {
        Route::get('/', [OmsDashboardController::class, 'index'])->name('dashboard');
        Route::get('/fragments/documents', [OmsDashboardController::class, 'documentsFragment'])->name('dashboard.fragments.documents');
        Route::get('/fragments/summary', [OmsDashboardController::class, 'summaryFragment'])->name('dashboard.fragments.summary');
        Route::get('/fragments/stats', [OmsDashboardController::class, 'statsFragment'])->name('dashboard.fragments.stats');
        Route::get('/dashboard/export/csv', [OmsDashboardController::class, 'exportCsv'])->name('dashboard.export.csv');

        Route::get('/order-notes', [OrderNoteController::class, 'index'])->name('order_notes.index');
        Route::get('/order-notes/create', [OrderNoteController::class, 'create'])->name('order_notes.create');
        Route::post('/order-notes/create-from-supplier/{supplierId}', [OrderNoteController::class, 'createFromSupplier'])->name('order_notes.create_from_supplier');
        Route::post('/order-notes', [OrderNoteController::class, 'store'])->name('order_notes.store');
        Route::get('/order-notes/{orderNote}', [OrderNoteController::class, 'show'])->name('order_notes.show');
        Route::get('/order-notes/{orderNote}/edit', [OrderNoteController::class, 'edit'])->name('order_notes.edit');
        Route::put('/order-notes/{orderNote}', [OrderNoteController::class, 'update'])->name('order_notes.update');
        Route::delete('/order-notes/{orderNote}', [OrderNoteController::class, 'destroy'])->name('order_notes.destroy');
        Route::post('/order-notes/{orderNote}/lines', [OrderNoteController::class, 'addLine'])->name('order_notes.lines.store');
        Route::patch('/order-notes/{orderNote}/lines/{line}', [OrderNoteController::class, 'updateLine'])->name('order_notes.lines.update');
        Route::delete('/order-notes/{orderNote}/lines/{line}', [OrderNoteController::class, 'destroyLine'])->name('order_notes.lines.destroy');
        Route::get('/order-notes/{orderNote}/supplier-products', [OrderNoteController::class, 'supplierProducts'])->name('order_notes.supplier_products');
        Route::post('/order-notes/{orderNote}/import/csv/preview', [OrderNoteController::class, 'importCsvPreview'])->name('order_notes.import.preview');
        Route::get('/order-notes/{orderNote}/import/csv/verify', [OrderNoteController::class, 'importCsvVerify'])->name('order_notes.import.verify');
        Route::post('/order-notes/{orderNote}/import/csv/confirm', [OrderNoteController::class, 'importCsvConfirm'])->name('order_notes.import.confirm');
        Route::post('/order-notes/{orderNote}/notes', [OrderNoteController::class, 'saveNotes'])->name('order_notes.notes.save');
        Route::post('/order-notes/{orderNote}/lines/{line}/notes', [OrderNoteController::class, 'saveLineNotes'])->name('order_notes.lines.notes.save');
        Route::get('/order-notes/{orderNote}/export/csv', [OrderNoteController::class, 'exportCsv'])->name('order_notes.export.csv');
        Route::get('/order-notes/{orderNote}/export/pdf', [OrderNoteController::class, 'exportPdf'])->name('order_notes.export.pdf');
        Route::get('/order-notes/{orderNote}/invoice', [SupplierInvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/order-notes/{orderNote}/invoice', [SupplierInvoiceController::class, 'store'])->name('invoices.store');
        Route::post('/order-notes/{orderNote}/bill', [BillingController::class, 'store'])->name('billing.store');

        Route::get('/billed-orders', [BilledOrderController::class, 'index'])->name('billed_orders.index');
        Route::get('/billed-orders/{billedOrder}', [BilledOrderController::class, 'show'])->name('billed_orders.show');
        Route::post('/billed-orders/{billedOrder}/notes', [BilledOrderController::class, 'saveNotes'])->name('billed_orders.notes.save');
        Route::post('/billed-orders/{billedOrder}/lines/{line}/notes', [BilledOrderController::class, 'saveLineNotes'])->name('billed_orders.lines.notes.save');
        Route::post('/billed-orders/{billedOrder}/shipment', [BilledOrderController::class, 'saveShipmentRelation'])->name('billed_orders.shipment.save');
        Route::get('/billed-orders/{billedOrder}/export/csv', [BilledOrderController::class, 'exportCsv'])->name('billed_orders.export.csv');
        Route::get('/billed-orders/{billedOrder}/export/pdf', [BilledOrderController::class, 'exportPdf'])->name('billed_orders.export.pdf');

        Route::get('/receptions', [ReceptionController::class, 'index'])->name('receptions.index');
        Route::post('/billed-orders/{billedOrder}/receive', [ReceptionController::class, 'store'])->name('receptions.store');
        Route::get('/billed-orders/{billedOrder}/receptions', [ReceptionController::class, 'history'])->name('receptions.history');
        Route::get('/invoices/{invoice}/receptions', [ReceptionController::class, 'invoiceHistory'])->name('receptions.invoice_history');
        Route::get('/receptions/export/csv', [ReceptionController::class, 'exportCsv'])->name('receptions.export.csv');

        Route::get('/invoices', [SupplierInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [SupplierInvoiceController::class, 'show'])->name('invoices.show');
        Route::post('/invoices/{invoice}/shipment', [SupplierInvoiceController::class, 'saveShipmentRelation'])->name('invoices.shipment.save');
        Route::post('/invoices/{invoice}/close', [SupplierInvoiceController::class, 'close'])->name('invoices.close');
        Route::post('/invoices/{invoice}/cancel', [SupplierInvoiceController::class, 'cancel'])->name('invoices.cancel');
        Route::get('/invoices/{invoice}/export/csv', [SupplierInvoiceController::class, 'exportCsv'])->name('invoices.export.csv');
        Route::get('/invoices/{invoice}/export/pdf', [SupplierInvoiceController::class, 'exportPdf'])->name('invoices.export.pdf');

        Route::get('/supplier-terms/{supplierId}', [SupplierTermLevelController::class, 'index'])->name('supplier_terms.index');
        Route::post('/supplier-terms/{supplierId}', [SupplierTermLevelController::class, 'store'])->name('supplier_terms.store');
        Route::put('/supplier-terms/{level}', [SupplierTermLevelController::class, 'update'])->name('supplier_terms.update');
        Route::delete('/supplier-terms/{level}', [SupplierTermLevelController::class, 'destroy'])->name('supplier_terms.destroy');

        Route::prefix('logistic-containers')->name('logistic_containers.')->group(function () {
            Route::get('/', [LogisticContainerController::class, 'index'])->name('index');
            Route::get('/create', [LogisticContainerController::class, 'create'])->name('create');
            Route::post('/', [LogisticContainerController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [LogisticContainerController::class, 'edit'])->name('edit');
            Route::put('/{id}', [LogisticContainerController::class, 'update'])->name('update');
            Route::delete('/{id}', [LogisticContainerController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('history')->name('history.')->group(function () {
            Route::get('/prices', [HistoryController::class, 'prices'])->name('prices');
            Route::get('/stock', [HistoryController::class, 'stock'])->name('stock');
            Route::get('/invoice/{billedOrderId}/prices', [HistoryController::class, 'pricesByInvoice'])->name('invoice.prices');
            Route::get('/reception/{receptionId}/stock', [HistoryController::class, 'stockByReception'])->name('reception.stock');
        });
    });

Route::middleware(['web', 'auth'])->prefix('data/asd/resources')->name('data.resources.')->group(function () {
    Route::get('/', [AsdResourcesController::class, 'index'])->name('index');
    Route::get('/{id_manufacturer}', [AsdResourcesController::class, 'edit'])->name('edit');
    Route::post('/{id_manufacturer}/update', [AsdResourcesController::class, 'update'])->name('update');
    Route::get('/{id_manufacturer}/images', [AsdResourcesController::class, 'images'])->name('images');
});

Route::get('/api/asd/resources', [AsdResourcesController::class, 'api'])->name('api.asd.resources');
Route::get('/api/asm/resources', [asmResourcesController::class, 'api'])->name('api.asm.resources');
Route::get('/api/gallery/cars', [asgCarsController::class, 'api'])->name('api.gallery.cars');
Route::get('/api/gallery/cars/{lang}', [asgCarsController::class, 'apiList'])->where('lang', 'en|es|fr|pt|it')->name('api.gallery.cars.lang');
Route::get('/api/gallery/cars/{lang}/{id}', [asgCarsController::class, 'apiShow'])->where('lang', 'en|es|fr|pt|it')->whereNumber('id')->name('api.gallery.cars.show');

Route::middleware(['web', 'auth'])->prefix('marketing/asm/resources')->name('marketing.resources.')->group(function () {
    Route::get('/', [asmResourcesController::class, 'index'])->name('index');
    Route::post('/{id_manufacturer}/{lang}/upload', [asmResourcesController::class, 'upload'])->name('upload');
});

Route::prefix('customTools/asg-cars')
    ->name('asg_cars.')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/', [asgCarsController::class, 'index'])->name('index');
        Route::get('/create', [asgCarsController::class, 'create'])->name('create');
        Route::post('/', [asgCarsController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [asgCarsController::class, 'edit'])->name('edit');
        Route::put('/{id}', [asgCarsController::class, 'update'])->name('update');
        Route::delete('/{id}', [asgCarsController::class, 'destroy'])->name('destroy');
    });



Route::post( 'customTools/stats/daily_stats',       [statsController::class, 'daily_stats'])->name('stats.daily_stats');
Route::get(  'customTools/stats/kpi',               [statsController::class, 'kpi'])->name('stats.kpi');


Route::get(  'customTools/dashboard/cron',              [dashboardController::class, 'cron_update'])->name('dashboard.cron_update');

Route::get( 'barcode/product/generate/{id_product}/{id_product_attribute}', [barcodeController::class, 'generateProductBarcode'])->name('barcode.generateProductBarcode');
Route::get( 'barcode/product/print/{id_product}/{id_product_attribute}/{repeat}', [barcodeController::class, 'printProductBarcode'])->name('barcode.printProductBarcode');

Route::middleware(['auth'])->group(function () {

    Route::get('/currency-rates',  [CurrencyVariationController::class, 'index'])->name('currency_variation.index');
    Route::post('/currency-rates', [CurrencyVariationController::class, 'store'])->name('currency_variation.store');

    Route::prefix('marketing/customTools/homepage/ASM')->name('marketing.homepage.')->group(function () {
        Route::get('/',                     [HomepageAdminController::class, 'index'])->name('index');
        Route::get('/edit/{id}',            [HomepageAdminController::class, 'edit'])->name('edit');
        Route::post('/update/{id}',         [HomepageAdminController::class, 'update'])->name('update');
        Route::get('/preview',              [HomepageAdminController::class, 'preview'])->name('preview');
        Route::post('/publish',             [HomepageAdminController::class, 'publish'])->name('publish');
        Route::get('/history',              [HomepageAdminController::class, 'history'])->name('history');
        Route::get('/restore/{id}',         [HomepageAdminController::class, 'restore'])->name('restore');
        Route::get('/homepage/slot/{id}',   [HomepageAdminController::class, 'getSlot']);
        Route::post('/homepage/slot/save',  [HomepageAdminController::class, 'saveSlot']);

    });

    Route::prefix('marketing/customTools/homepage/ASD')->name('marketing.homepage_ASD.')->group(function () {
        Route::get('/', [HomepageASDAdminController::class, 'index'])->name('index');
        Route::post('/update', [HomepageASDAdminController::class, 'update'])->name('update');
    });
    
    Route::prefix('customTools/shipments-check')->name('customTools.')->group(function () {
        Route::get('', [CarrierExpeditionCheckController::class, 'index'])->name('shipments.index');
        Route::post('/store', [CarrierExpeditionCheckController::class, 'store'])->name('shipments.store');
        Route::get('/history', [CarrierExpeditionCheckController::class, 'history'])->name('shipments.history');
        Route::get('/export', [CarrierExpeditionCheckController::class, 'exportCsv'])->name('shipments.export');
    });

    Route::prefix('customTools/safety-check')->name('customTools.')->group(function () {
        Route::get('', [SafetyCheckController::class, 'index'])->name('safety.index');
        Route::post('/store', [SafetyCheckController::class, 'store'])->name('safety.store');
        Route::get('/history', [SafetyCheckController::class, 'history'])->name('safety.history');
        Route::get('/export', [SafetyCheckController::class, 'exportCsv'])->name('safety.export');
    });

    Route::prefix('customTools/change-tracker')->name('customTools.changesTracker.')->group(function () {
        Route::get('/', [ChangeTrackerController::class, 'index'])->name('index');
        Route::get('/create', [ChangeTrackerController::class, 'create'])->name('create');
        Route::post('/store', [ChangeTrackerController::class, 'store'])->name('store');
        Route::get('/{id}', [ChangeTrackerController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ChangeTrackerController::class, 'edit'])->name('edit');
        Route::post('/{id}/update', [ChangeTrackerController::class, 'update'])->name('update');

        Route::get('/{projectId}/files/{fileId}/download', [ChangeTrackerController::class, 'downloadFile'])->name('files.download');
        Route::post('/{projectId}/errors/store', [ChangeTrackerController::class, 'storeError'])->name('errors.store');
        Route::post('/{projectId}/errors/{errorId}/update', [ChangeTrackerController::class, 'updateError'])->name('errors.update');

        Route::post('/{projectId}/files/{fileId}/delete', [ChangeTrackerController::class, 'deleteFile'])->name('files.delete');
    });

    Route::get('customTools/site-text-side-by-side', [SiteTextSideBySideController::class, 'index'])->name('site-text-side-by-side.index');
    Route::post('customTools/site-text-side-by-side', [SiteTextSideBySideController::class, 'compare'])->name('site-text-side-by-side.compare');

    Route::get('customTools/site-seo-compare', [SiteSeoCompareController::class, 'index'])->name('site-seo-compare.index');
    Route::post('customTools/site-seo-compare', [SiteSeoCompareController::class, 'compare'])->name('site-seo-compare.compare');

    Route::get('/tasks',                                    [AsgTasksController::class, 'index'])->name('asg_tasks.index');
    Route::post('/tasks',                                   [AsgTasksController::class, 'store'])->name('asg_tasks.store');
    Route::patch('/tasks/{task}/inline',                    [AsgTasksController::class, 'inlineUpdate'])->name('asg_tasks.inline');

    Route::get( 'customTools/base/price',                   [basePriceController::class, 'index'])->name('basePrice.index');
    Route::post('customTools/base/price/save',              [basePriceController::class, 'store'])->name('basePrice.store');
    Route::post('customTools/base/price/execute',           [basePriceController::class, 'execute'])->name('basePrice.execute');
    Route::post('customTools/base/price/pricing-data',      [basePriceController::class, 'pricingData'])->name('basePrice.pricingData');
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

    Route::get('customTools/shipping/packingList', [shippingController::class, 'packingList'])->name('shipping.packingList');
    Route::post('customTools/shipping/packingList/export-xls', [shippingController::class, 'exportPackingListXls'])->name('shipping.packingList.exportXls');

    Route::get( 'customTools/vat/check', fn () => redirect()->route('finance.tools.vat.check'))->name('checkVat.index');
    Route::get( 'customTools/vat/verify',[checkVatController::class, 'verify'])->name('checkVat.verify');
    
    Route::get( 'customTools/compats/index',[compatsController::class, 'index'])->name('compats.index');
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
    Route::post( 'customTools/picking/container', [pickingController::class, 'saveContainer'])->name('picking.container.save');
    Route::post( 'customTools/picking/getEAN', [pickingController::class, 'getEAN'])->name('picking.confirmEAN');

Route::get('customTools/housing/index', [housingController::class, 'index'])->name('housing.index');
Route::post('customTools/housing/requestData', [housingController::class, 'requestData'])->name('housing.requestData');
Route::post('customTools/housing/saveData', [housingController::class, 'saveData'])->name('housing.saveData'); // legacy compatibility
Route::post('customTools/housing/editLocation', [housingController::class, 'editLocation'])->name('housing.editLocation');
Route::post('customTools/housing/editMeasures', [housingController::class, 'editMeasures'])->name('housing.editMeasures');
Route::post('customTools/housing/editReference', [housingController::class, 'editReference'])->name('housing.editReference');
Route::post('customTools/housing/editEan13', [housingController::class, 'editEan13'])->name('housing.editEan13');
Route::post('customTools/housing/editStock', [housingController::class, 'editStock'])->name('housing.editStock');

Route::post('customTools/housing/editStockArrive', [housingController::class, 'editStockArrive'])->name('housing.editStockArrive');
Route::post('customTools/housing/bulkLookupProduct', [housingController::class, 'bulkLookupProduct'])->name('housing.bulkLookupProduct');
Route::post('customTools/housing/bulkSaveHousing', [housingController::class, 'bulkSaveHousing'])->name('housing.bulkSaveHousing');

/**
    Route::get('customTools/housing/index', [housingController::class, 'index'])->name('housing.index');
    Route::post('customTools/housing/requestData', [housingController::class, 'requestData'])->name('housing.requestData');
    Route::post('customTools/housing/saveData', [housingController::class, 'saveData'])->name('housing.saveData'); // legacy compatibility
    Route::post('customTools/housing/editLocation', [housingController::class, 'editLocation'])->name('housing.editLocation');
    Route::post('customTools/housing/editMeasures', [housingController::class, 'editMeasures'])->name('housing.editMeasures');
    Route::post('customTools/housing/editReference', [housingController::class, 'editReference'])->name('housing.editReference');
    Route::post('customTools/housing/editEan13', [housingController::class, 'editEan13'])->name('housing.editEan13');
    Route::post('customTools/housing/editStock', [housingController::class, 'editStock'])->name('housing.editStock');
    
    Route::post('customTools/housing/editStockArrive', [housingController::class, 'editStockArrive'])->name('housing.editStockArrive');
**/

    Route::get( 'documentManager', [documentsManagerController::class, 'index'])->name('documentsManager.index');
    Route::get( 'documentManager/add', [documentsManagerController::class, 'addDocument'])->name('documentsManager.addDocument');
    Route::get( 'documentManager/{category}/{element}', [documentsManagerController::class, 'listDocuments'])->name('documentsManager.listDocuments');
    Route::post( 'documentManager/save', [documentsManagerController::class, 'store'])->name('documentsManager.store');
    Route::post( 'documentManager/search', [documentsManagerController::class, 'search'])->name('documentsManager.search');
    Route::post( 'documentManager/loadFile', [documentsManagerController::class, 'loadFile'])->name('documentsManager.loadFile');
    Route::post( 'documentManager/listSearch', [documentsManagerController::class, 'listSearch'])->name('documentsManager.listSearch');
    Route::post( 'documentManager/destroy', [documentsManagerController::class, 'destroy'])->name('documentsManager.destroy');

    Route::get( 'customTools/documentManager', [documentsManagerController::class, 'index'])->name('documentsManager.legacy.index');
    Route::get( 'customTools/documentManager/add', [documentsManagerController::class, 'addDocument'])->name('documentsManager.legacy.addDocument');
    Route::get( 'customTools/documentManager/{category}/{element}', [documentsManagerController::class, 'listDocuments'])->name('documentsManager.legacy.listDocuments');
    Route::post( 'customTools/documentManager/save', [documentsManagerController::class, 'store'])->name('documentsManager.legacy.store');
    Route::post( 'customTools/documentManager/search', [documentsManagerController::class, 'search'])->name('documentsManager.legacy.search');
    Route::post( 'customTools/documentManager/loadFile', [documentsManagerController::class, 'loadFile'])->name('documentsManager.legacy.loadFile');
    Route::post( 'customTools/documentManager/listSearch', [documentsManagerController::class, 'listSearch'])->name('documentsManager.legacy.listSearch');
    Route::post( 'customTools/documentManager/destroy', [documentsManagerController::class, 'destroy'])->name('documentsManager.legacy.destroy');
    
    
    /** DPD **/
    Route::get( 'dpd/csv/generate/{id_order}/{weight}', [dpdController::class, 'generateCSV'])->name('dpd.generateCSV');
    /** DPD **/
    
    /** BARCODE **/
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
    
    Route::resource('customTools/stockEntry', stockEntryController::class)->only(['show', 'update', 'destroy']);
    Route::resource('customTools/autoOrders', autoOrdersController::class)->except(['update', 'destroy']);
    Route::resource('customTools/backorders', backordersController::class)->only(['index']);

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
        Route::post('/store', [taskController::class,'store'])->name('store');
        Route::post('/{id}/update', [taskController::class,'update'])->name('update');
    
        // ✅ novo: excel inline update
        Route::post('/{id}/field', [taskController::class,'updateField'])->name('field');
    
        // ✅ novo: comentários/histórico para expand inline
        Route::get('/{id}/comments', [taskController::class,'comments'])->name('comments');

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
Route::get(  'checklist/history/{department}',     [employeeChecklistController::class, 'history'])->name('checklist.history');
Route::get(  'checklist/today',                    [employeeChecklistController::class, 'today'])->name('checklist.today');
Route::patch('checklist/{task}/status',            [employeeChecklistController::class, 'updateStatus'])->name('checklist.updateStatus');
Route::patch('checklist/note',                     [employeeChecklistController::class, 'updateNote'])->name('checklist.updateNote');

Route::get(   'checklist',                         [checklistManagerController::class, 'index'])->name('checklist.index');
Route::get(   'checklist/create',                  [checklistManagerController::class, 'create'])->name('checklist.create');
Route::post(  'checklist/',                        [checklistManagerController::class, 'store'])->name('checklist.store');
Route::get(   'checklist/{id}/{template}/edit',    [checklistManagerController::class, 'edit'])->name('checklist.edit');
Route::put(   'checklist/{template}',              [checklistManagerController::class, 'update'])->name('checklist.update');
Route::delete('checklist/{template}',              [checklistManagerController::class, 'destroy'])->name('checklist.destroy');

Route::get(  'customTools/checklist/history/{department}',     [employeeChecklistController::class, 'history'])->name('checklist.legacy.history');
Route::get(  'customTools/checklist/today',                    [employeeChecklistController::class, 'today'])->name('checklist.legacy.today');
Route::patch('customTools/checklist/{task}/status',            [employeeChecklistController::class, 'updateStatus'])->name('checklist.legacy.updateStatus');
Route::patch('customTools/checklist/note',                     [employeeChecklistController::class, 'updateNote'])->name('checklist.legacy.updateNote');

Route::get(   'customTools/checklist',                         [checklistManagerController::class, 'index'])->name('checklist.legacy.index');
Route::get(   'customTools/checklist/create',                  [checklistManagerController::class, 'create'])->name('checklist.legacy.create');
Route::post(  'customTools/checklist/',                        [checklistManagerController::class, 'store'])->name('checklist.legacy.store');
Route::get(   'customTools/checklist/{id}/{template}/edit',    [checklistManagerController::class, 'edit'])->name('checklist.legacy.edit');
Route::put(   'customTools/checklist/{template}',              [checklistManagerController::class, 'update'])->name('checklist.legacy.update');
Route::delete('customTools/checklist/{template}',              [checklistManagerController::class, 'destroy'])->name('checklist.legacy.destroy');

/** Checklist admin assignment**/
Route::get('checklist/assignEmployees', [checklistManagerController::class, 'assignEmployees'])->name('checklist.assignEmployees');
Route::post('checklist/assignEmployees', [checklistManagerController::class, 'updateEmployeeAdmins'])->name('checklist.updateEmployeeAdmins');
Route::get('customTools/checklist/assignEmployees', [checklistManagerController::class, 'assignEmployees'])->name('checklist.legacy.assignEmployees');
Route::post('customTools/checklist/assignEmployees', [checklistManagerController::class, 'updateEmployeeAdmins'])->name('checklist.legacy.updateEmployeeAdmins');

Route::get('/checklist-carry-over', function() {
    // link para passar tasks pending para o dia de hoje.


    $today = Carbon::today();
    $departmentIds = daily_checklist::query()
        ->where('main_task', true)
        ->pluck('department_id')
        ->unique()
        ->values()
        ->all();

    $carriedCount = daily_checklist::ensureTasksForDate($departmentIds, $today);

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
