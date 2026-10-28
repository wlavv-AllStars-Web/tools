<?php

namespace App\Services\AutoOrders;

use App\Models\modules\auto_orders\AutoOrdersCalculationLog;
use App\Models\modules\auto_orders\AutoOrdersCandidate;
use App\Models\modules\auto_orders\AutoOrdersPurchaseList;
use App\Models\prestashop\orders_details;
use Illuminate\Support\Facades\DB;

/**
 * Converts newly-imported sales into the supplier draft order.
 *
 * Existing rows in auto_orders_candidates are legacy/manual candidates and are
 * intentionally untouched, except when a new sale of the same reference has
 * been successfully calculated here.
 */
class AutoOrderReplenishmentService
{
    public function process(array $sales): void
    {
        foreach ($sales as $sale) {
            $this->processSale($sale);
        }
    }

    /**
     * Refresh the recommendation shown beside every automatic draft line.
     *
     * The draft is deliberately never reduced here: a purchaser may have chosen a
     * higher amount manually. It is only raised when the current recommendation
     * exceeds the quantity already present in the draft.
     */
    public function refreshAutomaticDrafts(): void
    {
        AutoOrdersPurchaseList::query()
            ->where('is_automatic', true)
            ->orderBy('id')
            ->eachById(function (AutoOrdersPurchaseList $draft): void {
                $product = AutoOrdersPurchaseList::getPrestashopProductInfo((string) $draft->reference);

                if ($product === null) {
                    return;
                }

                $salesLast12Months = (int) orders_details::getSoldByRefOf((string) $draft->reference);
                $physicalStock = (int) $product->quantity;
                $stockArrive = (int) $product->stock_arrive;
                $targetStock = (int) ceil($salesLast12Months / 2);
                $quantityCalculated = max(0, $targetStock - $physicalStock - $stockArrive);

                if ((int) $draft->quantity < $quantityCalculated) {
                    $draft->quantity = $quantityCalculated;
                }

                $draft->automatic_expected_quantity = $quantityCalculated;
                $draft->automatic_calculated_at = now();
                $draft->save();
            });
    }

    private function processSale(array $sale): void
    {
        $product = $this->currentProductData($sale);

        if ($product === null) {
            $this->log($sale, null, 0, 0, 0, 0, 0, 0, 'ignored', 'product_not_found');

            return;
        }

        $salesLast12Months = (int) orders_details::getSoldByIDOf(
            (int) $product->id_product,
            (int) $product->id_product_attribute
        );
        $physicalStock = (int) $product->physical_stock;
        $stockArrive = (int) $product->stock_arrive;
        $targetStock = (int) ceil($salesLast12Months / 2);
        $quantityCalculated = max(0, $targetStock - $physicalStock - $stockArrive);

        if ((int) $product->end_of_life === 1) {
            $this->log($sale, $product, $salesLast12Months, $physicalStock, $stockArrive, $targetStock, 0, 0, 'ignored', 'end_of_life');

            return;
        }

        if ((int) $product->not_to_order === 1) {
            $this->log($sale, $product, $salesLast12Months, $physicalStock, $stockArrive, $targetStock, 0, 0, 'ignored', 'not_to_order');

            return;
        }

        DB::transaction(function () use ($sale, $product, $salesLast12Months, $physicalStock, $stockArrive, $targetStock, $quantityCalculated) {
            $draft = AutoOrdersPurchaseList::query()
                ->where('id_supplier', (int) $product->id_supplier)
                ->where('reference', (string) $product->reference)
                ->lockForUpdate()
                ->first();

            $quantityBefore = $draft ? (int) $draft->quantity : 0;
            $quantityAfter = $quantityBefore;
            $action = 'no_order_required';
            $reason = null;

            if ($draft === null && $quantityCalculated > 0) {
                $draft = new AutoOrdersPurchaseList();
                $draft->id_supplier = (int) $product->id_supplier;
                $draft->supplier = (string) $product->supplier;
                $draft->reference = (string) $product->reference;
                $draft->name = (string) $product->name;
                $draft->quantity = $quantityCalculated;
                $draft->sold = $salesLast12Months;
                $draft->warranty = 0;
                $quantityAfter = $quantityCalculated;
                $action = 'created';
            } elseif ($draft !== null && $quantityBefore < $quantityCalculated) {
                $draft->quantity = $quantityCalculated;
                $quantityAfter = $quantityCalculated;
                $action = 'increased_to_calculated';
            } elseif ($draft !== null && $quantityBefore > $quantityCalculated) {
                // Never reduce a decision already present in the draft order.
                $action = 'kept_above_calculated';
                $reason = 'draft_quantity_above_calculated';
            } elseif ($draft !== null) {
                $action = 'kept_at_calculated';
            }

            if ($draft !== null) {
                $draft->is_automatic = true;
                $draft->automatic_expected_quantity = $quantityCalculated;
                $draft->automatic_calculated_at = now();
                $draft->save();
            }

            $this->removeMatchingLegacyCandidates($sale);
            $this->log(
                $sale,
                $product,
                $salesLast12Months,
                $physicalStock,
                $stockArrive,
                $targetStock,
                $quantityBefore,
                $quantityAfter,
                $action,
                $reason,
                $quantityCalculated
            );
        });
    }

    private function currentProductData(array $sale): ?object
    {
        $prefix = env('DB2_DB_prefix', 'ps_');
        $productId = (int) ($sale['id_product'] ?? 0);
        $attributeId = (int) ($sale['id_product_attribute'] ?? 0);
        $shopId = (int) ($sale['id_shop'] ?? 0);

        if ($productId <= 0) {
            return null;
        }

        return DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->leftJoin($prefix . 'product_attribute as pa', function ($join) use ($attributeId) {
                $join->on('pa.id_product', '=', 'p.id_product')
                    ->where('pa.id_product_attribute', $attributeId);
            })
            ->leftJoin($prefix . 'supplier as s', 's.id_supplier', '=', 'p.id_supplier')
            ->leftJoin($prefix . 'product_lang as pl', function ($join) use ($shopId) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', 1)
                    ->where('pl.id_shop', $shopId);
            })
            ->leftJoin($prefix . 'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->leftJoin($prefix . 'custom_product_attribute as cpa', function ($join) use ($attributeId) {
                $join->on('cpa.id_product_attribute', '=', 'pa.id_product_attribute')
                    ->on('cpa.id_product', '=', 'p.id_product');
            })
            ->leftJoin($prefix . 'stock_available as sa', function ($join) use ($attributeId, $shopId) {
                $join->on('sa.id_product', '=', 'p.id_product')
                    ->where('sa.id_product_attribute', $attributeId)
                    ->where('sa.id_shop', $shopId);
            })
            ->where('p.id_product', $productId)
            ->selectRaw('p.id_product')
            ->selectRaw('COALESCE(pa.id_product_attribute, 0) as id_product_attribute')
            ->selectRaw('COALESCE(p.id_supplier, 0) as id_supplier')
            ->selectRaw('COALESCE(s.name, "") as supplier')
            ->selectRaw('COALESCE(pa.reference, p.reference) as reference')
            ->selectRaw('COALESCE(pl.name, p.reference) as name')
            ->selectRaw('COALESCE(sa.quantity, 0) as physical_stock')
            ->selectRaw('CASE WHEN COALESCE(cpa.stock_arrive, 0) > 0 THEN cpa.stock_arrive ELSE COALESCE(cp.stock_arrive, 0) END as stock_arrive')
            ->selectRaw('COALESCE(cpa.wmdeprecated, cp.wmdeprecated, 0) as end_of_life')
            ->selectRaw('COALESCE(cp.not_to_order, 0) as not_to_order')
            ->first();
    }

    private function removeMatchingLegacyCandidates(array $sale): void
    {
        $reference = (string) ($sale['reference'] ?? '');
        $attributeReference = (string) ($sale['attr_reference'] ?? '');

        if ($reference === '') {
            return;
        }

        AutoOrdersCandidate::query()
            ->where('reference', $reference)
            ->where('ordered', 0)
            ->where(function ($query) use ($attributeReference) {
                if ($attributeReference !== '') {
                    $query->where('attr_reference', $attributeReference);
                } else {
                    $query->whereNull('attr_reference')->orWhere('attr_reference', '');
                }
            })
            ->delete();
    }

    private function log(
        array $sale,
        ?object $product,
        int $salesLast12Months,
        int $physicalStock,
        int $stockArrive,
        int $targetStock,
        int $quantityBefore,
        int $quantityAfter,
        string $action,
        ?string $reason = null,
        ?int $quantityCalculated = null
    ): void {
        AutoOrdersCalculationLog::create([
            'id_order' => isset($sale['id_order']) ? (int) $sale['id_order'] : null,
            'id_order_detail' => isset($sale['id_order_detail']) ? (int) $sale['id_order_detail'] : null,
            'id_product' => (int) ($product->id_product ?? $sale['id_product'] ?? 0),
            'id_product_attribute' => (int) ($product->id_product_attribute ?? $sale['id_product_attribute'] ?? 0),
            'id_supplier' => isset($product->id_supplier) ? (int) $product->id_supplier : null,
            'supplier' => $product->supplier ?? null,
            'reference' => (string) ($product->reference ?? $sale['attr_reference'] ?? $sale['reference'] ?? ''),
            'sales_last_12_months' => $salesLast12Months,
            'physical_stock' => $physicalStock,
            'stock_arrive' => $stockArrive,
            'target_stock' => $targetStock,
            'quantity_before' => $quantityBefore,
            'quantity_calculated' => $quantityCalculated ?? 0,
            'quantity_after' => $quantityAfter,
            'action' => $action,
            'reason' => $reason,
            'meta' => [
                'origin' => $sale['origin'] ?? null,
                'source' => 'automatic_replenishment',
            ],
        ]);
    }
}
