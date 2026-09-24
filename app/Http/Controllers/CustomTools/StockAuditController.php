<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Services\StockAudit\StockAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StockAuditController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'reference' => ['nullable', 'string', 'max:128'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $movements = collect();
        $snapshots = collect();
        $stateColors = [];

        if (Schema::hasTable('stock_audit_movements')) {
            $movements = DB::table('stock_audit_movements')
                ->when(trim((string) ($filters['reference'] ?? '')) !== '', fn ($query) => $query->where('reference', 'like', '%' . trim($filters['reference']) . '%'))
                ->when(!empty($filters['from']), fn ($query) => $query->whereDate('occurred_at', '>=', $filters['from']))
                ->when(!empty($filters['to']), fn ($query) => $query->whereDate('occurred_at', '<=', $filters['to']))
                ->latest('occurred_at')
                ->paginate(50)
                ->withQueryString();
            $snapshots = DB::table('stock_audit_snapshots')->latest('captured_at')->limit(20)->get();
            $stateColors = $this->orderStateColors($movements);
        }

        $breadcrumbs = [
            ['name' => trans('web'), 'url' => route('web.index')],
            ['name' => 'Stock audit', 'url' => route('web.tools.stock_audit.index'), 'no_translation' => 1],
        ];

        return view('customTools.stock-audit.index', compact('movements', 'snapshots', 'filters', 'stateColors', 'breadcrumbs'));
    }

    public function snapshot(StockAuditService $audit): RedirectResponse
    {
        $snapshot = $audit->createSnapshot();

        return back()->with('success', 'Backup #' . $snapshot['id'] . ' criado (' . $snapshot['items_count'] . ' linhas).');
    }

    public function history(Request $request): View
    {
        $filters = $request->validate([
            'reference' => ['required', 'string', 'max:128'],
            'days' => ['nullable', 'integer', 'min:1', 'max:60'],
        ]);
        $reference = trim($filters['reference']);
        $days = (int) ($filters['days'] ?? 60);
        $chartMovements = collect();
        $movements = collect();
        $stateColors = [];

        if (Schema::hasTable('stock_audit_movements')) {
            $baseQuery = DB::table('stock_audit_movements')
                ->select([
                    'id',
                    'id_order',
                    'reference',
                    'source',
                    'operation',
                    'quantity_before',
                    'quantity_after',
                    'stock_arrive_before',
                    'stock_arrive_after',
                    'user_name',
                    'meta',
                    'occurred_at',
                ])
                ->where('reference', $reference)
                ->where('occurred_at', '>=', now()->subDays($days)->startOfDay());

            $chartMovements = (clone $baseQuery)
                ->orderBy('occurred_at')
                ->orderBy('id')
                ->get();

            $movements = (clone $baseQuery)
                ->latest('occurred_at')
                ->paginate(50)
                ->withQueryString();

            $stateColors = $this->orderStateColors($movements);
        }

        $chart = $this->chartData($chartMovements);
        $breadcrumbs = [
            ['name' => trans('web'), 'url' => route('web.index')],
            ['name' => 'Stock audit', 'url' => route('web.tools.stock_audit.index'), 'no_translation' => 1],
            ['name' => $reference, 'url' => route('web.tools.stock_audit.history', ['reference' => $reference, 'days' => $days]), 'no_translation' => 1],
        ];

        return view('customTools.stock-audit.history', compact('reference', 'days', 'movements', 'chart', 'stateColors', 'breadcrumbs'));
    }

    private function chartData(iterable $movements): array
    {
        $labels = [];
        $quantity = [];
        $stockArrive = [];
        $details = [];

        foreach ($movements as $movement) {
            $labels[] = (string) $movement->occurred_at;
            $quantity[] = $movement->quantity_after === null ? null : (int) $movement->quantity_after;
            $stockArrive[] = $movement->stock_arrive_after === null ? null : (int) $movement->stock_arrive_after;
            $details[] = [
                'user' => $movement->user_name ?: 'Sistema',
                'source' => $movement->source,
                'operation' => $movement->operation,
                'order_id' => $movement->id_order ? (int) $movement->id_order : null,
                'quantity_change' => $movement->quantity_before === null ? null : (int) $movement->quantity_after - (int) $movement->quantity_before,
                'stock_arrive_change' => $movement->stock_arrive_before === null ? null : (int) $movement->stock_arrive_after - (int) $movement->stock_arrive_before,
            ];
        }

        return compact('labels', 'quantity', 'stockArrive', 'details');
    }

    private function orderStateColors(iterable $movements): array
    {
        $stateIds = [];

        foreach ($movements as $movement) {
            $meta = is_string($movement->meta) ? (json_decode($movement->meta, true) ?: []) : (array) $movement->meta;

            foreach (['order_state_before', 'order_state_after'] as $key) {
                $stateId = (int) ($meta[$key]['id'] ?? 0);

                if ($stateId > 0) {
                    $stateIds[$stateId] = $stateId;
                }
            }
        }

        if ($stateIds === []) {
            return [];
        }

        try {
            $prefix = (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');

            return DB::connection('mysql2')
                ->table($prefix . 'order_state')
                ->whereIn('id_order_state', $stateIds)
                ->pluck('color', 'id_order_state')
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }
}