<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$ps = DB::connection('mysql2');
$ps->disableQueryLog();
$prefix = (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');
$outputDirectory = public_path('admin/download');
if (! is_dir($outputDirectory) && ! mkdir($outputDirectory, 0775, true) && ! is_dir($outputDirectory)) throw new RuntimeException('Unable to create backup directory.');
$path = $outputDirectory.'/oms_catalogue_audit_backup_'.now()->format('Ymd_His').'.csv';
$handle = fopen($path, 'wb');
if ($handle === false) throw new RuntimeException('Unable to create backup file.');

$totals = DB::table('oms_order_note_lines')->selectRaw('product_id, COALESCE(product_attribute_id, 0) as product_attribute_id, SUM(COALESCE(qty_ordered, 0)) as qty_ordered, SUM(COALESCE(qty_billed_total, 0)) as qty_invoiced, SUM(COALESCE(qty_received_total, 0)) as qty_received')->groupBy('product_id', 'product_attribute_id')->get()->keyBy(fn ($row) => (int) $row->product_id.':'.(int) $row->product_attribute_id);

fwrite($handle, "\xEF\xBB\xBF");
fputcsv($handle, ['record_type','id_product','id_product_attribute','id_shop','id_shop_group','reference','parent_reference','ean13','stock_available','stock_arrive','purchase_price_eur','sale_price_eur_final','purchase_price_supplier','sale_price_supplier_final','parent_purchase_supplier','parent_sale_supplier','purchase_price_supplier_impact','sale_price_supplier_impact','sale_price_eur_impact','oms_qty_ordered','oms_qty_invoiced','oms_qty_received'], ';');
$write = static function (object $row, string $type) use ($handle, $totals): void {
    $attributeId = (int) ($row->id_product_attribute ?? 0);
    $qty = $totals->get((int) $row->id_product.':'.$attributeId);
    fputcsv($handle, [$type,(int)$row->id_product,$attributeId,$row->id_shop,$row->id_shop_group,(string)$row->reference,(string)$row->parent_reference,(string)$row->ean13,$row->stock_available,$row->stock_arrive,$row->purchase_price_eur,$row->sale_price_eur_final,$row->purchase_price_supplier,$row->sale_price_supplier_final,$row->parent_purchase_supplier,$row->parent_sale_supplier,$row->purchase_price_supplier_impact,$row->sale_price_supplier_impact,$row->sale_price_eur_impact,$qty?->qty_ordered ?? 0,$qty?->qty_invoiced ?? 0,$qty?->qty_received ?? 0], ';');
};

$products = $ps->table($prefix.'product as p')->leftJoin($prefix.'stock_available as sa', function ($join) {$join->on('sa.id_product','=','p.id_product')->where('sa.id_product_attribute',0);})->leftJoin($prefix.'custom_product as cp','cp.id_product','=','p.id_product')->selectRaw('p.id_product, 0 as id_product_attribute, sa.id_shop, sa.id_shop_group, p.reference, "" as parent_reference, p.ean13, COALESCE(sa.quantity,0) as stock_available, COALESCE(cp.stock_arrive,0) as stock_arrive, p.wholesale_price as purchase_price_eur, p.price as sale_price_eur_final, cp.wholesale_price_base_currency as purchase_price_supplier, cp.price_base_currency as sale_price_supplier_final, cp.wholesale_price_base_currency as parent_purchase_supplier, cp.price_base_currency as parent_sale_supplier, 0 as purchase_price_supplier_impact, 0 as sale_price_supplier_impact, 0 as sale_price_eur_impact')->orderBy('p.id_product')->orderBy('sa.id_shop');
foreach ($products->cursor() as $row) $write($row, 'product');

$attributes = $ps->table($prefix.'product_attribute as pa')->join($prefix.'product as p','p.id_product','=','pa.id_product')->leftJoin($prefix.'stock_available as sa', function ($join) {$join->on('sa.id_product','=','pa.id_product')->on('sa.id_product_attribute','=','pa.id_product_attribute');})->leftJoin($prefix.'custom_product as cp','cp.id_product','=','p.id_product')->leftJoin($prefix.'custom_product_attribute as cpa','cpa.id_product_attribute','=','pa.id_product_attribute')->selectRaw('pa.id_product, pa.id_product_attribute, sa.id_shop, sa.id_shop_group, pa.reference, p.reference as parent_reference, pa.ean13, COALESCE(sa.quantity,0) as stock_available, COALESCE(cpa.stock_arrive,0) as stock_arrive, pa.wholesale_price as purchase_price_eur, (p.price + pa.price) as sale_price_eur_final, cpa.wholesale_price_base_currency as purchase_price_supplier, (COALESCE(cp.price_base_currency,0) + COALESCE(cpa.price_base_currency,0)) as sale_price_supplier_final, cp.wholesale_price_base_currency as parent_purchase_supplier, cp.price_base_currency as parent_sale_supplier, cpa.wholesale_price_base_currency as purchase_price_supplier_impact, cpa.price_base_currency as sale_price_supplier_impact, pa.price as sale_price_eur_impact')->orderBy('pa.id_product_attribute')->orderBy('sa.id_shop');
foreach ($attributes->cursor() as $row) $write($row, 'combination');

fclose($handle);
echo $path.PHP_EOL;