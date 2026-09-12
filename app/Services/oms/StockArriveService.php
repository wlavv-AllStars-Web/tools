<?php

namespace App\Services\oms;

use Illuminate\Support\Facades\DB;

class StockArriveService
{
    private ?bool $customAttributeHasProductId = null;

    public function adjust(int $productId, int $productAttributeId, int $delta): void
    {
        if ($productId <= 0 || $delta === 0) {
            return;
        }

        foreach ($this->targetsForReference($productId, max(0, $productAttributeId)) as $target) {
            if ($target->id_product_attribute > 0) {
                $this->ensureCustomProductAttributeRow($target->id_product, $target->id_product_attribute);
                DB::connection('mysql2')
                    ->table($this->psPrefix().'custom_product_attribute')
                    ->where('id_product_attribute', $target->id_product_attribute)
                    ->update([
                        'stock_arrive' => DB::raw('GREATEST(COALESCE(stock_arrive, 0) + ('.(int) $delta.'), 0)'),
                    ]);

                continue;
            }

            $this->ensureCustomProductRow($target->id_product);
            DB::connection('mysql2')
                ->table($this->psPrefix().'custom_product')
                ->where('id_product', $target->id_product)
                ->update([
                    'stock_arrive' => DB::raw('GREATEST(COALESCE(stock_arrive, 0) + ('.(int) $delta.'), 0)'),
                ]);
        }
    }

    /** Return only products/combinations that share the exact sellable reference. */
    protected function targetsForReference(int $productId, int $productAttributeId)
    {
        $db = DB::connection('mysql2');
        $prefix = $this->psPrefix();

        if ($productAttributeId > 0) {
            $reference = trim((string) $db->table($prefix.'product_attribute')
                ->where('id_product', $productId)
                ->where('id_product_attribute', $productAttributeId)
                ->value('reference'));
            if ($reference === '') {
                return collect([(object) ['id_product' => $productId, 'id_product_attribute' => $productAttributeId]]);
            }

            return $db->table($prefix.'product_attribute')
                ->where('reference', $reference)
                ->get(['id_product', 'id_product_attribute'])
                ->map(fn ($row) => (object) ['id_product' => (int) $row->id_product, 'id_product_attribute' => (int) $row->id_product_attribute])
                ->unique(fn ($row) => $row->id_product.':'.$row->id_product_attribute)
                ->values();
        }

        $reference = trim((string) $db->table($prefix.'product')
            ->where('id_product', $productId)
            ->value('reference'));
        if ($reference === '') {
            return collect([(object) ['id_product' => $productId, 'id_product_attribute' => 0]]);
        }

        return $db->table($prefix.'product')
            ->where('reference', $reference)
            ->get(['id_product'])
            ->map(fn ($row) => (object) ['id_product' => (int) $row->id_product, 'id_product_attribute' => 0])
            ->unique(fn ($row) => $row->id_product.':0')
            ->values();
    }
    protected function ensureCustomProductRow(int $productId): void
    {
        $exists = DB::connection('mysql2')
            ->table($this->psPrefix() . 'custom_product')
            ->where('id_product', $productId)
            ->exists();

        if ($exists) {
            return;
        }

        DB::connection('mysql2')
            ->table($this->psPrefix() . 'custom_product')
            ->insert([
                'id_product' => $productId,
                'stock_arrive' => 0,
            ]);
    }

    protected function ensureCustomProductAttributeRow(int $productId, int $productAttributeId): void
    {
        $exists = DB::connection('mysql2')
            ->table($this->psPrefix() . 'custom_product_attribute')
            ->where('id_product_attribute', $productAttributeId)
            ->exists();

        if ($exists) {
            return;
        }

        $payload = [
            'id_product_attribute' => $productAttributeId,
            'stock_arrive' => 0,
        ];

        if ($this->customAttributeHasProductId()) {
            $payload['id_product'] = $productId;
        }

        DB::connection('mysql2')
            ->table($this->psPrefix() . 'custom_product_attribute')
            ->insert($payload);
    }

    protected function customAttributeHasProductId(): bool
    {
        if ($this->customAttributeHasProductId !== null) {
            return $this->customAttributeHasProductId;
        }

        return $this->customAttributeHasProductId = DB::connection('mysql2')
            ->getSchemaBuilder()
            ->hasColumn($this->psPrefix() . 'custom_product_attribute', 'id_product');
    }

    protected function psPrefix(): string
    {
        return (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');
    }
}
