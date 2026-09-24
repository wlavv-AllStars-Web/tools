<?php
namespace App\Services\StockAudit;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StockAuditService
{
    private array $combinationProducts = [];
    private array $technicalProducts = [];
    private array $packComponents = [];

    public function createSnapshot(): array
    {
        $prefix = (string) env('DB2_DB_prefix', 'ps_');
        $rows = DB::connection('mysql2')->table($prefix.'stock_available as sa')
            ->leftJoin($prefix.'product as p', 'p.id_product', '=', 'sa.id_product')
            ->leftJoin($prefix.'product_attribute as pa', 'pa.id_product_attribute', '=', 'sa.id_product_attribute')
            ->leftJoin($prefix.'custom_product as cp', function ($join) { $join->on('cp.id_product', '=', 'sa.id_product')->where('sa.id_product_attribute', '=', 0); })
            ->leftJoin($prefix.'custom_product_attribute as cpa', 'cpa.id_product_attribute', '=', 'sa.id_product_attribute')
            ->where('sa.id_shop', 0)
            ->selectRaw('sa.id_product,sa.id_product_attribute,COALESCE(NULLIF(pa.reference, \"\"),p.reference) reference,sa.quantity,COALESCE(cpa.stock_arrive,cp.stock_arrive,0) stock_arrive')
            ->orderBy('sa.id_product')->orderBy('sa.id_product_attribute')->cursor();

        $timestamp = now();
        $path = 'stock-audit/snapshots/stock-'.$timestamp->format('Ymd_His').'.json.gz';
        $buffer = '';
        $count = 0;
        foreach ($rows as $row) {
            $buffer .= json_encode([
                'id_product'=>(int)$row->id_product, 'id_product_attribute'=>(int)$row->id_product_attribute,
                'reference'=>(string)$row->reference, 'quantity'=>(int)$row->quantity,
                'stock_arrive'=>(int)$row->stock_arrive,
            ], JSON_UNESCAPED_UNICODE)."\n";
            ++$count;
        }
        Storage::put($path, gzencode($buffer, 6));
        $size = Storage::size($path);
        $snapshot = DB::table('stock_audit_snapshots')->insertGetId([
            'path'=>$path, 'items_count'=>$count, 'file_size'=>$size, 'captured_at'=>$timestamp,
            'created_at'=>now(), 'updated_at'=>now(),
        ]);
        return ['id'=>$snapshot, 'path'=>$path, 'items_count'=>$count, 'file_size'=>$size];
    }

    public function record(array $data): void
    {
        $productId = (int) ($data['id_product'] ?? 0);
        $components = $productId > 0 ? $this->componentsOfPack($productId) : [];

        if ($components !== []) {
            $this->recordPackComponents($data, $components);

            return;
        }

        $this->recordOne($data);
    }

    private function recordPackComponents(array $data, array $components): void
    {
        $packBefore = (int) ($data['quantity_before'] ?? 0);
        $packAfter = (int) ($data['quantity_after'] ?? 0);
        $packDelta = isset($data['quantity_delta'])
            ? (int) $data['quantity_delta']
            : $packAfter - $packBefore;

        if ($packDelta === 0) {
            return;
        }

        $packReference = trim((string) ($data['reference'] ?? ''));
        $packProductId = (int) $data['id_product'];
        $meta = $this->metaArray($data['meta'] ?? []);

        foreach ($components as $component) {
            $componentDelta = $packDelta * (int) $component->quantity_in_pack;
            $componentAfter = (int) $component->stock_quantity;
            $componentReference = trim((string) $component->reference);
            $reference = $componentReference !== '' && $packReference !== ''
                ? $componentReference . ' ( ' . $packReference . ' )'
                : ($componentReference ?: $packReference);

            $componentMeta = $meta;
            $componentMeta['pack'] = [
                'id_product' => $packProductId,
                'reference' => $packReference,
                'quantity_in_pack' => (int) $component->quantity_in_pack,
            ];

            $componentData = array_merge($data, [
                'id_product' => (int) $component->id_product,
                'id_product_attribute' => (int) $component->id_product_attribute,
                'reference' => $reference !== '' ? $reference : null,
                'operation' => substr((string) ($data['operation'] ?? 'update') . '_pack_component', 0, 64),
                'quantity_before' => $componentAfter - $componentDelta,
                'quantity_after' => $componentAfter,
                'quantity_delta' => $componentDelta,
                'meta' => json_encode($componentMeta),
            ]);

            $this->recordOne($componentData);
        }
    }

    private function recordOne(array $data): void
    {
        $productId = (int) ($data['id_product'] ?? 0);
        $attributeId = (int) ($data['id_product_attribute'] ?? 0);

        if ($productId > 0 && $this->isTechnicalProductsProduct($productId)) {
            return;
        }

        if ($attributeId === 0 && $productId > 0 && $this->productHasCombinations($productId)) {
            return;
        }

        DB::table('stock_audit_movements')->insert(array_merge([
            'id_product_attribute'=>0, 'source'=>'unknown', 'operation'=>'update',
            'occurred_at'=>now(), 'created_at'=>now(), 'updated_at'=>now(),
        ], $data));
    }

    private function componentsOfPack(int $packProductId): array
    {
        if (array_key_exists($packProductId, $this->packComponents)) {
            return $this->packComponents[$packProductId];
        }

        try {
            $prefix = $this->prestashopPrefix();
            $components = DB::connection('mysql2')
                ->table($prefix . 'pack as pack')
                ->join($prefix . 'product as product', 'product.id_product', '=', 'pack.id_product_item')
                ->leftJoin($prefix . 'product_attribute as attribute', 'attribute.id_product_attribute', '=', 'pack.id_product_attribute_item')
                ->leftJoin($prefix . 'stock_available as stock', function ($join) {
                    $join->on('stock.id_product', '=', 'pack.id_product_item')
                        ->on('stock.id_product_attribute', '=', 'pack.id_product_attribute_item')
                        ->where('stock.id_shop', '=', 0);
                })
                ->where('pack.id_product_pack', $packProductId)
                ->groupBy('pack.id_product_item', 'pack.id_product_attribute_item', 'product.reference', 'attribute.reference')
                ->selectRaw('pack.id_product_item as id_product, pack.id_product_attribute_item as id_product_attribute, SUM(pack.quantity) as quantity_in_pack, COALESCE(NULLIF(attribute.reference, \"\"), product.reference) as reference, COALESCE(MAX(stock.quantity), 0) as stock_quantity')
                ->get()
                ->all();

            return $this->packComponents[$packProductId] = $components;
        } catch (\Throwable) {
            return $this->packComponents[$packProductId] = [];
        }
    }

    private function productHasCombinations(int $productId): bool
    {
        if (array_key_exists($productId, $this->combinationProducts)) {
            return $this->combinationProducts[$productId];
        }

        try {
            return $this->combinationProducts[$productId] = DB::connection('mysql2')
                ->table($this->prestashopPrefix() . 'product_attribute')
                ->where('id_product', $productId)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    private function isTechnicalProductsProduct(int $productId): bool
    {
        if (array_key_exists($productId, $this->technicalProducts)) {
            return $this->technicalProducts[$productId];
        }

        try {
            $prefix = $this->prestashopPrefix();

            return $this->technicalProducts[$productId] = DB::connection('mysql2')
                ->table($prefix . 'product as product')
                ->join($prefix . 'manufacturer as manufacturer', 'manufacturer.id_manufacturer', '=', 'product.id_manufacturer')
                ->where('product.id_product', $productId)
                ->whereRaw('LOWER(TRIM(manufacturer.name)) = ?', ['technical products'])
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    private function metaArray($meta): array
    {
        if (is_array($meta)) {
            return $meta;
        }

        return is_string($meta) ? (json_decode($meta, true) ?: []) : [];
    }

    private function prestashopPrefix(): string
    {
        return (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');
    }
}