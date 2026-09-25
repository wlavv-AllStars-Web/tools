<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OmsProductBalanceController extends Controller
{
    public function show(Request $request, int $productId): JsonResponse
    {
        if (! $this->hasValidToken((string) $request->bearerToken())) {
            return response()->json(['success' => false, 'message' => 'Invalid token'], 403);
        }

        $byAttribute = static fn ($row): int => (int) $row->product_attribute_id;

        $ordered = DB::table('oms_order_note_lines as line')
            ->join('oms_order_notes as note', 'note.id', '=', 'line.order_note_id')
            ->where('note.status', '!=', 'closed')
            ->where('line.product_id', $productId)
            ->selectRaw('COALESCE(line.product_attribute_id, 0) as product_attribute_id, SUM(line.qty_ordered) as quantity')
            ->groupBy('line.product_attribute_id')
            ->get()
            ->keyBy($byAttribute);

        $invoiced = DB::table('oms_billed_order_lines as billed')
            ->join('oms_order_note_lines as line', 'line.id', '=', 'billed.order_note_line_id')
            ->join('oms_order_notes as note', 'note.id', '=', 'line.order_note_id')
            ->where('note.status', '!=', 'closed')
            ->where('line.product_id', $productId)
            ->selectRaw('COALESCE(line.product_attribute_id, 0) as product_attribute_id, SUM(billed.qty_billed) as quantity')
            ->groupBy('line.product_attribute_id')
            ->get()
            ->keyBy($byAttribute);

        $received = DB::table('oms_reception_lines as reception')
            ->join('oms_billed_order_lines as billed', 'billed.id', '=', 'reception.billed_order_line_id')
            ->join('oms_order_note_lines as line', 'line.id', '=', 'billed.order_note_line_id')
            ->join('oms_order_notes as note', 'note.id', '=', 'line.order_note_id')
            ->where('note.status', '!=', 'closed')
            ->where('line.product_id', $productId)
            ->selectRaw('COALESCE(line.product_attribute_id, 0) as product_attribute_id, SUM(reception.qty_received) as quantity')
            ->groupBy('line.product_attribute_id')
            ->get()
            ->keyBy($byAttribute);

        $balances = $ordered->keys()
            ->merge($invoiced->keys())
            ->merge($received->keys())
            ->unique()
            ->mapWithKeys(function (int $attributeId) use ($ordered, $invoiced, $received): array {
                $receivedQuantity = (int) ($received->get($attributeId)->quantity ?? 0);

                return [$attributeId => [
                    'ordered' => (int) ($ordered->get($attributeId)->quantity ?? 0) - $receivedQuantity,
                    'arrive' => (int) ($invoiced->get($attributeId)->quantity ?? 0) - $receivedQuantity,
                ]];
            })
            ->all();

        return response()->json(['success' => true, 'data' => $balances]);
    }

    private function hasValidToken(string $provided): bool
    {
        $expected = (string) config('allstars.api.tokens.oms_balances');

        return $expected !== '' && hash_equals($expected, $provided);
    }
}
