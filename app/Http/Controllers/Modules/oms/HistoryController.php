<?php

namespace App\Http\Controllers\Modules\oms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class HistoryController extends Controller
{
    public function prices(Request $request)
    {
        $query = DB::table('oms_document_line_history as h');

        if ($request->filled('reference')) {
            $query->where(function ($q) use ($request) {
                $q->where('h.display_reference_snapshot', 'like', '%' . $request->reference . '%')
                    ->orWhere('h.product_reference_snapshot', 'like', '%' . $request->reference . '%')
                    ->orWhere('h.attribute_reference_snapshot', 'like', '%' . $request->reference . '%');
            });
        }

        if ($request->filled('product_id')) {
            $query->where('h.product_id', (int) $request->product_id);
        }

        if ($request->filled('product_attribute_id')) {
            $query->where('h.product_attribute_id', (int) $request->product_attribute_id);
        }

        if ($request->filled('billed_order_id')) {
            $query->where('h.billed_order_id', (int) $request->billed_order_id);
        }

        if ($request->filled('supplier_invoice_id')) {
            $query->where('h.supplier_invoice_id', (int) $request->supplier_invoice_id);
        }

        if ($request->filled('invoice_context_id')) {
            $invoiceId = (int) $request->invoice_context_id;

            $query->where(function ($q) use ($invoiceId) {
                $q->where('h.billed_order_id', $invoiceId)
                    ->orWhere('h.supplier_invoice_id', $invoiceId)
                    ->orWhere(function ($sub) use ($invoiceId) {
                        $sub->where('h.context_type', 'billed_order_line')
                            ->where('h.context_id', $invoiceId);
                    });
            });
        }

        if ($request->filled('reception_id')) {
            $query->where('h.reception_id', (int) $request->reception_id);
        }

        if ($request->filled('user_id')) {
            $query->where('h.user_id', (int) $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('h.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('h.created_at', '<=', $request->date_to);
        }

        $rows = $query
            ->orderByDesc('h.created_at')
            ->orderByDesc('h.id')
            ->paginate(50)
            ->withQueryString();

        return view('modules.oms.history.prices', compact('rows'));
    }

    public function stock(Request $request)
    {
        $query = DB::table('oms_stock_history as h');

        if ($request->filled('reference')) {
            $query->where(function ($q) use ($request) {
                $q->where('h.display_reference_snapshot', 'like', '%' . $request->reference . '%')
                    ->orWhere('h.product_reference_snapshot', 'like', '%' . $request->reference . '%')
                    ->orWhere('h.attribute_reference_snapshot', 'like', '%' . $request->reference . '%');
            });
        }

        if ($request->filled('product_id')) {
            $query->where('h.product_id', (int) $request->product_id);
        }

        if ($request->filled('product_attribute_id')) {
            $query->where('h.product_attribute_id', (int) $request->product_attribute_id);
        }

        if ($request->filled('billed_order_id')) {
            $query->where('h.billed_order_id', (int) $request->billed_order_id);
        }

        if ($request->filled('supplier_invoice_id')) {
            $query->where('h.supplier_invoice_id', (int) $request->supplier_invoice_id);
        }

        if ($request->filled('reception_id')) {
            $query->where('h.reception_id', (int) $request->reception_id);
        }

        if ($request->filled('stock_context_id')) {
            $contextId = (int) $request->stock_context_id;

            $query->where(function ($q) use ($contextId) {
                $q->where('h.reception_id', $contextId)
                    ->orWhere('h.billed_order_id', $contextId)
                    ->orWhere('h.supplier_invoice_id', $contextId)
                    ->orWhere('h.source_id', $contextId);
            });
        }

        if ($request->filled('user_id')) {
            $query->where('h.user_id', (int) $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('h.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('h.created_at', '<=', $request->date_to);
        }

        $rows = $query
            ->orderByDesc('h.created_at')
            ->orderByDesc('h.id')
            ->paginate(50)
            ->withQueryString();

        return view('modules.oms.history.stock', compact('rows'));
    }

    public function pricesByInvoice(Request $request, int $invoiceId)
    {
        $request->merge([
            'invoice_context_id' => $invoiceId,
        ]);

        return $this->prices($request);
    }

    public function stockByReception(Request $request, int $receptionId)
    {
        $request->merge([
            'stock_context_id' => $receptionId,
        ]);

        return $this->stock($request);
    }
}