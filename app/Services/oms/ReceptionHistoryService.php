<?php

namespace App\Services\oms;

use Illuminate\Support\Facades\DB;

class ReceptionHistoryService
{
    public function getByBilledOrder(int $billedOrderId)
    {
        return DB::table('oms_receptions as r')
            ->leftJoin('oms_reception_lines as rl', 'rl.reception_id', '=', 'r.id')
            ->leftJoin('oms_billed_order_lines as bol', 'bol.id', '=', 'rl.billed_order_line_id')
            ->leftJoin('oms_billed_orders as bo', 'bo.id', '=', 'r.billed_order_id')
            ->leftJoin('oms_supplier_invoices as si', 'si.id', '=', 'bo.supplier_invoice_id')
            ->where('r.billed_order_id', $billedOrderId)
            ->orderByDesc('r.created_at')
            ->get([
                'r.id as reception_id',
                'r.billed_order_id',
                'r.created_at',
                'r.created_by',
                'bo.reference as billed_order_reference',
                'si.invoice_reference',
                'rl.id as reception_line_id',
                'rl.billed_order_line_id',
                'bol.product_id',
                'bol.product_attribute_id',
                'bol.qty_billed',
                'bol.qty_received as billed_line_qty_received',
                'rl.qty_received',
            ]);
    }

    public function getByInvoice(int $invoiceId)
    {
        return DB::table('oms_receptions as r')
            ->leftJoin('oms_reception_lines as rl', 'rl.reception_id', '=', 'r.id')
            ->leftJoin('oms_billed_order_lines as bol', 'bol.id', '=', 'rl.billed_order_line_id')
            ->leftJoin('oms_billed_orders as bo', 'bo.id', '=', 'r.billed_order_id')
            ->leftJoin('oms_supplier_invoices as si', 'si.id', '=', 'bo.supplier_invoice_id')
            ->where('si.id', $invoiceId)
            ->orderByDesc('r.created_at')
            ->get([
                'r.id as reception_id',
                'r.billed_order_id',
                'r.created_at',
                'r.created_by',
                'bo.reference as billed_order_reference',
                'si.invoice_reference',
                'rl.id as reception_line_id',
                'rl.billed_order_line_id',
                'bol.product_id',
                'bol.product_attribute_id',
                'bol.qty_billed',
                'bol.qty_received as billed_line_qty_received',
                'rl.qty_received',
            ]);
    }

    public function getFlatExportRows(array $filters = [])
    {
        $query = DB::table('oms_receptions as r')
            ->leftJoin('oms_reception_lines as rl', 'rl.reception_id', '=', 'r.id')
            ->leftJoin('oms_billed_order_lines as bol', 'bol.id', '=', 'rl.billed_order_line_id')
            ->leftJoin('oms_billed_orders as bo', 'bo.id', '=', 'r.billed_order_id')
            ->leftJoin('oms_supplier_invoices as si', 'si.id', '=', 'bo.supplier_invoice_id')
            ->leftJoin('oms_order_notes as onote', 'onote.id', '=', 'bo.order_note_id')
            ->orderByDesc('r.created_at');

        if (!empty($filters['billed_order_id'])) {
            $query->where('r.billed_order_id', (int) $filters['billed_order_id']);
        }

        if (!empty($filters['invoice_id'])) {
            $query->where('si.id', (int) $filters['invoice_id']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('onote.supplier_id', (int) $filters['supplier_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('r.created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('r.created_at', '<=', $filters['date_to']);
        }

        return $query->get([
            'r.id as reception_id',
            'r.created_at',
            'r.created_by',
            'onote.supplier_id',
            'onote.reference as order_note_reference',
            'bo.reference as billed_order_reference',
            'si.invoice_reference',
            'bol.product_id',
            'bol.product_attribute_id',
            'rl.qty_received',
        ]);
    }
}
