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
                ->paginate(100)
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
        $movements = collect();

        if (Schema::hasTable('stock_audit_movements')) {
            $movements = DB::table('stock_audit_movements')
                ->select([
                    'id',
                    'id_order',
                    'source',
                    'operation',
                    'quantity_before',
                    'quantity_after',
                    'stock_arrive_before',
                    'stock_arrive_after',
                    'user_name',
                    'occurred_at',
                ])
                ->where('reference', $reference)
                ->where('occurred_at', '>=', now()->subDays($days)->startOfDay())
                ->orderBy('occurred_at')
                ->orderBy('id')
                ->get();
        }

        $chart = [
            'quantity' => $this->chartPoints($movements, 'quantity_before', 'quantity_after'),
            'stockArrive' => $this->chartPoints($movements, 'stock_arrive_before', 'stock_arrive_after'),
        ];
        $breadcrumbs = [
            ['name' => trans('web'), 'url' => route('web.index')],
            ['name' => 'Stock audit', 'url' => route('web.tools.stock_audit.index'), 'no_translation' => 1],
            ['name' => $reference, 'url' => route('web.tools.stock_audit.history', ['reference' => $reference, 'days' => $days]), 'no_translation' => 1],
        ];

        return view('customTools.stock-audit.history', compact('reference', 'days', 'movements', 'chart', 'breadcrumbs'));
    }

    private function chartPoints($movements, string $before, string $after): array
    {
        $points = [];
        $hasBaseline = false;

        foreach ($movements as $movement) {
            if ($movement->{$after} === null) {
                continue;
            }

            if (!$hasBaseline && $movement->{$before} !== null) {
                $points[] = [
                    'label' => (string) $movement->occurred_at,
                    'value' => (int) $movement->{$before},
                    'baseline' => true,
                ];
                $hasBaseline = true;
            }

            $points[] = [
                'label' => (string) $movement->occurred_at,
                'value' => (int) $movement->{$after},
                'user' => $movement->user_name ?: 'Sistema',
                'source' => $movement->source,
                'operation' => $movement->operation,
                'order_id' => $movement->id_order ? (int) $movement->id_order : null,
                'change' => $movement->{$before} !== null ? (int) $movement->{$after} - (int) $movement->{$before} : null,
                'baseline' => false,
            ];
        }

        return $points;
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