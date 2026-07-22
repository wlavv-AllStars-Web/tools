<?php

namespace App\Services\oms;

use App\Models\modules\oms\OrderNote;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class OrderNotePrintService
{
    public function download(OrderNote $orderNote, array $supplierMap): Response
    {
        require_once app_path('Libraries/tcpdf/tcpdf.php');

        $orderNote->loadMissing(['lines', 'supplier']);
        $rows = $orderNote->lines->map(fn ($line) => $this->lineData($line));
        $currency = strtoupper(trim((string) ($supplierMap['currency'] ?? 'EUR')));
        $symbol = match ($currency) {
            'USD' => '$',
            'GBP' => html_entity_decode('&pound;'),
            'EUR' => html_entity_decode('&euro;'),
            default => $currency,
        };
        $totalQuantity = (int) $rows->sum('quantity');
        $total = (float) $rows->sum(fn ($row) => $row['quantity'] * $row['unit_price']);

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('All Stars Group OMS');
        $pdf->SetAuthor('All Stars Distribution');
        $pdf->SetTitle('PO ' . $orderNote->reference);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetMargins(10, 8, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->writeHTML($this->html($orderNote, $supplierMap, $rows->all(), $symbol, $totalQuantity, $total), true, false, true, false, '');

        $filename = 'order-note-' . $orderNote->id . '-print.pdf';

        return response($pdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function lineData($line): array
    {
        $prefix = env('DB2_prefix') ?: env('DB2_DB_prefix') ?: config('database.connections.mysql2.prefix') ?: 'ps_';
        $product = DB::connection('mysql2')->table($prefix . 'product as p')
            ->leftJoin($prefix . 'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->where('p.id_product', (int) $line->product_id)
            ->select('p.reference', 'p.supplier_reference', 'p.wholesale_price', 'cp.wholesale_price_base_currency')
            ->first();
        $attribute = null;
        if (!empty($line->product_attribute_id)) {
            $attribute = DB::connection('mysql2')->table($prefix . 'product_attribute as pa')
                ->leftJoin($prefix . 'custom_product_attribute as cpa', 'cpa.id_product_attribute', '=', 'pa.id_product_attribute')
                ->where('pa.id_product_attribute', (int) $line->product_attribute_id)
                ->select('pa.reference', 'pa.supplier_reference', 'pa.wholesale_price', 'cpa.wholesale_price_base_currency')
                ->first();
        }

        $reference = $attribute->supplier_reference ?? $product->supplier_reference ?? $attribute->reference ?? $product->reference ?? '';
        $price = (float) ($attribute->wholesale_price_base_currency ?? 0);
        if ($price <= 0) $price = (float) ($product->wholesale_price_base_currency ?? 0);
        if ($price <= 0) $price = (float) ($attribute->wholesale_price ?? 0);
        if ($price <= 0) $price = (float) ($product->wholesale_price ?? 0);

        return ['reference' => $reference, 'quantity' => (int) $line->qty_ordered, 'unit_price' => $price];
    }

    private function html(OrderNote $orderNote, array $supplierMap, array $rows, string $symbol, int $totalQuantity, float $total): string
    {
        $escape = fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $date = optional($orderNote->created_at)->format('Y-m-d') ?: now()->format('Y-m-d');
        $email = $supplierMap['email'] ?? '';
        $incoterm = $supplierMap['incoterm'] ?? 'Ex Works';
        $logo = $escape(public_path('images/oms/logo_asd.png'));
        $body = '';

        foreach ($rows as $row) {
            $body .= '<tr nobr="true"><td width="34%" align="center">' . $escape($row['reference']) . '</td><td width="34%" align="center"><b>' . $row['quantity'] . '</b></td><td width="32%" align="right">' . number_format($row['unit_price'], 2, '.', ' ') . ' ' . $escape($symbol) . '</td></tr>';
        }

        return '<style>
            body{font-family:dejavusans;color:#000;font-size:9.5pt}.email{text-align:center;font-size:9pt}.meta{text-align:center;line-height:1.8}.po{text-align:center;font-size:10pt}.separator{background-color:#e5e5e5}.items th{background-color:#f2f2f2;font-weight:normal;text-align:center}.items td{line-height:1.05}.totals td{text-align:center}.footer{text-align:center}
        </style>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <td width="34%" align="center"><br><img src="' . $logo . '" width="175"><br></td>
                <td width="66%">
                    <table border="0" cellpadding="5" cellspacing="0">
                        <tr><td class="email" colspan="2">' . $escape($email) . '</td></tr>
                        <tr><td class="meta" width="50%"><b>DATE: ' . $date . '</b><br><b>VALID FOR 30 DAYS</b></td><td class="po" width="50%"><br><b>PO: # ' . $escape($orderNote->reference) . '</b></td></tr>
                    </table>
                </td>
            </tr>
        </table>
        <table class="separator" border="1" cellpadding="2" cellspacing="0"><tr><td>&nbsp;</td></tr></table>
        <table class="items" border="1" cellpadding="2" cellspacing="0"><thead><tr><th width="34%">SKU</th><th width="34%">Qtity</th><th width="32%" align="right">BRP</th></tr></thead><tbody>' . $body . '</tbody></table>
        <table class="separator" border="1" cellpadding="2" cellspacing="0"><tr><td>&nbsp;</td></tr></table>
        <table class="totals" border="1" cellpadding="3" cellspacing="0"><tr><td width="34%">TOTAL SKUs: ' . count($rows) . '</td><td width="34%">TOTAL QTITY: ' . $totalQuantity . '</td><td width="32%" align="right">TOTAL: ' . number_format($total, 2, '.', ' ') . ' ' . $escape($symbol) . '</td></tr></table>
        <table class="separator" border="1" cellpadding="2" cellspacing="0"><tr><td>&nbsp;</td></tr></table>
        <table border="1" cellpadding="4" cellspacing="0"><tr><td width="34%" align="center">SHIPPING METHOD</td><td width="66%" align="center">' . $escape($incoterm) . '</td></tr></table>
        <table border="1" cellpadding="7" cellspacing="0"><tr><td class="footer">All Stars Distribution - Z.I Gandra 4930-311 Valenca - Portugal</td></tr></table>';
    }
}