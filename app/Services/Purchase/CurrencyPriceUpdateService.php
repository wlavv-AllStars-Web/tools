<?php

namespace App\Services\Purchase;

use App\Models\modules\currency_price_update\CurrencyPriceUpdateBatch;
use App\Models\modules\currency_price_update\CurrencyPriceUpdateLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class CurrencyPriceUpdateService
{
    private const SUPPORTED_ISO = ['EUR', 'GBP', 'USD', 'JPY'];

    public function currencies(): Collection
    {
        $prefix = $this->prefix();

        $allCurrencies = DB::connection('mysql2')
            ->table($prefix . 'currency')
            ->get(['id_currency', 'iso_code', 'name', 'conversion_rate'])
            ->keyBy(fn ($currency) => strtoupper((string) $currency->iso_code));

        return collect(self::SUPPORTED_ISO)->map(function (string $iso) use ($allCurrencies) {
            $currency = $allCurrencies->get($iso);
            if (!$currency) return null;

            $currency->purchase_conversion_rate = (float) $currency->conversion_rate;
            $currency->sale_currency_iso = $this->saleCurrencyIso($iso);
            $currency->sale_conversion_rate = (float) ($allCurrencies->get($currency->sale_currency_iso)->conversion_rate ?? $currency->conversion_rate);

            return $currency;
        })->filter()->values();
    }

    public function currency(int $currencyId): object
    {
        $currency = $this->currencies()->firstWhere('id_currency', $currencyId);

        if (!$currency) {
            throw new RuntimeException('The selected currency is not supported.');
        }

        return $currency;
    }

    public function brandsForCurrency(int $currencyId): Collection
    {
        $prefix = $this->prefix();

        return DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->join($prefix . 'manufacturer as m', 'm.id_manufacturer', '=', 'p.id_manufacturer')
            ->join($prefix . 'custom_manufacturer as cm', 'cm.id_manufacturer', '=', 'p.id_manufacturer')
            ->where('cm.id_currency', $currencyId)
            ->groupBy('p.id_manufacturer', 'm.name')
            ->orderBy('m.name')
            ->get([
                'p.id_manufacturer as manufacturer_id',
                'm.name as manufacturer',
                DB::raw('COUNT(DISTINCT p.id_product) as products_count'),
            ]);
    }

    /**
     * Builds the exact preview used both by the confirmation screen and by apply().
     * Base-currency data is intentionally read-only: only EUR price columns are changed.
     */
    public function preview(int $currencyId, float $newPurchaseRate, float $newSaleRate): array
    {
        $currency = $this->currency($currencyId);
        $newPurchaseRate = $this->normaliseRate((string) $currency->iso_code, $newPurchaseRate);
        $newSaleRate = $this->normaliseRate((string) $currency->iso_code, $newSaleRate);
        $brands = $this->brandsForCurrency($currencyId);
        $brandIds = $brands->pluck('manufacturer_id')->map(fn ($id) => (int) $id)->all();

        if ($brandIds === []) {
            return compact('currency', 'newPurchaseRate', 'newSaleRate', 'brands') + ['linesByBrand' => collect(), 'summary' => $this->emptySummary()];
        }

        $productRows = $this->productRows($brandIds);
        $productPreviewById = [];
        $linesByBrand = collect();

        foreach ($productRows as $row) {
            $line = $this->productLine($row, $newPurchaseRate, $newSaleRate);
            $productPreviewById[(int) $line['product_id']] = $line;
            $linesByBrand->put((int) $line['manufacturer_id'], collect([$line]));
        }

        foreach ($this->attributeRows($brandIds) as $row) {
            $parent = $productPreviewById[(int) $row->id_product] ?? null;
            $line = $this->attributeLine($row, $parent, $newPurchaseRate, $newSaleRate);
            $manufacturerId = (int) $line['manufacturer_id'];
            $linesByBrand->put($manufacturerId, $linesByBrand->get($manufacturerId, collect())->push($line));
        }

        $linesByBrand = $brands->mapWithKeys(function ($brand) use ($linesByBrand) {
            return [(int) $brand->manufacturer_id => $linesByBrand->get((int) $brand->manufacturer_id, collect())
                ->sortBy(fn (array $line) => $line['line_type'] . '|' . $line['reference'])
                ->values()];
        });

        $allLines = $linesByBrand->flatten(1);

        return compact('currency', 'newPurchaseRate', 'newSaleRate', 'brands', 'linesByBrand') + [
            'summary' => [
                'lines' => $allLines->count(),
                'invalid_lines' => $allLines->where('invalid_for_update', true)->count(),
                'purchase_updates' => $allLines->where('purchase_updated', true)->count(),
                'sale_updates' => $allLines->where('sale_updated', true)->count(),
                'brands' => $brands->count(),
            ],
        ];
    }

    public function apply(int $currencyId, float $expectedOldPurchaseRate, float $expectedOldSaleRate, float $newPurchaseRate, float $newSaleRate, ?object $user): CurrencyPriceUpdateBatch
    {
        $preview = $this->preview($currencyId, $newPurchaseRate, $newSaleRate);
        $currency = $preview['currency'];
        $currentPurchaseRate = (float) $currency->purchase_conversion_rate;
        $currentSaleRate = (float) $currency->sale_conversion_rate;

        if (abs($currentPurchaseRate - $expectedOldPurchaseRate) > 0.00000001 || abs($currentSaleRate - $expectedOldSaleRate) > 0.00000001) {
            throw new RuntimeException('The current purchase or sale rate changed after the preview. Please generate a new preview.');
        }

        $batch = CurrencyPriceUpdateBatch::create([
            'currency_id' => $currencyId,
            'currency_iso' => $currency->iso_code,
            'old_purchase_conversion_rate' => $currentPurchaseRate,
            'new_purchase_conversion_rate' => $preview['newPurchaseRate'],
            'old_sale_conversion_rate' => $currentSaleRate,
            'new_sale_conversion_rate' => $preview['newSaleRate'],
            'status' => 'applying',
            'applied_by' => $user?->id,
            'applied_by_name' => $user?->name,
        ]);

        $lines = $preview['linesByBrand']->flatten(1)->values();
        $preparedLines = $lines->map(function (array $line) {
            $oldSnapshot = $this->lineSnapshot($line);
            $newSnapshot = $this->newLineSnapshot($line, $oldSnapshot);

            $line['_old_snapshot'] = $oldSnapshot;
            $line['_new_snapshot'] = $newSnapshot;

            return $line;
        });

        CurrencyPriceUpdateLine::insert($preparedLines
            ->map(fn (array $line) => $this->lineLogRow($batch, $line))
            ->all());

        try {
            DB::connection('mysql2')->transaction(function () use ($preparedLines) {
                foreach ($preparedLines as $line) {
                    $this->writeLineSnapshot($line, $line['_new_snapshot']);
                }
            });
        } catch (\Throwable $exception) {
            $batch->update(['status' => 'failed']);
            throw $exception;
        }

        $batch->update(['status' => 'applied']);

        return $batch->fresh();
    }

    public function rollback(CurrencyPriceUpdateBatch $batch, ?object $user): void
    {
        if ($batch->status !== 'applied') {
            throw new RuntimeException('Only an applied update can be reverted.');
        }

        $hasNewerAppliedBatch = CurrencyPriceUpdateBatch::query()
            ->where('currency_id', $batch->currency_id)
            ->where('status', 'applied')
            ->where('id', '>', $batch->id)
            ->exists();
        if ($hasNewerAppliedBatch) {
            throw new RuntimeException('A newer update exists for this currency. Revert the most recent update first.');
        }

        $lines = $batch->lines()->orderBy('line_type')->orderBy('id')->get();

        DB::connection('mysql2')->transaction(function () use ($batch, $lines) {
            foreach ($lines as $line) {
                $this->writeLineSnapshot([
                    'line_type' => $line->line_type,
                    'product_id' => (int) $line->product_id,
                    'product_attribute_id' => (int) $line->product_attribute_id,
                ], $line->old_snapshot ?: [], true);
            }

        });

        $batch->update([
            'status' => 'reverted',
            'reverted_at' => now(),
            'reverted_by' => $user?->id,
            'reverted_by_name' => $user?->name,
        ]);
    }

    private function productRows(array $brandIds): Collection
    {
        $prefix = $this->prefix();

        return DB::connection('mysql2')->table($prefix . 'product as p')
            ->join($prefix . 'manufacturer as m', 'm.id_manufacturer', '=', 'p.id_manufacturer')
            ->leftJoin($prefix . 'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->whereIn('p.id_manufacturer', $brandIds)
            ->orderBy('m.name')->orderBy('p.reference')
            ->get([
                'p.id_product', 'p.id_manufacturer', 'p.reference', 'p.wholesale_price', 'p.price',
                'm.name as manufacturer',
                DB::raw('COALESCE(cp.wholesale_price_base_currency, 0) as purchase_base'),
                DB::raw('COALESCE(cp.price_base_currency, 0) as sale_base'),
            ]);
    }

    private function attributeRows(array $brandIds): Collection
    {
        $prefix = $this->prefix();

        return DB::connection('mysql2')->table($prefix . 'product_attribute as pa')
            ->join($prefix . 'product as p', 'p.id_product', '=', 'pa.id_product')
            ->join($prefix . 'manufacturer as m', 'm.id_manufacturer', '=', 'p.id_manufacturer')
            ->leftJoin($prefix . 'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->leftJoin($prefix . 'custom_product_attribute as cpa', 'cpa.id_product_attribute', '=', 'pa.id_product_attribute')
            ->whereIn('p.id_manufacturer', $brandIds)
            ->orderBy('m.name')->orderBy('p.reference')->orderBy('pa.reference')
            ->get([
                'p.id_product', 'p.id_manufacturer', 'p.reference as product_reference', 'p.price as product_price',
                'pa.id_product_attribute', 'pa.reference', 'pa.wholesale_price', 'pa.price',
                'm.name as manufacturer',
                DB::raw('COALESCE(cp.price_base_currency, 0) as product_sale_base'),
                DB::raw('COALESCE(cpa.wholesale_price_base_currency, 0) as purchase_base'),
                DB::raw('COALESCE(cpa.price_base_currency, 0) as sale_base_impact'),
            ]);
    }

    private function productLine(object $row, float $newPurchaseRate, float $newSaleRate): array
    {
        $purchaseBase = (float) $row->purchase_base;
        $saleBase = (float) $row->sale_base;
        $oldPurchase = (float) $row->wholesale_price;
        $oldSale = (float) $row->price;
        $validForUpdate = $purchaseBase > 0 && $saleBase > 0;
        $purchaseUpdated = $validForUpdate;
        $saleUpdated = $validForUpdate;

        return [
            'manufacturer_id' => (int) $row->id_manufacturer,
            'manufacturer' => (string) $row->manufacturer,
            'product_id' => (int) $row->id_product,
            'product_attribute_id' => 0,
            'reference' => (string) $row->reference,
            'line_type' => 'product',
            'purchase_base_currency' => $purchaseUpdated ? $purchaseBase : null,
            'sale_base_currency' => $saleUpdated ? $saleBase : null,
            'old_purchase_eur' => $oldPurchase,
            'new_purchase_eur' => $purchaseUpdated ? $this->toEur($purchaseBase, $newPurchaseRate) : $oldPurchase,
            'old_sale_eur' => $oldSale,
            'new_sale_eur' => $saleUpdated ? $this->toEur($saleBase, $newSaleRate) : $oldSale,
            'purchase_updated' => $purchaseUpdated,
            'sale_updated' => $saleUpdated,
            'invalid_for_update' => !$validForUpdate,
            'reason' => $validForUpdate ? null : 'purchase_or_sale_base_price_missing',
        ];
    }

    private function attributeLine(object $row, ?array $parent, float $newPurchaseRate, float $newSaleRate): array
    {
        $purchaseBase = (float) $row->purchase_base;
        $saleImpactBase = (float) $row->sale_base_impact;
        $parentSaleBase = (float) $row->product_sale_base;
        $parentOldSale = (float) $row->product_price;
        $parentNewSale = (float) ($parent['new_sale_eur'] ?? $parentOldSale);
        $oldImpact = (float) $row->price;
        $validForUpdate = $purchaseBase > 0 && $parentSaleBase > 0 && $saleImpactBase > 0;
        $purchaseUpdated = $validForUpdate;
        $saleUpdated = $validForUpdate;

        return [
            'manufacturer_id' => (int) $row->id_manufacturer,
            'manufacturer' => (string) $row->manufacturer,
            'product_id' => (int) $row->id_product,
            'product_attribute_id' => (int) $row->id_product_attribute,
            'reference' => (string) ($row->reference ?: $row->product_reference),
            'line_type' => 'attribute',
            'purchase_base_currency' => $purchaseUpdated ? $purchaseBase : null,
            'sale_base_currency' => $parentSaleBase > 0 ? $parentSaleBase + $saleImpactBase : null,
            'old_purchase_eur' => (float) $row->wholesale_price,
            'new_purchase_eur' => $purchaseUpdated ? $this->toEur($purchaseBase, $newPurchaseRate) : (float) $row->wholesale_price,
            'old_sale_eur' => $parentOldSale + $oldImpact,
            'new_sale_eur' => $parentNewSale + ($saleUpdated ? $this->toEur($saleImpactBase, $newSaleRate) : $oldImpact),
            'new_sale_impact_eur' => $saleUpdated ? $this->toEur($saleImpactBase, $newSaleRate) : $oldImpact,
            'purchase_updated' => $purchaseUpdated,
            'sale_updated' => $saleUpdated,
            'invalid_for_update' => !$validForUpdate,
            'reason' => $validForUpdate ? null : 'purchase_or_sale_base_price_missing',
        ];
    }

    private function lineSnapshot(array $line): array
    {
        $prefix = $this->prefix();
        $connection = DB::connection('mysql2');
        $isAttribute = $line['line_type'] === 'attribute';
        $table = $prefix . ($isAttribute ? 'product_attribute' : 'product');
        $key = $isAttribute ? 'id_product_attribute' : 'id_product';
        $id = $isAttribute ? $line['product_attribute_id'] : $line['product_id'];
        $shopTable = $prefix . ($isAttribute ? 'product_attribute_shop' : 'product_shop');
        $shopKey = $key;

        $core = (array) $connection->table($table)->where($key, $id)->first([$key, 'wholesale_price', 'price']);
        $shops = $this->hasPrestashopTable($shopTable)
            ? $connection->table($shopTable)->where($shopKey, $id)->get(['id_shop', $shopKey, 'wholesale_price', 'price'])->map(fn ($row) => (array) $row)->all()
            : [];

        $productBase = (array) $connection->table($prefix . 'custom_product')
            ->where('id_product', $line['product_id'])
            ->first(['id_product', 'wholesale_price_base_currency', 'price_base_currency']);
        $attributeBase = $isAttribute
            ? (array) $connection->table($prefix . 'custom_product_attribute')
                ->where('id_product_attribute', $line['product_attribute_id'])
                ->first(['id_product_attribute', 'wholesale_price_base_currency', 'price_base_currency'])
            : null;

        // This is audit-only data. writeLineSnapshot deliberately never writes it,
        // so the supplier-currency base values remain immutable through apply/revert.
        return ['core' => $core, 'shops' => $shops, 'base_currency' => [
            'product' => $productBase,
            'attribute' => $attributeBase,
        ]];
    }

    private function newLineSnapshot(array $line, array $oldSnapshot): array
    {
        $newSnapshot = $oldSnapshot;
        $newSnapshot['core']['wholesale_price'] = $line['new_purchase_eur'];

        if ($line['line_type'] === 'product') {
            $newSnapshot['core']['price'] = $line['new_sale_eur'];
        } elseif ($line['sale_updated']) {
            // Combinations store only a price impact in PrestaShop. The preview shows
            // the parent + impact total, but the raw attribute value stays the impact.
            $newSnapshot['core']['price'] = $line['new_sale_impact_eur'];
        }

        foreach ($newSnapshot['shops'] as &$shop) {
            $shop['wholesale_price'] = $newSnapshot['core']['wholesale_price'];
            $shop['price'] = $newSnapshot['core']['price'];
        }
        unset($shop);

        return $newSnapshot;
    }

    private function writeLineSnapshot(array $line, array $snapshot, bool $restore = false): void
    {
        if (empty($snapshot['core'])) return;

        $purchaseUpdated = (bool) ($line['purchase_updated'] ?? $restore);
        $saleUpdated = (bool) ($line['sale_updated'] ?? $restore);
        if (!$purchaseUpdated && !$saleUpdated) return;

        $prefix = $this->prefix();
        $connection = DB::connection('mysql2');
        $isAttribute = ($line['line_type'] ?? '') === 'attribute';
        $table = $prefix . ($isAttribute ? 'product_attribute' : 'product');
        $key = $isAttribute ? 'id_product_attribute' : 'id_product';
        $id = $isAttribute ? (int) $line['product_attribute_id'] : (int) $line['product_id'];
        $shopTable = $prefix . ($isAttribute ? 'product_attribute_shop' : 'product_shop');

        $values = [];
        if ($purchaseUpdated) $values['wholesale_price'] = $snapshot['core']['wholesale_price'];
        if ($saleUpdated) $values['price'] = $snapshot['core']['price'];
        $connection->table($table)->where($key, $id)->update($values);

        if (!$this->hasPrestashopTable($shopTable)) return;
        foreach ($snapshot['shops'] ?? [] as $shop) {
            $shopValues = [];
            if ($purchaseUpdated) $shopValues['wholesale_price'] = $shop['wholesale_price'];
            if ($saleUpdated) $shopValues['price'] = $shop['price'];
            $connection->table($shopTable)
                ->where('id_shop', $shop['id_shop'])
                ->where($key, $id)
                ->update($shopValues);
        }
    }

    private function lineLogRow(CurrencyPriceUpdateBatch $batch, array $line): array
    {
        return [
            'batch_id' => $batch->id,
            'manufacturer_id' => $line['manufacturer_id'],
            'manufacturer' => $line['manufacturer'],
            'product_id' => $line['product_id'],
            'product_attribute_id' => $line['product_attribute_id'],
            'reference' => $line['reference'],
            'line_type' => $line['line_type'],
            'purchase_base_currency' => $line['purchase_base_currency'],
            'sale_base_currency' => $line['sale_base_currency'],
            'old_purchase_eur' => $line['old_purchase_eur'],
            'new_purchase_eur' => $line['new_purchase_eur'],
            'old_sale_eur' => $line['old_sale_eur'],
            'new_sale_eur' => $line['new_sale_eur'],
            'purchase_updated' => $line['purchase_updated'],
            'sale_updated' => $line['sale_updated'],
            'reason' => $line['reason'],
            'old_snapshot' => json_encode($line['_old_snapshot'] ?? []),
            'new_snapshot' => json_encode($line['_new_snapshot'] ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function toEur(float $value, float $rate): float
    {
        return round($value / $rate, 6);
    }

    private function normaliseRate(string $iso, float $rate): float
    {
        if ($iso === 'EUR') return 1.0;
        if ($rate <= 0) throw new RuntimeException('The new exchange rate must be greater than zero.');

        return round($rate, 8);
    }

    private function emptySummary(): array
    {
        return ['lines' => 0, 'invalid_lines' => 0, 'purchase_updates' => 0, 'sale_updates' => 0, 'brands' => 0];
    }

    private function prefix(): string
    {
        return env('DB2_DB_prefix', env('DB2_prefix', 'ps_'));
    }

    private function hasPrestashopTable(string $table): bool
    {
        $unqualified = str_contains($table, '.') ? substr($table, strrpos($table, '.') + 1) : $table;

        return Schema::connection('mysql2')->hasTable($unqualified);
    }

    private function saleCurrencyIso(string $purchaseCurrencyIso): string
    {
        return match (strtoupper($purchaseCurrencyIso)) {
            'USD' => 'USS',
            'GBP' => 'GBS',
            'JPY' => 'JPS',
            default => strtoupper($purchaseCurrencyIso),
        };
    }
}
