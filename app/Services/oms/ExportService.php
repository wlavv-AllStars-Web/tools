<?php

namespace App\Services\oms;

use App\Models\modules\oms\BilledOrder;
use App\Models\modules\oms\OrderNote;
use App\Models\modules\oms\SupplierInvoice;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    public function streamCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $headers, ';');

            foreach ($rows as $row) {
                fputcsv($handle, $this->normalizeRow($row, $headers), ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportOrderNoteCsv(OrderNote $orderNote): StreamedResponse
    {
        $headers = [
            'order_note_reference',
            'supplier_id',
            'created_at',
            'internal_note',
            'logistic_note',
            'product_id',
            'product_attribute_id',
            'product_reference',
            'attribute_reference',
            'supplier_reference',
            'ean13',
            'product_name',
            'qty_ordered',
            'qty_billed_total',
            'qty_received_total',
            'remaining_to_bill',
        ];

        $rows = $orderNote->lines->map(function ($line) use ($orderNote) {
            $meta = $this->resolveProductMeta(
                (int) ($line->product_id ?? 0),
                !empty($line->product_attribute_id) ? (int) $line->product_attribute_id : null
            );

            return [
                'order_note_reference' => $orderNote->reference,
                'supplier_id' => $orderNote->supplier_id,
                'created_at' => optional($orderNote->created_at)?->format('Y-m-d H:i:s'),
                'internal_note' => $orderNote->internal_note,
                'logistic_note' => $orderNote->logistic_note,
                'product_id' => $line->product_id ?? null,
                'product_attribute_id' => $line->product_attribute_id ?? null,
                'product_reference' => $meta['product_reference'],
                'attribute_reference' => $meta['attribute_reference'],
                'supplier_reference' => $meta['supplier_reference'],
                'ean13' => $meta['ean13'],
                'product_name' => $meta['product_name'],
                'qty_ordered' => $line->qty_ordered ?? 0,
                'qty_billed_total' => $line->qty_billed_total ?? 0,
                'qty_received_total' => $line->qty_received_total ?? 0,
                'remaining_to_bill' => $line->remaining_to_bill ?? 0,
            ];
        });

        return $this->streamCsv('order-note-' . $orderNote->id . '.csv', $headers, $rows);
    }

    public function exportBilledOrderCsv(BilledOrder $billedOrder): StreamedResponse
    {
        $headers = [
            'billed_order_reference',
            'invoice_reference',
            'order_note_reference',
            'created_at',
            'internal_note',
            'logistic_note',
            'product_id',
            'product_attribute_id',
            'product_reference',
            'attribute_reference',
            'supplier_reference',
            'ean13',
            'product_name',
            'qty_billed',
            'qty_received',
            'qty_missing_to_receive',
        ];

        $rows = $billedOrder->lines->map(function ($line) use ($billedOrder) {
            $meta = $this->resolveProductMeta(
                (int) ($line->product_id ?? 0),
                !empty($line->product_attribute_id) ? (int) $line->product_attribute_id : null
            );

            return [
                'billed_order_reference' => $billedOrder->reference,
                'invoice_reference' => optional($billedOrder->invoice)->invoice_reference,
                'order_note_reference' => optional($billedOrder->orderNote)->reference,
                'created_at' => optional($billedOrder->created_at)?->format('Y-m-d H:i:s'),
                'internal_note' => $billedOrder->internal_note,
                'logistic_note' => $billedOrder->logistic_note,
                'product_id' => $line->product_id ?? null,
                'product_attribute_id' => $line->product_attribute_id ?? null,
                'product_reference' => $meta['product_reference'],
                'attribute_reference' => $meta['attribute_reference'],
                'supplier_reference' => $meta['supplier_reference'],
                'ean13' => $meta['ean13'],
                'product_name' => $meta['product_name'],
                'qty_billed' => $line->qty_billed ?? 0,
                'qty_received' => $line->qty_received ?? 0,
                'qty_missing_to_receive' => $line->qty_missing_to_receive ?? max(0, (int) ($line->qty_billed ?? 0) - (int) ($line->qty_received ?? 0)),
            ];
        });

        return $this->streamCsv('billed-order-' . $billedOrder->id . '.csv', $headers, $rows);
    }

    public function exportInvoiceCsv(SupplierInvoice $invoice): StreamedResponse
    {
        $headers = [
            'invoice_reference',
            'invoice_date',
            'supplier_id',
            'billed_order_reference',
            'order_note_reference',
            'product_id',
            'product_attribute_id',
            'product_reference',
            'attribute_reference',
            'supplier_reference',
            'ean13',
            'product_name',
            'qty_billed',
            'qty_received',
            'qty_missing_to_receive',
        ];

        $rows = collect();

        foreach ($invoice->billedOrders as $billedOrder) {
            foreach ($billedOrder->lines as $line) {
                $meta = $this->resolveProductMeta(
                    (int) ($line->product_id ?? 0),
                    !empty($line->product_attribute_id) ? (int) $line->product_attribute_id : null
                );

                $rows->push([
                    'invoice_reference' => $invoice->invoice_reference,
                    'invoice_date' => $invoice->invoice_date ?? null,
                    'supplier_id' => optional($billedOrder->orderNote)->supplier_id,
                    'billed_order_reference' => $billedOrder->reference,
                    'order_note_reference' => optional($billedOrder->orderNote)->reference,
                    'product_id' => $line->product_id ?? null,
                    'product_attribute_id' => $line->product_attribute_id ?? null,
                    'product_reference' => $meta['product_reference'],
                    'attribute_reference' => $meta['attribute_reference'],
                    'supplier_reference' => $meta['supplier_reference'],
                    'ean13' => $meta['ean13'],
                    'product_name' => $meta['product_name'],
                    'qty_billed' => $line->qty_billed ?? 0,
                    'qty_received' => $line->qty_received ?? 0,
                    'qty_missing_to_receive' => $line->qty_missing_to_receive ?? max(0, (int) ($line->qty_billed ?? 0) - (int) ($line->qty_received ?? 0)),
                ]);
            }
        }

        return $this->streamCsv('supplier-invoice-' . $invoice->id . '.csv', $headers, $rows);
    }

    protected function resolveProductMeta(int $productId, ?int $productAttributeId = null): array
    {
        if ($productId <= 0) {
            return [
                'product_reference' => null,
                'attribute_reference' => null,
                'supplier_reference' => null,
                'ean13' => null,
                'product_name' => null,
            ];
        }

        $prefix = $this->getPrestashopPrefix();
        $langId = (int) (env('PS_LANG_DEFAULT', env('PRESTASHOP_LANG_ID', 1)) ?: 1);

        $product = DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->leftJoin($prefix . 'product_lang as pl', function ($join) use ($langId) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', '=', $langId);
            })
            ->where('p.id_product', $productId)
            ->select([
                'p.reference as product_reference',
                'p.supplier_reference',
                'p.ean13 as product_ean13',
                'pl.name as product_name',
            ])
            ->first();

        $attribute = null;
        if (!empty($productAttributeId)) {
            $attribute = DB::connection('mysql2')
                ->table($prefix . 'product_attribute as pa')
                ->where('pa.id_product_attribute', $productAttributeId)
                ->select([
                    'pa.reference as attribute_reference',
                    'pa.supplier_reference as attribute_supplier_reference',
                    'pa.ean13 as attribute_ean13',
                ])
                ->first();
        }

        return [
            'product_reference' => $product->product_reference ?? null,
            'attribute_reference' => $attribute->attribute_reference ?? null,
            'supplier_reference' => $attribute->attribute_supplier_reference ?? $product->supplier_reference ?? null,
            'ean13' => $attribute->attribute_ean13 ?? $product->product_ean13 ?? null,
            'product_name' => $product->product_name ?? null,
        ];
    }

    protected function getPrestashopPrefix(): string
    {
        return env('DB2_prefix')
            ?: env('DB2_DB_prefix')
            ?: config('database.connections.mysql2.prefix')
            ?: 'ps_';
    }

    protected function normalizeRow($row, array $headers): array
    {
        $array = is_array($row) ? $row : (array) $row;

        $normalized = [];
        foreach ($headers as $header) $normalized[] = $array[$header] ?? null;

        return $normalized;
    }
}
