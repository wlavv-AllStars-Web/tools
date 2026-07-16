<?php

/** Main routes **/
use App\Http\Controllers\Areas\dashboardController;
use App\Http\Controllers\Areas\adminController;
use App\Http\Controllers\Areas\webController;
use App\Http\Controllers\Areas\hrController;
use App\Http\Controllers\Areas\financeController;
use App\Http\Controllers\Areas\logisticsController;
use App\Http\Controllers\Areas\marketingController;
use App\Http\Controllers\Areas\MarketingProductImageReviewController;
use App\Http\Controllers\Areas\customerSupportController;
use App\Http\Controllers\Areas\salesController;
use App\Http\Controllers\Areas\purchaseController;
use App\Http\Controllers\Areas\WebProductExportController;
use App\Http\Controllers\Areas\WebProductStoreCompareController;

use App\Http\Controllers\Areas\dataController;
use App\Http\Controllers\CustomTools\DataAsdShippingController;

/** AREAS **/
Route::get('home', [dashboardController::class, 'index'])->name('home.index');
Route::get('dashboard', [dashboardController::class, 'index'])->name('dashboard.index');

Route::post( 'customTools/dashboard/post',              [dashboardController::class, 'post'])->name('dashboard.post');
Route::post( 'customTools/dashboard/shipping_report',   [dashboardController::class, 'shipping_report'])->name('dashboard.shipping_report');

Route::get('administration', [adminController::class, 'index'])->name('administration.index');
Route::get('web', [webController::class, 'index'])->name('web.index');
Route::post('web/newsletter/send-pending', [webController::class, 'sendPendingNewsletterEmails'])->name('web.newsletter.send_pending');
Route::get('web/product-export', [WebProductExportController::class, 'index'])->name('web.product_export.index');
Route::post('web/product-export/generate', [WebProductExportController::class, 'generate'])->name('web.product_export.generate');
Route::get('web/product-export/download/{filename}', [WebProductExportController::class, 'download'])->name('web.product_export.download');
Route::get('web/product-store-compare', [WebProductStoreCompareController::class, 'index'])->name('web.product_store_compare.index');
Route::post('web/product-store-compare/catalog', [WebProductStoreCompareController::class, 'uploadCatalog'])->name('web.product_store_compare.catalog');
Route::get('web/product-store-compare/csv', [WebProductStoreCompareController::class, 'csv'])->name('web.product_store_compare.csv');
Route::get('web/product-store-compare/pdf', [WebProductStoreCompareController::class, 'pdf'])->name('web.product_store_compare.pdf');

Route::resources([ 'hr'             => hrController::class      ]);
Route::get('finance', [financeController::class, 'index'])->name('finance.index');

Route::get('data', [dataController::class, 'index'])->name('data.index');
Route::post('data/asd-images/sync', [dataController::class, 'syncAsdImages'])->name('data.asd_images.sync');
Route::get('data/asd-shipping', [DataAsdShippingController::class, 'index'])->name('data.asd_shipping.index');
Route::post('data/asd-shipping', [DataAsdShippingController::class, 'update'])->name('data.asd_shipping.update');


Route::get(  'finance/documents/inventory', [financeController::class, 'download_inventory'])->name('finance.download_inventory');
Route::get(  'finance/documents/intrastat', [financeController::class, 'download_intrastat'])->name('finance.download_intrastat');
Route::post( 'finance/documents/intrastat/importacao', [financeController::class, 'intrastat_import'])->name('finance.download_intrastat_import');
Route::post( 'finance/documents/intrastat/exportacao', [financeController::class, 'intrastat_export'])->name('finance.download_intrastat_export');
Route::post( 'finance/documents/intrastat/saveCurrencyRate', [financeController::class, 'save_currency_rate'])->name('finance.save_currency_rate');

Route::get('logistics', [logisticsController::class, 'index'])->name('logistics.index');
Route::get('marketing', [marketingController::class, 'index'])->name('marketing.index');
Route::get('marketing/product-images', [MarketingProductImageReviewController::class, 'index'])->name('marketing.product_images.index');
Route::get('marketing/product-images/products', [MarketingProductImageReviewController::class, 'products'])->name('marketing.product_images.products');
Route::post( 'customTools/marketing/post', [marketingController::class, 'post'])->name('marketing.post');
Route::post('marketing/youtube-broken-links/sync', [marketingController::class, 'syncYoutubeBrokenLinks'])->name('marketing.youtube_broken_links.sync');
Route::get( 'marketing/ASD/missingImages',[marketingController::class, 'getASDMissingImages'])->name('marketing.asdMissingImages');

Route::get('customer', [customerSupportController::class, 'index'])->name('customer.index');

Route::get('sales', [salesController::class, 'index'])->name('sales.index');
Route::get('purchase', [purchaseController::class, 'index'])->name('purchase.index');

/** Controllers **/
Route::post( 'orders/sendReviewedEmail', [salesController::class, 'sendReviewedEmail'])->name('orders.sendReviewedEmail');
