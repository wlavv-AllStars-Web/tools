<?php

namespace App\Services\oms;

use App\Models\modules\oms\BilledOrder;
use App\Models\modules\oms\BilledOrderLine;
use App\Models\modules\oms\SupplierInvoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reverts billed quantities and, when required, their receptions.  Every stock
 * change is retained in oms_stock_history as an OMS invoice reversal.
 */
class BilledOrderReversalService
{
    public function __construct(
        protected StockArriveService $stockArriveService,
        protected SupplierInvoiceWorkflowService $workflowService,
    ) {
    }

    public function revertLine(BilledOrderLine $line, int $quantity, bool $allowNegativeStock = false): array
    {
        return $this->revert([['line_id' => (int) $line->id, 'quantity' => $quantity]], $allowNegativeStock);
    }

    public function revertBilledOrder(BilledOrder $billedOrder, bool $allowNegativeStock = false): array
    {
        $lines = $billedOrder->lines()->get(['id', 'qty_billed']);

        return $this->revert($lines->map(fn ($line) => [
            'line_id' => (int) $line->id,
            'quantity' => (int) $line->qty_billed,
        ])->all(), $allowNegativeStock);
    }

    public function revertInvoice(SupplierInvoice $invoice, bool $allowNegativeStock = false): array
    {
        $lines = BilledOrderLine::query()
            ->whereIn('billed_order_id', BilledOrder::query()->where('supplier_invoice_id', $invoice->id)->select('id'))
            ->get(['id', 'qty_billed']);

        return $this->revert($lines->map(fn ($line) => [
            'line_id' => (int) $line->id,
            'quantity' => (int) $line->qty_billed,
        ])->all(), $allowNegativeStock);
    }

    private function revert(array $requestedLines, bool $allowNegativeStock): array
    {
        $requested = collect($requestedLines)
            ->mapWithKeys(fn ($row) => [(int) $row['line_id'] => (int) $row['quantity']])
            ->filter(fn ($quantity, $lineId) => $lineId > 0 && $quantity > 0);

        if ($requested->isEmpty()) {
            throw ValidationException::withMessages(['quantity' => 'Select at least one billed quantity to reverse.']);
        }

        $prefix = $this->psPrefix();
        DB::connection('mysql2')->beginTransaction();

        try {
            $result = DB::transaction(function () use ($requested, $allowNegativeStock, $prefix) {
                $lines = BilledOrderLine::query()
                    ->with(['billedOrder.orderNote', 'billedOrder.invoice'])
                    ->whereIn('id', $requested->keys()->all())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($lines->count() !== $requested->count()) {
                    throw ValidationException::withMessages(['line' => 'One or more billed lines no longer exist.']);
                }

                $operations = collect();
                foreach ($requested as $lineId => $quantity) {
                    $line = $lines->get($lineId);
                    $billed = (int) $line->qty_billed;
                    if ($quantity > $billed) {
                        throw ValidationException::withMessages(['quantity' => "Quantity to reverse cannot exceed the billed quantity ({$billed})."]);
                    }

                    $receivedFromLines = (int) DB::table('oms_reception_lines')
                        ->where('billed_order_line_id', $line->id)
                        ->sum('qty_received');
                    $received = max((int) ($line->qty_received ?? 0), $receivedFromLines);
                    $newBilled = $billed - $quantity;
                    $receivedToReverse = max(0, $received - $newBilled);

                    $operations->push(compact('line', 'billed', 'received', 'receivedFromLines', 'quantity', 'newBilled', 'receivedToReverse'));
                }

                $negative = $this->negativeStockTargets($operations, $prefix);
                if ($negative->isNotEmpty() && ! $allowNegativeStock) {
                    throw ValidationException::withMessages([
                        'negative_stock' => 'This reversal would make stock negative for: '.$negative->implode(', ').'. Confirm the negative stock reversal to continue.',
                    ]);
                }

                $user = Auth::user();
                $affectedOrderNotes = collect();
                $summary = ['lines' => 0, 'qty_billed_reversed' => 0, 'qty_received_reversed' => 0];

                foreach ($operations as $operation) {
                    /** @var BilledOrderLine $line */
                    $line = $operation['line'];
                    $billedOrder = $line->billedOrder;
                    $productId = (int) $line->product_id;
                    $attributeId = (int) ($line->product_attribute_id ?? 0);
                    $receivedToReverse = (int) $operation['receivedToReverse'];
                    $arriveDelta = -((int) $operation['quantity']) + $receivedToReverse;

                    $stockBefore = $this->primaryStock($productId, $attributeId, $prefix);
                    $arriveBefore = $this->primaryArrive($productId, $attributeId, $prefix);

                    if ($receivedToReverse > 0) {
                        $this->removeReceptionQuantities((int) $line->id, $receivedToReverse);
                        $this->adjustStockTargets($productId, $attributeId, -$receivedToReverse, $prefix);
                    }

                    if ($arriveDelta !== 0) {
                        $this->stockArriveService->adjust($productId, $attributeId, $arriveDelta);
                    }

                    $newBilled = (int) $operation['newBilled'];
                    if ($newBilled === 0) {
                        DB::table('oms_billed_order_lines')->where('id', $line->id)->delete();
                    } else {
                        DB::table('oms_billed_order_lines')->where('id', $line->id)->update([
                            'qty_billed' => $newBilled,
                            'qty_received' => max(0, (int) $operation['received'] - $receivedToReverse),
                            'updated_at' => now(),
                        ]);
                    }

                    $stockAfter = $this->primaryStock($productId, $attributeId, $prefix);
                    $arriveAfter = $this->primaryArrive($productId, $attributeId, $prefix);
                    $reference = $this->referenceSnapshot($productId, $attributeId, $prefix);

                    DB::table('oms_stock_history')->insert([
                        'source_type' => 'invoice_reversal',
                        'source_id' => (int) $line->id,
                        'order_note_id' => $billedOrder->order_note_id ?: null,
                        'billed_order_id' => (int) $billedOrder->id,
                        'supplier_invoice_id' => $billedOrder->supplier_invoice_id ?: null,
                        'reception_id' => null,
                        'product_id' => $productId,
                        'product_attribute_id' => $attributeId,
                        'product_reference_snapshot' => $reference['product'],
                        'attribute_reference_snapshot' => $reference['attribute'],
                        'display_reference_snapshot' => $reference['display'],
                        'ps_quantity_before' => $stockBefore,
                        'ps_quantity_delta' => -$receivedToReverse,
                        'ps_quantity_after' => $stockAfter,
                        'ps_quantity_arrive_before' => $arriveBefore,
                        'ps_quantity_arrive_delta' => $arriveDelta,
                        'ps_quantity_arrive_after' => $arriveAfter,
                        'user_id' => $user?->id,
                        'user_name_snapshot' => $user?->name ?: 'OMS',
                        'user_email_snapshot' => $user?->email,
                        'created_at' => now(),
                    ]);

                    $affectedOrderNotes->push((int) $billedOrder->order_note_id);
                    $summary['lines']++;
                    $summary['qty_billed_reversed'] += (int) $operation['quantity'];
                    $summary['qty_received_reversed'] += $receivedToReverse;
                }

                // Empty billed orders have no operational value after all their lines were reversed.
                $billedOrderIds = $operations->pluck('line.billed_order_id')->unique();
                foreach ($billedOrderIds as $billedOrderId) {
                    if (! DB::table('oms_billed_order_lines')->where('billed_order_id', $billedOrderId)->exists()) {
                        DB::table('oms_receptions')->where('billed_order_id', $billedOrderId)->delete();
                        DB::table('oms_billed_orders')->where('id', $billedOrderId)->delete();
                    }
                }

                $affectedOrderNotes->filter()->unique()->each(function ($orderNoteId) {
                    $orderNote = \App\Models\modules\oms\OrderNote::find($orderNoteId);
                    if ($orderNote) {
                        $this->workflowService->refreshOrderNoteStatus($orderNote->fresh(['lines', 'billedOrders']));
                    }
                });

                return $summary;
            });

            DB::connection('mysql2')->commit();
            return $result;
        } catch (\Throwable $exception) {
            if (DB::connection('mysql2')->transactionLevel() > 0) {
                DB::connection('mysql2')->rollBack();
            }
            throw $exception;
        }
    }

    private function removeReceptionQuantities(int $lineId, int $quantity): void
    {
        $remaining = $quantity;
        $rows = DB::table('oms_reception_lines')
            ->where('billed_order_line_id', $lineId)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->get();

        foreach ($rows as $row) {
            if ($remaining <= 0) {
                break;
            }
            $taken = min($remaining, (int) $row->qty_received);
            $left = (int) $row->qty_received - $taken;
            if ($left === 0) {
                DB::table('oms_reception_lines')->where('id', $row->id)->delete();
            } else {
                DB::table('oms_reception_lines')->where('id', $row->id)->update(['qty_received' => $left, 'updated_at' => now()]);
            }
            $remaining -= $taken;
        }

        // Older OMS entries may contain the received quantity on the billed line
        // without a reception-line record. The billed-line quantity is still the
        // source of truth for the stock reversal; only the available reception
        // rows can be removed in that legacy case.

        DB::table('oms_receptions')->whereNotExists(function ($query) {
            $query->select(DB::raw(1))->from('oms_reception_lines')->whereColumn('oms_reception_lines.reception_id', 'oms_receptions.id');
        })->delete();
    }

    private function negativeStockTargets(Collection $operations, string $prefix): Collection
    {
        $targets = collect();

        foreach ($operations as $operation) {
            $quantity = (int) $operation['receivedToReverse'];
            if ($quantity <= 0) {
                continue;
            }

            $line = $operation['line'];
            $label = $this->referenceSnapshot((int) $line->product_id, (int) ($line->product_attribute_id ?? 0), $prefix)['display'] ?: ('Product #'.$line->product_id);
            foreach ($this->stockTargets((int) $line->product_id, (int) ($line->product_attribute_id ?? 0), $prefix) as $target) {
                $key = (int) $target->id_product.':'.(int) $target->id_product_attribute;
                $current = $targets->get($key, ['quantity' => (int) $target->quantity, 'delta' => 0, 'labels' => collect()]);
                $current['delta'] += $quantity;
                $current['labels']->push($label);
                $targets->put($key, $current);
            }
        }

        return $targets
            ->filter(fn ($target) => $target['quantity'] - $target['delta'] < 0)
            ->flatMap(fn ($target) => $target['labels'])
            ->unique()
            ->values();
    }
    private function adjustStockTargets(int $productId, int $attributeId, int $delta, string $prefix): void
    {
        $this->stockTargets($productId, $attributeId, $prefix)->each(function ($target) use ($delta, $prefix) {
            DB::connection('mysql2')->table($prefix.'stock_available')
                ->where('id_product', $target->id_product)
                ->where('id_product_attribute', $target->id_product_attribute)
                ->update(['quantity' => DB::raw('quantity + ('.((int) $delta).')')]);
        });
    }

    private function stockTargets(int $productId, int $attributeId, string $prefix): Collection
    {
        $table = $attributeId > 0 ? $prefix.'product_attribute' : $prefix.'product';
        $key = $attributeId > 0 ? 'id_product_attribute' : 'id_product';
        $reference = trim((string) DB::connection('mysql2')->table($table)->where($key, $attributeId ?: $productId)->value('reference'));
        $query = DB::connection('mysql2')->table($prefix.'stock_available as sa');
        if ($reference === '') {
            return $query->where('sa.id_product', $productId)->where('sa.id_product_attribute', $attributeId)->get(['sa.id_product', 'sa.id_product_attribute', 'sa.quantity']);
        }
        if ($attributeId > 0) {
            return $query->join($prefix.'product_attribute as pa', 'pa.id_product_attribute', '=', 'sa.id_product_attribute')->where('pa.reference', $reference)->get(['sa.id_product', 'sa.id_product_attribute', 'sa.quantity']);
        }
        return $query->join($prefix.'product as p', 'p.id_product', '=', 'sa.id_product')->where('p.reference', $reference)->where('sa.id_product_attribute', 0)->get(['sa.id_product', 'sa.id_product_attribute', 'sa.quantity']);
    }

    private function primaryStock(int $productId, int $attributeId, string $prefix): int
    {
        return (int) DB::connection('mysql2')->table($prefix.'stock_available')->where('id_product', $productId)->where('id_product_attribute', $attributeId)->value('quantity');
    }

    private function primaryArrive(int $productId, int $attributeId, string $prefix): int
    {
        $table = $attributeId > 0 ? $prefix.'custom_product_attribute' : $prefix.'custom_product';
        $key = $attributeId > 0 ? 'id_product_attribute' : 'id_product';
        return (int) DB::connection('mysql2')->table($table)->where($key, $attributeId ?: $productId)->value('stock_arrive');
    }

    private function referenceSnapshot(int $productId, int $attributeId, string $prefix): array
    {
        $product = trim((string) DB::connection('mysql2')->table($prefix.'product')->where('id_product', $productId)->value('reference'));
        $attribute = $attributeId > 0 ? trim((string) DB::connection('mysql2')->table($prefix.'product_attribute')->where('id_product_attribute', $attributeId)->value('reference')) : '';
        return ['product' => $product ?: null, 'attribute' => $attribute ?: null, 'display' => $attribute ?: ($product ?: null)];
    }

    private function psPrefix(): string
    {
        return (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');
    }
}
