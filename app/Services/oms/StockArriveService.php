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

        $productAttributeId = max(0, $productAttributeId);

        if ($productAttributeId > 0) {
            $this->ensureCustomProductAttributeRow($productId, $productAttributeId);

            DB::connection('mysql2')
                ->table($this->psPrefix() . 'custom_product_attribute')
                ->where('id_product_attribute', $productAttributeId)
                ->update([
                    'stock_arrive' => DB::raw('GREATEST(COALESCE(stock_arrive, 0) + (' . (int) $delta . '), 0)'),
                ]);

            return;
        }

        $this->ensureCustomProductRow($productId);

        DB::connection('mysql2')
            ->table($this->psPrefix() . 'custom_product')
            ->where('id_product', $productId)
            ->update([
                'stock_arrive' => DB::raw('GREATEST(COALESCE(stock_arrive, 0) + (' . (int) $delta . '), 0)'),
            ]);
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
