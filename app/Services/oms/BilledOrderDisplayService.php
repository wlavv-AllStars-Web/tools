<?php

namespace App\Services\oms;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BilledOrderDisplayService
{
    public function hydrateLines(Collection $lines): Collection
    {
        if ($lines->isEmpty()) {
            return $lines;
        }

        $productIds = $lines->pluck('product_id')->filter()->unique()->values()->all();
        $attributeIds = $lines->pluck('product_attribute_id')->filter()->unique()->values()->all();

        $products = collect();
        if (!empty($productIds)) {
            $products = DB::connection('mysql2')
                ->table('ps_product as p')
                ->leftJoin('ps_product_lang as pl_asm', function ($join) {
                    $join->on('pl_asm.id_product', '=', 'p.id_product')
                         ->where('pl_asm.id_lang', 1)
                         ->where('pl_asm.id_shop', 2);
                })
                ->leftJoin('ps_product_lang as pl_asd', function ($join) {
                    $join->on('pl_asd.id_product', '=', 'p.id_product')
                         ->where('pl_asd.id_lang', 1)
                         ->where('pl_asd.id_shop', 3);
                })
                ->whereIn('p.id_product', $productIds)
                ->select([
                    'p.id_product',
                    'p.reference as product_reference',
                    DB::raw('COALESCE(NULLIF(pl_asm.name, ""), NULLIF(pl_asd.name, ""), "Unknown") as product_name'),
                ])
                ->get()
                ->keyBy('id_product');
        }

        $attributes = collect();
        if (!empty($attributeIds)) {
            $attributes = DB::connection('mysql2')
                ->table('ps_product_attribute as pa')
                ->whereIn('pa.id_product_attribute', $attributeIds)
                ->select([
                    'pa.id_product_attribute',
                    'pa.id_product',
                    'pa.reference as attribute_reference',
                ])
                ->get()
                ->keyBy('id_product_attribute');
        }

        return $lines->map(function ($line) use ($products, $attributes) {
            $product = $products->get((int) $line->product_id);
            $attribute = !empty($line->product_attribute_id)
                ? $attributes->get((int) $line->product_attribute_id)
                : null;

            $displayReference = $attribute->attribute_reference
                ?? $product->product_reference
                ?? null;

            $displayProductName = $product->product_name
                ?? ('Product #' . $line->product_id);

            $displayProductKey = (string) $line->product_id;
            if ((int) ($line->product_attribute_id ?? 0) > 0) {
                $displayProductKey .= ' | ' . (int) $line->product_attribute_id;
            }

            $line->display_reference = $displayReference;
            $line->display_product_name = $displayProductName;
            $line->display_product_key = $displayProductKey;

            return $line;
        });
    }
}