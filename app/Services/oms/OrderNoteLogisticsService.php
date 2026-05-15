<?php

namespace App\Services\oms;

use App\Models\modules\oms\LogisticContainer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrderNoteLogisticsService
{
    public function buildEmptySummary(): array
    {
        return [
            'totals' => [
                'line_count' => 0,
                'lines_with_logistics' => 0,
                'missing_count' => 0,
                'volume_m3' => 0.0,
                'weight_kg' => 0.0,
                'qty_total' => 0,
                'estimated_pallet_units' => 0,
            ],
            'containers' => [],
            'suggestions' => [],
            'missing_items' => [],
        ];
    }

    public function buildSummaryFromBuilderLines(iterable $builderLines): array
    {
        $lines = collect($builderLines)->values();
        if ($lines->isEmpty()) {
            return $this->buildEmptySummary();
        }

        $productIds = $lines->pluck('product_id')->filter()->unique()->values();
        $productMeta = $productIds->isEmpty()
            ? collect()
            : DB::connection('mysql2')
                ->table('ps_product as p')
                ->whereIn('p.id_product', $productIds->all())
                ->select([
                    'p.id_product',
                    'p.width',
                    'p.height',
                    'p.depth',
                    'p.weight',
                ])
                ->get()
                ->keyBy('id_product');

        $volumeTotal = 0.0;
        $weightTotal = 0.0;
        $qtyTotal = 0;
        $linesWithLogistics = 0;
        $missingItems = [];

        foreach ($lines as $line) {
            $qty = max(0, (int) data_get($line, 'qty_ordered', 0));
            $qtyTotal += $qty;

            $product = $productMeta->get((int) data_get($line, 'product_id', 0));

            $width = (float) data_get($product, 'width', 0);
            $height = (float) data_get($product, 'height', 0);
            $depth = (float) data_get($product, 'depth', 0);
            $weight = (float) data_get($product, 'weight', 0);

            $hasLogistics = $width > 0 && $height > 0 && $depth > 0 && $weight > 0;

            if (!$hasLogistics) {
                $missingItems[] = [
                    'line_id' => (int) data_get($line, 'id', 0),
                    'product_id' => (int) data_get($line, 'product_id', 0),
                    'product_attribute_id' => (int) data_get($line, 'product_attribute_id', 0),
                    'sku' => (string) data_get($line, 'sku', '—'),
                    'name' => (string) data_get($line, 'name', 'Product'),
                    'qty_ordered' => $qty,
                ];
                continue;
            }

            $linesWithLogistics++;
            $volumePerUnitM3 = $this->cm3ToM3($width * $height * $depth);
            $volumeTotal += $volumePerUnitM3 * $qty;
            $weightTotal += $weight * $qty;
        }

        try {
            $containers = LogisticContainer::query()
                ->where('is_active', true)
                ->orderBy('type')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        } catch (Throwable $e) {
            $containers = collect();
        }

        $containerRows = $containers->map(function (LogisticContainer $container) use ($volumeTotal, $weightTotal) {
            $analysis = $this->analyzeContainer($container, $volumeTotal, $weightTotal, 0);

            return [
                'id' => (int) $container->id,
                'type' => (string) $container->type,
                'name' => (string) $container->name,
                'units_needed' => $analysis['units_needed'],
                'fits' => $analysis['fits'],
                'volume_usage_pct' => $analysis['volume_usage_pct'],
                'weight_usage_pct' => $analysis['weight_usage_pct'],
                'capacity_volume_m3' => $analysis['capacity_volume_m3'],
                'capacity_weight_kg' => $analysis['capacity_weight_kg'],
                'max_pallets' => $container->max_pallets !== null ? (int) $container->max_pallets : null,
            ];
        });

        $bestPallet = $containerRows
            ->where('type', 'pallet')
            ->sortBy([
                ['units_needed', 'asc'],
                ['volume_usage_pct', 'desc'],
            ])
            ->first();

        $estimatedPalletUnits = (int) data_get($bestPallet, 'units_needed', 0);

        $containerRows = $containers->map(function (LogisticContainer $container) use ($volumeTotal, $weightTotal, $estimatedPalletUnits) {
            $analysis = $this->analyzeContainer($container, $volumeTotal, $weightTotal, $estimatedPalletUnits);

            return [
                'id' => (int) $container->id,
                'type' => (string) $container->type,
                'name' => (string) $container->name,
                'units_needed' => $analysis['units_needed'],
                'fits' => $analysis['fits'],
                'volume_usage_pct' => $analysis['volume_usage_pct'],
                'weight_usage_pct' => $analysis['weight_usage_pct'],
                'capacity_volume_m3' => $analysis['capacity_volume_m3'],
                'capacity_weight_kg' => $analysis['capacity_weight_kg'],
                'max_pallets' => $container->max_pallets !== null ? (int) $container->max_pallets : null,
            ];
        })->values();

        $suggestions = collect(['box', 'pallet', 'container'])
            ->map(function (string $type) use ($containerRows) {
                return $containerRows
                    ->where('type', $type)
                    ->sortBy([
                        ['units_needed', 'asc'],
                        ['volume_usage_pct', 'desc'],
                    ])
                    ->first();
            })
            ->filter()
            ->values()
            ->all();

        return [
            'totals' => [
                'line_count' => (int) $lines->count(),
                'lines_with_logistics' => (int) $linesWithLogistics,
                'missing_count' => count($missingItems),
                'volume_m3' => round($volumeTotal, 3),
                'weight_kg' => round($weightTotal, 2),
                'qty_total' => (int) $qtyTotal,
                'estimated_pallet_units' => $estimatedPalletUnits,
            ],
            'containers' => $containerRows->all(),
            'suggestions' => $suggestions,
            'missing_items' => $missingItems,
        ];
    }

    public function calculateFromOrderNote($orderNote): array
    {
        $lines = collect($orderNote->lines ?? [])->map(function ($line) {
            return [
                'id' => data_get($line, 'id'),
                'product_id' => (int) data_get($line, 'product_id', 0),
                'product_attribute_id' => (int) data_get($line, 'product_attribute_id', 0),
                'qty_ordered' => (int) data_get($line, 'qty_ordered', 0),
                'sku' => (string) (data_get($line, 'reference') ?: data_get($line, 'sku') ?: '—'),
                'name' => (string) (data_get($line, 'name') ?: 'Product'),
            ];
        });

        return $this->buildSummaryFromBuilderLines($lines);
    }

    public function calculateFromBilledOrder($billedOrder): array
    {
        $lines = collect($billedOrder->lines ?? [])->map(function ($line) {
            $qty = (int) data_get($line, 'qty_billed', 0);
            if ($qty <= 0) {
                $qty = (int) data_get($line, 'qty_ordered', 0);
            }

            return [
                'id' => data_get($line, 'id'),
                'product_id' => (int) data_get($line, 'product_id', 0),
                'product_attribute_id' => (int) data_get($line, 'product_attribute_id', 0),
                'qty_ordered' => max(0, $qty),
                'sku' => (string) (data_get($line, 'reference') ?: data_get($line, 'sku') ?: '—'),
                'name' => (string) (data_get($line, 'name') ?: 'Product'),
            ];
        });

        return $this->buildSummaryFromBuilderLines($lines);
    }
    
    protected function analyzeContainer(LogisticContainer $container, float $volumeTotal, float $weightTotal, int $estimatedPalletUnits): array
    {
        $capacityVolume = $this->cm3ToM3(
            max(0, (float) $container->width_cm) *
            max(0, (float) $container->height_cm) *
            max(0, (float) $container->depth_cm)
        );

        $capacityWeight = max(0, (float) $container->max_weight_kg);

        $unitsByVolume = $capacityVolume > 0 ? (int) ceil($volumeTotal / $capacityVolume) : 0;
        $unitsByWeight = $capacityWeight > 0 ? (int) ceil($weightTotal / $capacityWeight) : 0;
        $unitsByPallets = 0;

        if ($container->type === 'container' && (int) $container->max_pallets > 0 && $estimatedPalletUnits > 0) {
            $unitsByPallets = (int) ceil($estimatedPalletUnits / max(1, (int) $container->max_pallets));
        }

        $unitsNeeded = max(1, $unitsByVolume, $unitsByWeight, $unitsByPallets);

        return [
            'units_needed' => $unitsNeeded,
            'fits' => $unitsNeeded === 1,
            'volume_usage_pct' => $capacityVolume > 0 && $unitsNeeded > 0 ? round(($volumeTotal / ($capacityVolume * $unitsNeeded)) * 100, 1) : 0.0,
            'weight_usage_pct' => $capacityWeight > 0 && $unitsNeeded > 0 ? round(($weightTotal / ($capacityWeight * $unitsNeeded)) * 100, 1) : 0.0,
            'capacity_volume_m3' => round($capacityVolume, 3),
            'capacity_weight_kg' => round($capacityWeight, 2),
        ];
    }

    protected function cm3ToM3(float $cm3): float
    {
        return $cm3 > 0 ? ($cm3 / 1000000) : 0.0;
    }
}
