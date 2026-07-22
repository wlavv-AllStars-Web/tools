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
            'GBP' => '£',
            'EUR' => '€',
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
        $pdf->SetMargins(18, 16, 18);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();
        $pdf->Image(public_path('images/oms/logo_asd.png'), 18, 12, 45, 0, 'PNG');
        $pdf->SetY(31);
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
        $body = '';
        foreach ($rows as $row) {
            $body .= '<tr><td>' . $escape($row['reference']) . '</td><td class="qty">' . $row['quantity'] . '</td><td class="price">' . number_format($row['unit_price'], 2, '.', ' ') . ' ' . $escape($symbol) . '</td></tr>';
        }

        return '<style>
            body{font-family:dejavusans;color:#252525;font-size:9pt}.contact{color:#777;font-size:8pt;text-align:right}.meta{width:100%;margin-top:8px;margin-bottom:14px}.meta td{padding:3px 4px}.meta-label{font-size:8pt;color:#666;font-weight:bold;width:22%}.po-value{font-size:13pt;font-weight:bold}.items{width:100%;border-collapse:collapse}.items th{background-color:#333333;color:#ffffff;font-weight:bold;padding:7px 8px;border:1px solid #333333}.items td{padding:6px 8px;border-bottom:1px solid #d7d7d7}.sku{width:54%}.qty{text-align:center;width:16%}.price{text-align:right;width:30%}.summary-wrap{width:100%;margin-top:12px}.summary-spacer{width:54%}.summary{width:46%}.summary td{padding:4px 6px;border-bottom:1px solid #dddddd}.summary .value{text-align:right;font-weight:bold}.summary .grand td{font-size:11pt;border-top:1px solid #333333;border-bottom:0}.shipping{width:100%;margin-top:20px;border-top:1px solid #333333}.shipping td{padding-top:8px}.shipping-title{font-size:8pt;font-weight:bold;color:#555}.shipping-value{font-size:10pt}.footer{margin-top:28px;color:#777;font-size:8pt;text-align:center;border-top:1px solid #dddddd;padding-top:8px}
        </style>
        <div class="contact">' . $escape($email) . '</div>
        <table class="meta" cellpadding="0"><tr><td class="meta-label">DATE:</td><td>' . $date . '</td></tr><tr><td class="meta-label">VALID FOR:</td><td>30 DAYS</td></tr><tr><td class="meta-label">PO:</td><td class="po-value"># ' . $escape($orderNote->reference) . '</td></tr></table>
        <table class="items" cellpadding="0"><thead><tr><th class="sku">SKU</th><th class="qty">Qtity</th><th class="price">BRP</th></tr></thead><tbody>' . $body . '</tbody></table>
        <table class="summary-wrap"><tr><td class="summary-spacer"></td><td><table class="summary"><tr><td>TOTAL SKUs:</td><td class="value">' . count($rows) . '</td></tr><tr><td>TOTAL QTITY:</td><td class="value">' . $totalQuantity . '</td></tr><tr class="grand"><td>TOTAL:</td><td class="value">' . number_format($total, 2, '.', ' ') . ' ' . $escape($symbol) . '</td></tr></table></td></tr></table>
        <table class="shipping"><tr><td><span class="shipping-title">SHIPPING METHOD</span><br><span class="shipping-value">' . $escape($incoterm) . '</span></td></tr></table>
        <div class="footer">All Stars Distribution · Z.I. Gandra · 4930-311 Valença · Portugal</div>';
    }
}