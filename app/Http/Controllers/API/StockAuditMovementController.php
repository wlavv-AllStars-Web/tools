<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\StockAudit\StockAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockAuditMovementController extends Controller
{
    public function store(Request $request, StockAuditService $stockAuditService): JsonResponse
    {
        if (! $this->hasValidToken($request)) {
            return response()->json(['success' => false, 'message' => 'Invalid token'], 403);
        }

        $data = $request->validate([
            'id_product' => ['required', 'integer', 'min:1'],
            'id_product_attribute' => ['nullable', 'integer', 'min:0'],
            'reference' => ['nullable', 'string', 'max:128'],
            'quantity_before' => ['nullable', 'integer'],
            'quantity_after' => ['required', 'integer'],
            'quantity_delta' => ['nullable', 'integer'],
            'id_shop' => ['nullable', 'integer', 'min:0'],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'user_name' => ['nullable', 'string', 'max:255'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $before = $data['quantity_before'] ?? null;
        $after = (int) $data['quantity_after'];
        $delta = $data['quantity_delta'] ?? null;

        if ($before === null && $delta !== null) {
            $before = $after - (int) $delta;
        }

        if ($before === null) {
            return response()->json([
                'success' => false,
                'message' => 'quantity_before or quantity_delta is required',
            ], 422);
        }

        $before = (int) $before;
        $delta = $after - $before;

        if (isset($data['quantity_delta']) && (int) $data['quantity_delta'] !== $delta) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid quantity delta',
            ], 422);
        }

        if ($delta === 0) {
            return response()->json(['success' => true, 'recorded' => false]);
        }

        $stockAuditService->record([
            'id_product' => (int) $data['id_product'],
            'id_product_attribute' => (int) ($data['id_product_attribute'] ?? 0),
            'reference' => $data['reference'] ?? null,
            'source' => 'prestashop',
            'operation' => 'prestashop_quantity_update',
            'quantity_before' => $before,
            'quantity_after' => $after,
            'quantity_delta' => $delta,
            'stock_arrive_before' => null,
            'stock_arrive_after' => null,
            'stock_arrive_delta' => null,
            'user_id' => isset($data['user_id']) ? (int) $data['user_id'] : null,
            'user_name' => $data['user_name'] ?? 'PrestaShop',
            'meta' => json_encode([
                'id_shop' => isset($data['id_shop']) ? (int) $data['id_shop'] : null,
                'hook' => 'actionUpdateQuantity',
            ]),
            'occurred_at' => $data['occurred_at'] ?? now(),
        ]);

        return response()->json(['success' => true, 'recorded' => true], 201);
    }

    private function hasValidToken(Request $request): bool
    {
        $expected = (string) config('allstars.api.tokens.stock_audit');
        $provided = (string) ($request->bearerToken() ?: $request->input('token'));

        return $expected !== '' && hash_equals($expected, $provided);
    }
}