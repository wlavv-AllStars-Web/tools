<?php

namespace App\Services\oms;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OmsProcurementBridge
{
    public static function available(): bool
    {
        return Schema::hasTable('oms_billed_orders')
            && Schema::hasTable('oms_billed_order_lines')
            && Schema::hasTable('oms_receptions')
            && Schema::hasTable('oms_reception_lines');
    }

    public static function pendingLinesByCode(string $code, int $billedOrderId = 0): Collection
    {
        $code = trim($code);

        if ($code === '' || !self::available()) {
            return collect();
        }

        $candidates = self::prestashopCandidates($code);

        if ($candidates['product_ids']->isEmpty() && $candidates['attribute_ids']->isEmpty()) {
            return collect();
        }

        $receivedSubquery = DB::table('oms_reception_lines')
            ->select('billed_order_line_id', DB::raw('SUM(qty_received) as qty_received_sum'))
            ->groupBy('billed_order_line_id');

        $query = DB::table('oms_billed_order_lines as bol')
            ->join('oms_billed_orders as bo', 'bo.id', '=', 'bol.billed_order_id')
            ->leftJoin('oms_order_notes as onote', 'onote.id', '=', 'bo.order_note_id')
            ->leftJoinSub($receivedSubquery, 'rl_sum', 'rl_sum.billed_order_line_id', '=', 'bol.id')
            ->where(function ($query) {
                $query->whereNull('bo.status')
                    ->orWhereNotIn('bo.status', ['cancelled', 'closed']);
            })
            ->whereRaw('COALESCE(bol.qty_billed, 0) > COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0)')
            ->where(function ($query) use ($candidates) {
                if ($candidates['attribute_ids']->isNotEmpty()) {
                    $query->orWhereIn('bol.product_attribute_id', $candidates['attribute_ids']->all());
                }

                if ($candidates['product_ids']->isNotEmpty()) {
                    $query->orWhere(function ($productQuery) use ($candidates) {
                        $productQuery->whereIn('bol.product_id', $candidates['product_ids']->all())
                            ->where(function ($attributeQuery) {
                                $attributeQuery->whereNull('bol.product_attribute_id')
                                    ->orWhere('bol.product_attribute_id', 0);
                            });
                    });
                }
            })
            ->when($billedOrderId > 0, fn ($query) => $query->where('bo.id', $billedOrderId))
            ->select(
                'bol.id',
                'bol.billed_order_id',
                'bol.product_id',
                'bol.product_attribute_id',
                'bol.qty_billed',
                'bol.qty_received',
                'bo.reference as order_reference',
                'onote.reference as order_note_reference',
                DB::raw('COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0) as qty_received_real')
            )
            ->orderBy('bo.id')
            ->get();

        return self::hydrateLegacyLines($query);
    }

    public static function pendingLineById(int $lineId)
    {
        if ($lineId <= 0 || !self::available()) {
            return null;
        }

        $receivedSubquery = DB::table('oms_reception_lines')
            ->select('billed_order_line_id', DB::raw('SUM(qty_received) as qty_received_sum'))
            ->groupBy('billed_order_line_id');

        $line = DB::table('oms_billed_order_lines as bol')
            ->join('oms_billed_orders as bo', 'bo.id', '=', 'bol.billed_order_id')
            ->leftJoin('oms_order_notes as onote', 'onote.id', '=', 'bo.order_note_id')
            ->leftJoinSub($receivedSubquery, 'rl_sum', 'rl_sum.billed_order_line_id', '=', 'bol.id')
            ->where('bol.id', $lineId)
            ->select(
                'bol.id',
                'bol.billed_order_id',
                'bol.product_id',
                'bol.product_attribute_id',
                'bol.qty_billed',
                'bol.qty_received',
                'bo.reference as order_reference',
                'onote.reference as order_note_reference',
                DB::raw('COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0) as qty_received_real')
            )
            ->first();

        return self::hydrateLegacyLines(collect([$line])->filter())->first();
    }

    public static function recordReception(int $lineId, int $newReceivedTotal): int
    {
        $line = self::pendingLineById($lineId);

        if (!$line) {
            return 0;
        }

        $quantityToReceive = $newReceivedTotal - (int) $line->qty_received;

        if ($quantityToReceive <= 0) {
            return 0;
        }

        DB::transaction(function () use ($line, $quantityToReceive) {
            $receptionId = DB::table('oms_receptions')->insertGetId([
                'billed_order_id' => (int) $line->po_id,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('oms_reception_lines')->insert([
                'reception_id' => $receptionId,
                'billed_order_line_id' => (int) $line->oms_billed_order_line_id,
                'qty_received' => $quantityToReceive,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('oms_billed_order_lines')
                ->where('id', (int) $line->oms_billed_order_line_id)
                ->update([
                    'qty_received' => DB::raw('COALESCE(qty_received, 0) + ' . $quantityToReceive),
                    'updated_at' => now(),
                ]);
        });

        return $quantityToReceive;
    }

    private static function prestashopCandidates(string $code): array
    {
        $prefix = self::psPrefix();

        $products = DB::connection('mysql2')
            ->table($prefix . 'product')
            ->where('ean13', $code)
            ->orWhere('reference', 'LIKE', $code)
            ->pluck('id_product');

        $attributes = DB::connection('mysql2')
            ->table($prefix . 'product_attribute')
            ->where('ean13', $code)
            ->orWhere('reference', 'LIKE', $code)
            ->get(['id_product', 'id_product_attribute']);

        return [
            'product_ids' => $products->merge($attributes->pluck('id_product'))->map(fn ($id) => (int) $id)->unique()->values(),
            'attribute_ids' => $attributes->pluck('id_product_attribute')->map(fn ($id) => (int) $id)->unique()->values(),
        ];
    }

    private static function hydrateLegacyLines(Collection $rows): Collection
    {
        if ($rows->isEmpty()) {
            return collect();
        }

        $prefix = self::psPrefix();
        $productIds = $rows->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->unique()->values();
        $attributeIds = $rows->pluck('product_attribute_id')->filter()->map(fn ($id) => (int) $id)->unique()->values();

        $products = DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->leftJoin($prefix . 'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->whereIn('p.id_product', $productIds->all())
            ->select(
                'p.id_product',
                'p.reference',
                'p.ean13',
                'p.location as housing',
                'p.width',
                'p.height',
                'p.depth',
                'p.weight',
                DB::raw('COALESCE(cp.dim_verify, 0) as dim_verify'),
                DB::raw('COALESCE(cp.parcels, 1) as parcels'),
                DB::raw('COALESCE(cp.fc, 0) as fc')
            )
            ->get()
            ->keyBy('id_product');

        $attributes = $attributeIds->isEmpty()
            ? collect()
            : DB::connection('mysql2')
                ->table($prefix . 'product_attribute as pa')
                ->leftJoin($prefix . 'custom_product_attribute as cpa', 'cpa.id_product_attribute', '=', 'pa.id_product_attribute')
                ->whereIn('pa.id_product_attribute', $attributeIds->all())
                ->select(
                    'pa.id_product_attribute',
                    'pa.id_product',
                    'pa.reference',
                    'pa.ean13',
                    'cpa.location as attr_location'
                )
                ->get()
                ->keyBy('id_product_attribute');

        return $rows->map(function ($row) use ($products, $attributes) {
            $productId = (int) $row->product_id;
            $attributeId = (int) ($row->product_attribute_id ?? 0);
            $product = clone ($products->get($productId) ?? (object) []);
            $attribute = $attributeId > 0 ? $attributes->get($attributeId) : null;
            $sku = trim((string) data_get($attribute, 'reference', data_get($product, 'reference', '')));
            $ean13 = trim((string) data_get($attribute, 'ean13', data_get($product, 'ean13', '')));

            $product->attr_location = data_get($attribute, 'attr_location', '');

            return (object) [
                'oms_billed_order_line_id' => (int) $row->id,
                'po_id' => (int) $row->billed_order_id,
                'product_id' => $productId,
                'product_attribute_id' => $attributeId,
                'sku' => $sku,
                'wmsku' => $sku,
                'wmean13' => $ean13,
                'qty_ordered' => (int) $row->qty_billed,
                'qty_wmfaturado' => (int) $row->qty_billed,
                'qty_received' => (int) $row->qty_received_real,
                'qty_expected' => max(0, (int) $row->qty_billed - (int) $row->qty_received_real),
                'is_new' => 0,
                'product' => $product,
                'attribute' => $attribute,
                'order_reference' => $row->order_reference ?: ($row->order_note_reference ?: ('OMS-' . $row->billed_order_id)),
            ];
        });
    }

    private static function psPrefix(): string
    {
        return env('DB2_prefix', env('DB2_DB_prefix', 'ps_'));
    }
}
