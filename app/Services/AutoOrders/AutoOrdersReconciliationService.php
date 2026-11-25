<?php

namespace App\Services\AutoOrders;

use Illuminate\Support\Facades\DB;

/** Builds a read-only, current-state reconciliation using the shared stock row. */
final class AutoOrdersReconciliationService
{
    private string $prefix;

    public function __construct()
    {
        $this->prefix = env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
    }

    public function manufacturers()
    {
        return DB::connection('mysql2')->table($this->prefix . 'manufacturer as m')
            ->join($this->prefix . 'product as p', 'p.id_manufacturer', '=', 'm.id_manufacturer')
            ->where('p.id_supplier', '>', 0)
            ->select('m.id_manufacturer', 'm.name')
            ->distinct()->orderBy('m.name')->get();
    }

    public function forManufacturer(int $manufacturerId): array
    {
        $ps = DB::connection('mysql2');
        $p = $this->prefix;
        $attributeRows = $ps->table($p . 'product as product')->join($p . 'product_attribute as pa', 'pa.id_product', '=', 'product.id_product')
            ->where('product.id_manufacturer', $manufacturerId)
            ->selectRaw('product.id_product, pa.id_product_attribute, product.id_supplier, COALESCE(NULLIF(pa.reference, ""), product.reference) reference')->get();
        $simpleRows = $ps->table($p . 'product as product')->where('product.id_manufacturer', $manufacturerId)
            ->whereNotExists(function ($q) use ($p) { $q->selectRaw('1')->from($p . 'product_attribute as pa')->whereColumn('pa.id_product', 'product.id_product'); })
            ->selectRaw('product.id_product, 0 id_product_attribute, product.id_supplier, product.reference')->get();
        $skus = $attributeRows->merge($simpleRows)->filter(fn ($r) => $r->reference !== '')->values();
        if ($skus->isEmpty()) return [];

        $productIds = $skus->pluck('id_product')->unique()->all();
        $key = static fn ($product, $attribute) => (int) $product . ':' . (int) $attribute;
        $wanted = $skus->mapWithKeys(fn ($r) => [$key($r->id_product, $r->id_product_attribute) => $r]);
        $snapshot = $ps->table($p . 'custom_sold')->whereIn('id_product', $productIds)
            ->where('month', '>=', now()->subMonths(11)->startOfMonth()->toDateString())
            ->selectRaw('id_product,id_product_attribute,SUM(quantity_sold) qty')->groupBy('id_product','id_product_attribute')->get()->keyBy(fn($r) => $key($r->id_product,$r->id_product_attribute));
        $recent = $ps->table($p . 'order_detail as od')->join($p . 'orders as o','o.id_order','=','od.id_order')->whereIn('od.product_id',$productIds)
            ->where('o.valid',1)->whereIn('o.current_state',[2,3,4,5,15,16,28])->where('o.date_add','>=',now()->subMonths(12))
            ->selectRaw('od.product_id,COALESCE(od.product_attribute_id,0) id_product_attribute,SUM(od.product_quantity) qty')->groupBy('od.product_id','od.product_attribute_id')->get()->keyBy(fn($r) => $key($r->product_id,$r->id_product_attribute));
        $stock = $ps->table($p . 'stock_available')->whereIn('id_product',$productIds)->where('id_shop',0)->select('id_product','id_product_attribute','quantity')->get()->keyBy(fn($r) => $key($r->id_product,$r->id_product_attribute));
        $cp = $ps->table($p . 'custom_product')->whereIn('id_product',$productIds)->select('id_product','stock_arrive','wmdeprecated','not_to_order')->get()->keyBy('id_product');
        $cpa = $ps->table($p . 'custom_product_attribute')->whereIn('id_product',$productIds)->select('id_product','id_product_attribute','stock_arrive','wmdeprecated')->get()->keyBy(fn($r) => $key($r->id_product,$r->id_product_attribute));
        $drafts = DB::table('auto_orders_purchase_list')->selectRaw('id_supplier,reference,SUM(quantity) qty')->groupBy('id_supplier','reference')->get()->keyBy(fn($r) => $r->id_supplier . ':' . $r->reference);

        $rows = [];
        foreach ($wanted as $skuKey => $sku) {
            $base = $cp->get($sku->id_product); $attr = $cpa->get($skuKey);
            if (($base->not_to_order ?? 0) || ($attr->wmdeprecated ?? $base->wmdeprecated ?? 0)) continue;
            $sold = (int)($snapshot->get($skuKey)->qty ?? 0) + (int)($recent->get($skuKey)->qty ?? 0);
            $target = (int) ceil($sold / 2);
            $physical = (int)($stock->get($skuKey)->quantity ?? 0);
            $arrive = (int)($attr->stock_arrive ?? $base->stock_arrive ?? 0);
            $draft = (int)($drafts->get($sku->id_supplier . ':' . $sku->reference)->qty ?? 0);
            $needed = max(0, $target - $physical - $arrive - $draft);
            if ($needed > 0) $rows[] = (object) compact('sku','sold','target','physical','arrive','draft','needed');
        }
        usort($rows, fn($a,$b) => $b->needed <=> $a->needed ?: strcmp($a->sku->reference,$b->sku->reference));
        return $rows;
    }
}
