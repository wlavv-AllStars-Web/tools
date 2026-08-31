<?php

namespace App\Http\Controllers\Modules\oms;

use App\Http\Controllers\Controller;
use App\Models\modules\oms\BilledOrderLine;
use App\Models\modules\oms\OmsDocumentLineHistory;
use App\Models\modules\oms\OrderNote;
use App\Models\prestashop\suppliers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SimplifiedOrderNoteController extends Controller
{
    /**
     * Read-only review screen for the simplified OMS workflow.
     * It has no mutation endpoints: real writes will be designed after the UI is approved.
     */
    public function index(Request $request)
    {
        $supplierId = (int) $request->integer('supplier_id');
        $requestedOrderNoteId = (int) $request->integer('order_note_id');

        $suppliers = suppliers::query()
            ->select(['id_supplier', 'name'])
            ->orderBy('name')
            ->get();

        $orderNotes = collect();
        $orderNote = null;

        if ($supplierId > 0) {
            $orderNotes = OrderNote::query()
                ->where('supplier_id', $supplierId)
                ->orderByDesc('created_at')
                ->get(['id', 'supplier_id', 'reference', 'status', 'created_at']);

            $orderNote = $orderNotes->firstWhere('id', $requestedOrderNoteId) ?? $orderNotes->first();

            if ($orderNote) {
                $orderNote->load(['supplier', 'lines']);
            }
        }

        return view('modules.oms.order_notes.simplified', [
            'suppliers' => $suppliers,
            'selectedSupplierId' => $supplierId,
            'orderNotes' => $orderNotes,
            'orderNote' => $orderNote,
            'simplifiedOmsRows' => $orderNote ? $this->rowsForOrderNote($orderNote) : collect(),
        ]);
    }

    private function rowsForOrderNote(OrderNote $orderNote)
    {
        $lines = $orderNote->lines;
        $lineIds = $lines->pluck('id')->map(fn ($id) => (int) $id)->filter()->values();

        if ($lineIds->isEmpty()) {
            return collect();
        }

        $invoicedByLine = BilledOrderLine::query()
            ->whereIn('order_note_line_id', $lineIds->all())
            ->selectRaw('order_note_line_id, SUM(qty_billed) as quantity')
            ->groupBy('order_note_line_id')
            ->pluck('quantity', 'order_note_line_id');

        $receivedByLine = DB::table('oms_reception_lines as reception_lines')
            ->join('oms_billed_order_lines as billed_lines', 'billed_lines.id', '=', 'reception_lines.billed_order_line_id')
            ->whereIn('billed_lines.order_note_line_id', $lineIds->all())
            ->selectRaw('billed_lines.order_note_line_id, SUM(reception_lines.qty_received) as quantity')
            ->groupBy('billed_lines.order_note_line_id')
            ->pluck('quantity', 'billed_lines.order_note_line_id');

        $latestBilledLines = BilledOrderLine::query()
            ->whereIn('order_note_line_id', $lineIds->all())
            ->orderByDesc('id')
            ->get()
            ->groupBy('order_note_line_id')
            ->map(fn ($rows) => $rows->first());

        $latestHistoryByBilledLine = OmsDocumentLineHistory::query()
            ->where('context_type', 'billed_order_line')
            ->whereIn('context_id', $latestBilledLines->pluck('id')->filter()->all())
            ->orderByDesc('id')
            ->get()
            ->groupBy('context_id')
            ->map(fn ($rows) => $rows->first());

        $productIds = $lines->pluck('product_id')->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $attributeIds = $lines->pluck('product_attribute_id')->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $prefix = (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');

        $products = DB::connection('mysql2')
            ->table($prefix . 'product as product')
            ->leftJoin($prefix . 'product_lang as product_lang', 'product_lang.id_product', '=', 'product.id_product')
            ->whereIn('product.id_product', $productIds->all())
            ->groupBy('product.id_product', 'product.reference')
            ->selectRaw('product.id_product, product.reference, MIN(product_lang.name) as name')
            ->get()
            ->keyBy('id_product');

        $attributes = $attributeIds->isEmpty()
            ? collect()
            : DB::connection('mysql2')
                ->table($prefix . 'product_attribute')
                ->whereIn('id_product_attribute', $attributeIds->all())
                ->select(['id_product_attribute', 'reference'])
                ->get()
                ->keyBy('id_product_attribute');

        return $lines->map(function ($line) use ($invoicedByLine, $receivedByLine, $latestBilledLines, $latestHistoryByBilledLine, $products, $attributes) {
            $billedLine = $latestBilledLines->get((int) $line->id);
            $history = $billedLine ? $latestHistoryByBilledLine->get((int) $billedLine->id) : null;
            $product = $products->get((int) $line->product_id);
            $attribute = $line->product_attribute_id ? $attributes->get((int) $line->product_attribute_id) : null;

            return [
                'line_id' => (int) $line->id,
                'product_id' => (int) $line->product_id,
                'product_attribute_id' => $line->product_attribute_id ? (int) $line->product_attribute_id : null,
                'reference' => trim((string) ($attribute->reference ?? '')) ?: trim((string) ($product->reference ?? '')) ?: '—',
                'name' => trim((string) ($product->name ?? '')) ?: 'Product #' . (int) $line->product_id,
                'ordered' => (int) $line->qty_ordered,
                'invoiced' => (int) ($invoicedByLine[(int) $line->id] ?? 0),
                'received' => (int) ($receivedByLine[(int) $line->id] ?? 0),
                'purchase_supplier' => (float) ($billedLine->unit_price_invoice_currency ?? 0),
                'purchase_eur' => (float) ($billedLine->unit_price_eur ?? 0),
                'sales_supplier' => (float) ($history->new_sale_supplier_currency ?? 0),
                'sales_eur' => (float) ($history->new_sale_eur ?? 0),
                'currency_iso' => (string) ($billedLine->currency_iso ?? 'EUR'),
            ];
        })->values();
    }
}
