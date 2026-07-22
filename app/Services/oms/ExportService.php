<?php

namespace App\Services\oms;

use App\Models\modules\oms\BilledOrder;
use App\Models\modules\oms\OrderNote;
use App\Models\modules\oms\SupplierInvoice;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    public function streamXlsx(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $path = tempnam(sys_get_temp_dir(), 'oms-xlsx-');
            $zip = new \ZipArchive();

            if ($path === false || $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Unable to create XLSX export.');
            }

            $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypes());
            $zip->addFromString('_rels/.rels', $this->xlsxRootRelationships());
            $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbook());
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRelationships());
            $zip->addFromString('xl/styles.xml', $this->xlsxStyles());
            $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxWorksheet($headers, $rows));
            $zip->close();

            readfile($path);
            @unlink($path);
        }, preg_replace('/\.csv$/i', '.xlsx', $filename), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function xlsxWorksheet(array $headers, iterable $rows): string
    {
        $allRows = [$headers];
        foreach ($rows as $row) $allRows[] = $this->normalizeRow($row, $headers);

        $sheetRows = '';
        foreach ($allRows as $rowIndex => $values) {
            $cells = '';
            foreach (array_values($values) as $columnIndex => $value) {
                $reference = $this->xlsxColumnName($columnIndex + 1) . ($rowIndex + 1);
                if ($rowIndex > 0 && (is_int($value) || is_float($value))) {
                    $cells .= '<c r="' . $reference . '"><v>' . $value . '</v></c>';
                } else {
                    $cells .= '<c r="' . $reference . '" t="inlineStr"' . ($rowIndex === 0 ? ' s="1"' : '') . '><is><t xml:space="preserve">' . $this->xlsxEscape($value ?? '') . '</t></is></c>';
                }
            }
            $sheetRows .= '<row r="' . ($rowIndex + 1) . '">' . $cells . '</row>';
        }

        $lastColumn = $this->xlsxColumnName(count($headers));
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols><col min="1" max="' . count($headers) . '" width="20" customWidth="1"/></cols><sheetData>' . $sheetRows . '</sheetData><autoFilter ref="A1:' . $lastColumn . '1"/></worksheet>';
    }

    private function xlsxColumnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }
        return $name;
    }

    private function xlsxEscape($value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function xlsxContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>';
    }

    private function xlsxRootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    }

    private function xlsxWorkbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="OMS Export" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function xlsxWorkbookRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    }

    private function xlsxStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font/><font><b/><color rgb="FFFFFFFF"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1F4E78"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border/></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center"/></xf></cellXfs></styleSheet>';
    }

    public function exportOrderNoteXlsx(OrderNote $orderNote): StreamedResponse
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

        return $this->streamXlsx('order-note-' . $orderNote->id . '.xlsx', $headers, $rows);
    }

    public function exportBilledOrderXlsx(BilledOrder $billedOrder): StreamedResponse
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

        return $this->streamXlsx('billed-order-' . $billedOrder->id . '.xlsx', $headers, $rows);
    }

    public function exportInvoiceXlsx(SupplierInvoice $invoice): StreamedResponse
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

        return $this->streamXlsx('supplier-invoice-' . $invoice->id . '.xlsx', $headers, $rows);
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
