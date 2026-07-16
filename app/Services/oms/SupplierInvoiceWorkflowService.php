<?php

namespace App\Services\oms;

use App\Models\modules\oms\BilledOrder;
use App\Models\modules\oms\BilledOrderLine;
use App\Models\modules\oms\OrderNote;
use App\Models\modules\oms\OrderNoteLine;
use App\Models\modules\oms\SupplierInvoice;
use App\Models\prestashop\currency;
use App\Models\prestashop\custom_manufacturer;
use App\Models\prestashop\product;
use App\Models\prestashop\product_shop;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierInvoiceWorkflowService
{
    private ?bool $customAttributeHasProductId = null;

    public function getDraftInvoicesForSupplier(int $supplierId): Collection
    {
        return SupplierInvoice::query()
            ->withCount('billedOrders')
            ->where('supplier_id', $supplierId)
            ->where('status', 'draft')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getInvoiceableLines(OrderNote $orderNote): Collection
    {
        $orderNote->loadMissing('lines');

        $prefix = $this->psPrefix();
        $lineMap = $orderNote->lines->keyBy('id');
        $productIds = $lineMap->pluck('product_id')->filter()->unique()->values()->all();
        $attributeIds = $lineMap->pluck('product_attribute_id')->filter()->unique()->values()->all();

        $products = collect();

        if (!empty($productIds)) {
            $products = DB::connection('mysql2')
                ->table($prefix . 'product as p')
                ->leftJoin($prefix . 'product_lang as pl', function ($join) {
                    $join->on('pl.id_product', '=', 'p.id_product')
                        ->where('pl.id_lang', 1);
                })
                ->leftJoin($prefix . 'stock_available as sa', function ($join) {
                    $join->on('sa.id_product', '=', 'p.id_product')
                        ->where('sa.id_product_attribute', 0);
                })
                ->leftJoin($prefix . 'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
                ->whereIn('p.id_product', $productIds)
                ->select([
                    'p.id_product',
                    'p.reference as product_reference',
                    'p.ean13 as product_ean13',
                    'p.id_manufacturer',
                    'p.wholesale_price',
                    'p.price as product_sale_price',
                    'pl.name as product_name',
                    'sa.quantity as current_stock',
                    'cp.wholesale_price_base_currency as custom_wholesale_price_base_currency',
                    'cp.price_base_currency as custom_price_base_currency',
                    'cp.price_display_base_currency as custom_price_display_base_currency',
                ])
                ->get()
                ->keyBy('id_product');
        }

        $attributes = collect();

        if (!empty($attributeIds)) {
            $attributes = DB::connection('mysql2')
                ->table($prefix . 'product_attribute as pa')
                ->leftJoin($prefix . 'custom_product_attribute as cpa', 'cpa.id_product_attribute', '=', 'pa.id_product_attribute')
                ->whereIn('pa.id_product_attribute', $attributeIds)
                ->select([
                    'pa.id_product_attribute',
                    'pa.id_product',
                    'pa.reference as attribute_reference',
                    'pa.ean13 as attribute_ean13',
                    'pa.wholesale_price as attribute_wholesale_price',
                    'pa.price as attribute_price_impact',
                    'cpa.wholesale_price_base_currency as custom_attribute_wholesale_price_base_currency',
                    'cpa.price_base_currency as custom_attribute_price_base_currency',
                    'cpa.price_display_base_currency as custom_attribute_price_display_base_currency',
                ])
                ->get()
                ->keyBy('id_product_attribute');
        }

        return $orderNote->lines
            ->sortBy('id')
            ->map(function (OrderNoteLine $line) use ($products, $attributes) {
                $product = $products->get($line->product_id);
                $attribute = $line->product_attribute_id
                    ? $attributes->get($line->product_attribute_id)
                    : null;

                $productReference = $product->product_reference ?? null;
                $attributeReference = $attribute->attribute_reference ?? null;
                $reference = $attributeReference ?: $productReference;
                $ean13 = $attribute->attribute_ean13 ?? $product->product_ean13 ?? null;
                $currentStock = (int) ($product->current_stock ?? 0);
                $qtyBilled = (int) $line->qty_billed_total;
                $remaining = max(0, (int) $line->qty_ordered - $qtyBilled);
                $isAttribute = !empty($line->product_attribute_id);
                $manufacturerId = (int) ($product->id_manufacturer ?? 0);

                /*
                |--------------------------------------------------------------------------
                | Current purchase prices
                |--------------------------------------------------------------------------
                | ps_product / ps_product_attribute = EUR
                | ps_custom_product / ps_custom_product_attribute = supplier currency
                */
                $currentPurchaseSupplierCurrency = $isAttribute
                    ? (float) ($attribute->custom_attribute_wholesale_price_base_currency ?? 0)
                    : (float) ($product->custom_wholesale_price_base_currency ?? 0);

                $currentPurchaseEur = (float) (
                    $attribute->attribute_wholesale_price
                    ?? $product->wholesale_price
                    ?? 0
                );

                /*
                |--------------------------------------------------------------------------
                | Current sale prices
                |--------------------------------------------------------------------------
                | Sale EUR comes from PrestaShop core.
                | Sale supplier currency comes from ps_custom_*.
                */
                $currentSaleSupplierCurrency = $isAttribute
                    ? (float) ($attribute->custom_attribute_price_base_currency ?? 0)
                    : (float) ($product->custom_price_base_currency ?? 0);

                $currentSaleEur = (float) (
                    (($product->product_sale_price ?? 0) + ($attribute->attribute_price_impact ?? 0))
                );

                return (object) [
                    'order_note_line_id' => (int) $line->id,
                    'product_id' => (int) $line->product_id,
                    'product_attribute_id' => $line->product_attribute_id ? (int) $line->product_attribute_id : null,
                    'reference' => $reference,
                    'product_reference_snapshot' => $productReference,
                    'attribute_reference_snapshot' => $attributeReference,
                    'display_reference_snapshot' => $reference,
                    'product_name' => $product->product_name ?? ('Product #' . $line->product_id),
                    'ean13' => $ean13,
                    'qty_ordered' => (int) $line->qty_ordered,
                    'qty_billed' => $qtyBilled,
                    'qty_remaining' => $remaining,
                    'current_stock' => $currentStock,
                    'manufacturer_id' => $manufacturerId,
                    'is_attribute' => $isAttribute,
                    'current_purchase_supplier_currency' => $currentPurchaseSupplierCurrency,
                    'current_purchase_eur' => $currentPurchaseEur,
                    'current_sale_supplier_currency' => $currentSaleSupplierCurrency,
                    'current_sale_eur' => $currentSaleEur,
                    
                    // Compatibilidade com a view atual.
                    // A view usa este campo para preencher o input "Purchase Unit Price (Supplier Currency)".
                    'current_wholesale_price' => $currentPurchaseSupplierCurrency,
                    'reference_copy' => $reference,
                ];
            })
            ->values();
    }

    public function resolveCurrencyForOrderNote(OrderNote $orderNote, ?Collection $selectedLines = null): array
    {
        $productIds = ($selectedLines ?: $orderNote->lines)
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $manufacturerId = 0;

        if (!empty($productIds)) {
            $manufacturerId = (int) product::query()
                ->whereIn('id_product', $productIds)
                ->orderBy('id_product')
                ->value('id_manufacturer');
        }

        $currencyId = 2;
        $currencyIso = 'EUR';
        $purchaseConversionRate = 1.0;
        $saleConversionRate = 1.0;

        if ($manufacturerId > 0) {
            $customManufacturer = custom_manufacturer::query()->find($manufacturerId);

            if ($customManufacturer && (int) $customManufacturer->id_currency > 0) {
                $currency = currency::query()->find((int) $customManufacturer->id_currency);

                if ($currency) {
                    $currencyId = (int) $currency->id_currency;
                    $currencyIso = strtoupper((string) $currency->iso_code);
                    $purchaseConversionRate = $this->resolvePurchaseConversionRate($currencyIso);
                    $saleConversionRate = $this->resolveSaleConversionRate($currencyIso, $purchaseConversionRate);
                }
            }
        }

        return [
            'manufacturer_id' => $manufacturerId,
            'currency_id' => $currencyId,
            'currency_iso' => $currencyIso,

            /*
             * Legacy key kept for compatibility.
             * It always represents the purchase conversion rate.
             */
            'conversion_rate' => $purchaseConversionRate,

            'purchase_conversion_rate' => $purchaseConversionRate,
            'sale_conversion_rate' => $saleConversionRate,
            'is_eur' => strtoupper($currencyIso) === 'EUR' || $currencyId === 2,
        ];
    }

    public function confirmInvoiceForOrderNote(OrderNote $orderNote, array $payload): SupplierInvoice
    {
        $invoiceableLines = $this->getInvoiceableLines($orderNote)->keyBy('order_note_line_id');

        $requestedLines = collect($payload['lines'] ?? [])
            ->filter(function ($line) {
                return (int) ($line['qty_billed'] ?? 0) > 0;
            })
            ->values();

        if ($requestedLines->isEmpty()) {
            throw ValidationException::withMessages([
                'lines' => 'Select at least one line and billed quantity.',
            ]);
        }

        $skippedInvalidPriceLines = collect();
        $selectedLines = collect();

        foreach ($requestedLines as $linePayload) {
            $orderNoteLineId = (int) ($linePayload['order_note_line_id'] ?? 0);
            $qtyBilled = (int) ($linePayload['qty_billed'] ?? 0);
            $unitPrice = (float) ($linePayload['unit_price'] ?? 0);
            $line = $invoiceableLines->get($orderNoteLineId);

            if (!$line) {
                throw ValidationException::withMessages([
                    'lines' => 'One or more selected lines are invalid.',
                ]);
            }

            if ($qtyBilled <= 0 || $qtyBilled > (int) $line->qty_remaining) {
                throw ValidationException::withMessages([
                    'lines' => 'Billed quantity exceeds the remaining quantity on at least one line.',
                ]);
            }

            if ($unitPrice <= 0) {
                $skippedInvalidPriceLines->push([
                    'order_note_line_id' => $orderNoteLineId,
                    'reference' => (string) ($line->reference ?: ('Product #' . $line->product_id)),
                    'qty_billed' => $qtyBilled,
                    'unit_price' => $unitPrice,
                ]);

                continue;
            }

            $selectedLines->push($linePayload);
        }

        if ($selectedLines->isEmpty()) {
            $references = $skippedInvalidPriceLines->pluck('reference')->filter()->implode(', ');

            throw ValidationException::withMessages([
                'lines' => 'No product was marked as invoiced because the selected purchase price was invalid (must be greater than zero): ' . $references,
            ]);
        }

        $currencyMeta = $this->resolveCurrencyForOrderNote(
            $orderNote,
            $selectedLines->map(function ($line) use ($invoiceableLines) {
                return (object) [
                    'product_id' => $invoiceableLines[(int) $line['order_note_line_id']]->product_id,
                ];
            })
        );

        return DB::transaction(function () use ($orderNote, $payload, $selectedLines, $invoiceableLines, $currencyMeta, $skippedInvalidPriceLines) {
            $invoice = null;
            $existingInvoiceId = (int) ($payload['existing_invoice_id'] ?? 0);
            $invoiceReference = trim((string) ($payload['invoice_reference'] ?? ''));

            if ($existingInvoiceId > 0) {
                $invoice = SupplierInvoice::query()
                    ->where('supplier_id', (int) $orderNote->supplier_id)
                    ->where('status', 'draft')
                    ->findOrFail($existingInvoiceId);
            }

            if (!$invoice && $invoiceReference !== '') {
                $invoice = SupplierInvoice::query()
                    ->where('supplier_id', (int) $orderNote->supplier_id)
                    ->where('status', 'draft')
                    ->where('invoice_reference', $invoiceReference)
                    ->first();
            }

            if (!$invoice) {
                $invoice = SupplierInvoice::create([
                    'supplier_id' => (int) $orderNote->supplier_id,
                    'invoice_reference' => $invoiceReference !== '' ? $invoiceReference : ('INV-' . now()->format('YmdHis')),
                    'invoice_date' => $payload['invoice_date'] ?? now()->toDateString(),
                    'due_date' => $payload['due_date'] ?? null,
                    'currency_id' => (int) $currencyMeta['currency_id'],
                    'currency_iso' => (string) $currencyMeta['currency_iso'],
                    'conversion_rate' => (float) $currencyMeta['purchase_conversion_rate'],
                    'status' => 'draft',
                    'internal_note' => $payload['internal_note'] ?? null,
                    'logistic_note' => $payload['logistic_note'] ?? null,
                ]);
            } else {
                $invoice->fill([
                    'invoice_date' => $payload['invoice_date'] ?? $invoice->invoice_date,
                    'due_date' => $payload['due_date'] ?? $invoice->due_date,
                    'internal_note' => $payload['internal_note'] ?? $invoice->internal_note,
                    'logistic_note' => $payload['logistic_note'] ?? $invoice->logistic_note,
                ])->save();
            }

            $billedOrder = BilledOrder::create([
                'order_note_id' => (int) $orderNote->id,
                'supplier_invoice_id' => (int) $invoice->id,
                'reference' => 'BO-' . $orderNote->reference . '-' . now()->format('His'),
                'status' => 'billed',
                'internal_note' => null,
                'logistic_note' => null,
            ]);

            $userSnapshot = $this->getUserSnapshot();
            $purchaseConversionRate = (float) ($currencyMeta['purchase_conversion_rate'] ?? 1.0);
            $saleConversionRate = (float) ($currencyMeta['sale_conversion_rate'] ?? 1.0);
            $isEur = (bool) ($currencyMeta['is_eur'] ?? false);

            foreach ($selectedLines as $linePayload) {
                $orderNoteLineId = (int) $linePayload['order_note_line_id'];
                $invoiceable = $invoiceableLines->get($orderNoteLineId);
                $qtyBilled = (int) $linePayload['qty_billed'];

                /*
                |--------------------------------------------------------------------------
                | Purchase price from invoice
                |--------------------------------------------------------------------------
                | Invoice unit price is in supplier currency.
                */
                $unitPriceInvoice = round((float) $linePayload['unit_price'], 6);

                $unitPriceEur = $isEur
                    ? $unitPriceInvoice
                    : round($unitPriceInvoice * $purchaseConversionRate, 6);

                $oldPurchaseSupplierCurrency = round((float) ($invoiceable->current_purchase_supplier_currency ?? 0), 6);
                $oldPurchaseEur = round((float) ($invoiceable->current_purchase_eur ?? 0), 6);
                $oldSaleSupplierCurrency = round((float) ($invoiceable->current_sale_supplier_currency ?? 0), 6);
                $oldSaleEur = round((float) ($invoiceable->current_sale_eur ?? 0), 6);

                $newPurchaseSupplierCurrency = $unitPriceInvoice;
                $newPurchaseEur = $unitPriceEur;

                /*
                |--------------------------------------------------------------------------
                | Sale price recalculation
                |--------------------------------------------------------------------------
                | If purchase cost changes, sale price follows the same percentage.
                | ps_product / ps_product_attribute = EUR.
                | ps_custom_product / ps_custom_product_attribute = supplier currency.
                */
                $costVariationFactor = $oldPurchaseEur > 0
                    ? ($newPurchaseEur / $oldPurchaseEur)
                    : 1.0;

                $newSaleEur = round($oldSaleEur * $costVariationFactor, 6);

                $newSaleSupplierCurrency = $isEur
                    ? $newSaleEur
                    : round($newSaleEur / $saleConversionRate, 6);

                $billedOrderLine = BilledOrderLine::create([
                    'billed_order_id' => (int) $billedOrder->id,
                    'order_note_line_id' => (int) $orderNoteLineId,
                    'product_id' => (int) $invoiceable->product_id,
                    'product_attribute_id' => $invoiceable->product_attribute_id,
                    'qty_billed' => $qtyBilled,
                    'qty_received' => 0,
                    'unit_price_invoice_currency' => $unitPriceInvoice,
                    'unit_price_eur' => $unitPriceEur,
                    'currency_id' => (int) $currencyMeta['currency_id'],
                    'currency_iso' => (string) $currencyMeta['currency_iso'],
                    'conversion_rate_used' => $purchaseConversionRate,
                ]);

                DB::table('oms_document_line_history')->insert([
                    'context_type' => 'billed_order_line',
                    'context_id' => (int) $billedOrderLine->id,
                    'order_note_id' => (int) $orderNote->id,
                    'billed_order_id' => (int) $billedOrder->id,
                    'supplier_invoice_id' => (int) $invoice->id,
                    'reception_id' => null,
                    'product_id' => (int) $invoiceable->product_id,
                    'product_attribute_id' => (int) ($invoiceable->product_attribute_id ?? 0),
                    'product_reference_snapshot' => $invoiceable->product_reference_snapshot ?? null,
                    'attribute_reference_snapshot' => $invoiceable->attribute_reference_snapshot ?? null,
                    'display_reference_snapshot' => $invoiceable->display_reference_snapshot ?? $invoiceable->reference ?? null,
                    'invoice_currency_id' => (int) $currencyMeta['currency_id'],
                    'invoice_currency_iso' => (string) $currencyMeta['currency_iso'],
                    'conversion_rate_used' => $purchaseConversionRate,
                    'purchase_conversion_rate_used' => $purchaseConversionRate,
                    'sale_conversion_rate_used' => $saleConversionRate,
                    'unit_price_invoice_currency' => $unitPriceInvoice,
                    'unit_price_eur' => $unitPriceEur,
                    'qty' => $qtyBilled,
                    'old_purchase_supplier_currency' => $oldPurchaseSupplierCurrency,
                    'new_purchase_supplier_currency' => $newPurchaseSupplierCurrency,
                    'old_purchase_eur' => $oldPurchaseEur,
                    'new_purchase_eur' => $newPurchaseEur,
                    'old_sale_supplier_currency' => $oldSaleSupplierCurrency,
                    'new_sale_supplier_currency' => $newSaleSupplierCurrency,
                    'old_sale_eur' => $oldSaleEur,
                    'new_sale_eur' => $newSaleEur,
                    'old_wholesale_price_eur' => $oldPurchaseEur,
                    'new_wholesale_price_eur' => $newPurchaseEur,
                    'user_id' => $userSnapshot['user_id'],
                    'user_name_snapshot' => $userSnapshot['user_name_snapshot'],
                    'user_email_snapshot' => $userSnapshot['user_email_snapshot'],
                    'created_at' => now(),
                ]);

                $this->updatePrestashopPrices(
                    (int) $invoiceable->product_id,
                    $invoiceable->product_attribute_id ? (int) $invoiceable->product_attribute_id : null,
                    $newPurchaseSupplierCurrency,
                    $newPurchaseEur,
                    $newSaleSupplierCurrency,
                    $newSaleEur
                );
            }

            $this->refreshOrderNoteStatus($orderNote->fresh(['lines', 'billedOrders']));

            $result = $invoice->fresh(['billedOrders.lines', 'supplier']);
            $result->setAttribute('skipped_invalid_price_lines', $skippedInvalidPriceLines->all());

            return $result;
        });
    }

    public function cancelInvoice(SupplierInvoice $invoice): SupplierInvoice
    {
        return DB::transaction(function () use ($invoice) {
            $invoice->status = 'cancelled';
            $invoice->save();

            $orderNotes = OrderNote::query()
                ->whereIn('id', $invoice->billedOrders()->pluck('order_note_id')->filter()->unique()->values())
                ->get();

            foreach ($orderNotes as $orderNote) {
                $this->refreshOrderNoteStatus($orderNote->fresh(['lines', 'billedOrders']));
            }

            return $invoice->fresh();
        });
    }

    public function refreshOrderNoteStatus(OrderNote $orderNote): void
    {
        $orderNote->loadMissing('lines', 'billedOrders');

        if ($orderNote->lines->isEmpty()) {
            $orderNote->status = 'order_note';
            $orderNote->save();
            return;
        }

        $allBilled = $orderNote->lines->every(function (OrderNoteLine $line) {
            return (int) ($line->qty_billed_total ?? 0) >= (int) $line->qty_ordered;
        });

        $allReceived = $orderNote->lines->every(function (OrderNoteLine $line) {
            return (int) ($line->qty_received_total ?? 0) >= (int) $line->qty_ordered;
        });

        if (!$allBilled) {
            $orderNote->status = 'order_note';
        } elseif ($allReceived) {
            $orderNote->status = 'closed';
        } else {
            $orderNote->status = 'billed';
        }

        $orderNote->save();
    }

    protected function updatePrestashopPrices(
        int $productId,
        ?int $productAttributeId,
        float $purchaseSupplierCurrency,
        float $purchaseEur,
        float $saleSupplierCurrency,
        float $saleEur
    ): void {
        if (!$this->prestashopProductExists($productId)) {
            throw new \RuntimeException('Cannot update PrestaShop prices. Invalid id_product: ' . $productId);
        }

        /*
        |--------------------------------------------------------------------------
        | Base product - PrestaShop EUR
        |--------------------------------------------------------------------------
        */
        product::query()
            ->where('id_product', $productId)
            ->update([
                'wholesale_price' => $purchaseEur,
                'price' => $saleEur,
            ]);

        product_shop::query()
            ->where('id_product', $productId)
            ->update([
                'wholesale_price' => $purchaseEur,
                'price' => $saleEur,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Base product - custom supplier currency
        |--------------------------------------------------------------------------
        */
        $this->ensureCustomProductRow($productId);

        DB::connection('mysql2')
            ->table($this->psPrefix() . 'custom_product')
            ->where('id_product', $productId)
            ->update([
                'wholesale_price_base_currency' => $purchaseSupplierCurrency,
                'price_base_currency' => $saleSupplierCurrency,
                'price_display_base_currency' => $saleSupplierCurrency,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Attribute - PrestaShop EUR + supplier currency
        |--------------------------------------------------------------------------
        */
        if ($productAttributeId && $productAttributeId > 0) {
            if (!$this->prestashopProductAttributeExists($productId, $productAttributeId)) {
                throw new \RuntimeException('Cannot update PrestaShop attribute prices. Invalid id_product_attribute: ' . $productAttributeId);
            }

            DB::connection('mysql2')
                ->table($this->psPrefix() . 'product_attribute')
                ->where('id_product', $productId)
                ->where('id_product_attribute', $productAttributeId)
                ->update([
                    'wholesale_price' => $purchaseEur,

                    /*
                     * Keep the attribute sale price impact neutral.
                     * Base sale price is updated on ps_product / ps_product_shop.
                     */
                    'price' => 0,
                ]);

            $this->ensureCustomProductAttributeRow($productId, $productAttributeId);

            DB::connection('mysql2')
                ->table($this->psPrefix() . 'custom_product_attribute')
                ->where('id_product_attribute', $productAttributeId)
                ->update([
                    'id_product' => $productId,
                    'wholesale_price_base_currency' => $purchaseSupplierCurrency,
                    'price_base_currency' => $saleSupplierCurrency,
                    'price_display_base_currency' => $saleSupplierCurrency,
                ]);
        }
    }

    protected function ensureCustomProductRow(int $productId): void
    {
        DB::connection('mysql2')
            ->table($this->psPrefix() . 'custom_product')
            ->updateOrInsert(
                ['id_product' => $productId],
                ['id_product' => $productId]
            );
    }

    protected function ensureCustomProductAttributeRow(int $productId, int $productAttributeId): void
    {
        $payload = [
            'id_product_attribute' => $productAttributeId,
        ];

        if ($this->customAttributeHasProductId()) {
            $payload['id_product'] = $productId;
        }

        DB::connection('mysql2')
            ->table($this->psPrefix() . 'custom_product_attribute')
            ->updateOrInsert(
                ['id_product_attribute' => $productAttributeId],
                $payload
            );
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

    protected function prestashopProductExists(int $productId): bool
    {
        return DB::connection('mysql2')
            ->table($this->psPrefix() . 'product')
            ->where('id_product', $productId)
            ->exists();
    }

    protected function prestashopProductAttributeExists(int $productId, int $productAttributeId): bool
    {
        return DB::connection('mysql2')
            ->table($this->psPrefix() . 'product_attribute')
            ->where('id_product', $productId)
            ->where('id_product_attribute', $productAttributeId)
            ->exists();
    }

    protected function psPrefix(): string
    {
        return (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');
    }

    protected function resolvePurchaseConversionRate(?string $currencyIso): float
    {
        $currencyIso = strtolower(trim((string) $currencyIso));

        if ($currencyIso === '' || $currencyIso === 'eur') {
            return 1.0;
        }

        $rate = currency::query()
            ->whereRaw('LOWER(iso_code) = ?', [$currencyIso])
            ->value('conversion_rate');

        return $rate !== null ? (float) $rate : 1.0;
    }

    protected function resolveSaleConversionRate(string $purchaseCurrencyIso, float $fallbackRate): float
    {
        $purchaseCurrencyIso = strtolower(trim($purchaseCurrencyIso));

        $saleIsoMap = [
            'usd' => 'uss',
            'gbp' => 'gbs',
            'jpy' => 'jps',
        ];

        $saleIso = $saleIsoMap[$purchaseCurrencyIso] ?? $purchaseCurrencyIso;

        if ($saleIso === '' || $saleIso === 'eur') {
            return 1.0;
        }

        $saleRate = currency::query()
            ->whereRaw('LOWER(iso_code) = ?', [$saleIso])
            ->value('conversion_rate');

        return $saleRate !== null ? (float) $saleRate : $fallbackRate;
    }

    protected function getUserSnapshot(): array
    {
        $user = Auth::user();

        return [
            'user_id' => $user?->id,
            'user_name_snapshot' => $user?->name,
            'user_email_snapshot' => $user?->email,
        ];
    }

    public function closeInvoice(SupplierInvoice $invoice): void
    {
        if ($invoice->status !== 'draft') {
            return;
        }

        $invoice->status = 'confirmed';
        $invoice->save();
    }
}
