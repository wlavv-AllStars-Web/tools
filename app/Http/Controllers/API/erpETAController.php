<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\modules\shipping\shipping;
use App\Models\prestashop\pack;
use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;
use App\Models\prestashop\product_lang;
use App\Models\prestashop\stock_available;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class erpETAController extends Controller
{
    public function getProductFromERP(Request $request): JsonResponse
    {
        $idLang = (int) ($request->id_lang ?? 1);

        [$idProduct, $idProductAttr, $product, $resolvedReference] = $this->resolveRequestedProduct($request);

        if (!$product) {
            return response()->json([
                'waiting' => false,
                'quantity' => 0,
                'message' => 'Product not found!',
            ]);
        }

        $hasCombinations = product_attribute::where('id_product', $idProduct)->count() > 0;
        if ($hasCombinations && $idProductAttr === 0 && (string) $product->reference === (string) $resolvedReference) {
            return response()->json([
                'waiting' => false,
                'quantity' => 0,
                'message' => '',
            ]);
        }

        $labels = [
            'in_stock' =>       [1 => 'In stock: ',         4 => 'En stock: ',          5 => 'En stock: '],
            'shipped_within' => [1 => 'Shipped within: ',   4 => 'Enviado dentro de: ', 5 => 'Expédié sous: '],
            'available_on' =>   [1 => 'Availability: ',     4 => 'Disponibilidad: ',    5 => 'Disponibilité: '],
        ];

        $tooltips = [
            'out_of_stock_generic' => [
                1 => 'This product is currently out of stock and there are no pending OMS incoming quantities linked to an active shipment.',
                4 => 'Este producto no está disponible en stock y no existen cantidades pendientes en el OMS vinculadas a un envío activo.',
                5 => 'Ce produit n’est pas en stock et aucune quantité entrante OMS n’est liée à un envoi actif.',
            ],
            'in_stock_generic' => [
                1 => 'This product is currently in stock in our warehouses.',
                4 => 'Este producto está actualmente en stock en nuestros almacenes.',
                5 => 'Ce produit est actuellement en stock dans nos entrepôts.',
            ],
            'incoming_with_eta' => [
                1 => 'This item has invoiced quantities in OMS that are still pending reception and already linked to a shipment. The displayed date is the latest ETA recorded for the shipment.',
                4 => 'Este artículo tiene cantidades facturadas en el OMS aún pendientes de recepción y ya vinculadas a un envío. La fecha mostrada es la última ETA registrada para ese envío.',
                5 => 'Cet article a des quantités facturées dans l’OMS encore en attente de réception et déjà liées à un envoi. La date affichée correspond à la dernière ETA enregistrée pour cet envoi.',
            ],
        ];

        $getProductInfoMessage = function (string $messageText): string {
            return '<label class="editable">' . $messageText . '</label>';
        };

        $getTooltipIcon = function (string $tooltipText): string {
            $safe = htmlspecialchars($tooltipText, ENT_QUOTES, 'UTF-8');

            return '<i class="fa fa-question-circle api-tip"
                        title="' . $safe . '"
                        data-tip="' . $safe . '"
                        aria-label="Info"
                        role="button"
                        tabindex="0"
                        style="font-size:18px;line-height:24px;vertical-align:middle;margin:0 8px;cursor:pointer;color:#666;"></i>';
        };

        $formatEtaForTooltip = function (int $lang, Carbon $date): string {
            return in_array($lang, [1, 4, 5], true) ? $date->format('d/m/Y') : $date->format('Y-m-d');
        };

        $packInfo = pack::availablePackQty($idProduct);
        $isPack = is_array($packInfo) && !empty($packInfo['is_pack']) && $packInfo['is_pack'] === true;

        if (!$isPack) {
            $stockQty = $this->getPrestashopStockQty($idProduct, $idProductAttr);
            if ($stockQty > 0) {
                $tooltip = $tooltips['in_stock_generic'][$idLang] ?? '';
                $message = $getProductInfoMessage(
                    ($labels['in_stock'][$idLang] ?? $labels['in_stock'][1])
                    . $getTooltipIcon($tooltip)
                    . ' <span class="label label-success" style="font-size:14px;font-weight:900">' . $stockQty . '</span>'
                );

                return response()->json([
                    'waiting' => false,
                    'quantity' => $stockQty,
                    'message' => $message,
                ]);
            }

            $incoming = $this->getOmsIncomingForProduct($idProduct, $idProductAttr);
            if ($incoming['quantity'] > 0 && $incoming['eta']) {
                $date = Carbon::createFromFormat('Y-m-d', $incoming['eta']);
                $dateMsg = $date->format('d/m/Y');
                $tooltip = $tooltips['incoming_with_eta'][$idLang] ?? '';
                $tooltip = str_replace('{{ETA}}', $formatEtaForTooltip($idLang, $date), $tooltip);

                $message = $getProductInfoMessage(
                    ($labels['available_on'][$idLang] ?? $labels['available_on'][1])
                    . $getTooltipIcon($tooltip)
                    . ' <span class="label label-warning" style="color:#333;">' . $dateMsg . '</span>'
                );

                return response()->json([
                    'waiting' => true,
                    'quantity' => $incoming['quantity'],
                    'message' => $message,
                    'eta' => $incoming['eta'],
                    'shipment_id' => $incoming['shipment_id'],
                ]);
            }

            $productInfo = product_lang::where('id_lang', $idLang)->where('id_product', $idProduct)->first();
            $tooltip = $tooltips['out_of_stock_generic'][$idLang] ?? '';
            $fallbackLabel = trim((string) optional($productInfo)->available_later);
            $fallbackLabel = $fallbackLabel !== '' ? $fallbackLabel : 'OUT OF STOCK';

            $message = $getProductInfoMessage(
                ($labels['shipped_within'][$idLang] ?? $labels['shipped_within'][1])
                . $getTooltipIcon($tooltip)
                . ' <span class="label label-warning" style="color:#333;">' . $fallbackLabel . '</span>'
            );

            return response()->json([
                'waiting' => false,
                'quantity' => 0,
                'message' => $message,
            ]);
        }

        $packQty = isset($packInfo['pack_qty']) ? (int) $packInfo['pack_qty'] : 0;
        if ($packQty > 0) {
            $tooltip = $tooltips['in_stock_generic'][$idLang] ?? '';
            $message = $getProductInfoMessage(
                ($labels['in_stock'][$idLang] ?? $labels['in_stock'][1])
                . $getTooltipIcon($tooltip)
                . ' <span class="label label-success" style="font-size:14px;font-weight:900">' . $packQty . '</span>'
            );

            return response()->json([
                'waiting' => false,
                'quantity' => $packQty,
                'message' => $message,
                'is_pack' => true,
            ]);
        }

        $components = $packInfo['components'] ?? [];
        if (empty($components)) {
            return response()->json([
                'waiting' => false,
                'quantity' => 0,
                'message' => 'OUT OF STOCK',
                'is_pack' => true,
            ]);
        }

        $expectedPacks = null;
        $etaCandidates = [];
        $foundIncoming = false;

        foreach ($components as $component) {
            $idCompProduct = (int) ($component['id_product'] ?? 0);
            $idCompAttr = (int) ($component['id_product_attribute'] ?? 0);
            $qtyInPack = max((int) ($component['qty_in_pack'] ?? 1), 1);

            $compStock = array_key_exists('stock', $component)
                ? (int) $component['stock']
                : $this->getPrestashopStockQty($idCompProduct, $idCompAttr);

            $incoming = $this->getOmsIncomingForProduct($idCompProduct, $idCompAttr);
            $incomingQty = (int) ($incoming['quantity'] ?? 0);
            if ($incomingQty > 0) {
                $foundIncoming = true;
            }
            if (!empty($incoming['eta'])) {
                $etaCandidates[] = $incoming['eta'];
            }

            $possiblePacks = (int) floor(($compStock + $incomingQty) / $qtyInPack);
            $expectedPacks = is_null($expectedPacks) ? $possiblePacks : min($expectedPacks, $possiblePacks);
        }

        $expectedPacks = $expectedPacks ?? 0;
        if ($expectedPacks <= 0) {
            return response()->json([
                'waiting' => false,
                'quantity' => 0,
                'message' => 'OUT OF STOCK',
                'is_pack' => true,
            ]);
        }

        if (!empty($etaCandidates)) {
            sort($etaCandidates);
            $expectedEta = (string) $etaCandidates[0];
            $date = Carbon::createFromFormat('Y-m-d', $expectedEta);
            $dateMessage = $date->format('d/m/Y');
            $tooltip = $tooltips['incoming_with_eta'][$idLang] ?? '';
            $tooltip = str_replace('{{ETA}}', $formatEtaForTooltip($idLang, $date), $tooltip);

            $message = $getProductInfoMessage(
                ($labels['available_on'][$idLang] ?? $labels['available_on'][1])
                . $getTooltipIcon($tooltip)
                . ' <span class="label label-warning" style="color:#333;">' . $dateMessage . '</span>'
            );

            return response()->json([
                'waiting' => true,
                'quantity' => $expectedPacks,
                'message' => $message,
                'is_pack' => true,
                'eta' => $expectedEta,
            ]);
        }

        return response()->json([
            'waiting' => $foundIncoming,
            'quantity' => $expectedPacks,
            'message' => 'OUT OF STOCK',
            'is_pack' => true,
        ]);
    }

    public function getEtaBatch(Request $request): JsonResponse
    {
        $references = $request->input('references', []);

        if (!is_array($references) || empty($references)) {
            return response()->json([
                'error' => true,
                'message' => 'references must be a non-empty array',
            ], 422);
        }

        $references = collect(array_slice($references, 0, 25))
            ->map(fn ($reference) => trim((string) $reference))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $results = $this->computeEtaBatch($references);

        return response()
            ->json([
                'count' => count($results),
                'results' => $results,
            ])
            ->header('Cache-Control', 'no-store, private, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Resolve an ETA batch with a bounded number of queries.
     *
     * Product, stock, pack and OMS data is fetched once for the entire request,
     * instead of repeating the same lookups for every reference.
     */
    private function computeEtaBatch(array $references): array
    {
        if (empty($references)) {
            return [];
        }

        $productTable = (new product())->getTable();
        $attributeTable = (new product_attribute())->getTable();
        $packTable = (new pack())->getTable();
        $stockTable = (new stock_available())->getTable();
        $prestashop = DB::connection('mysql2');

        $productsByReference = $prestashop->table($productTable)
            ->whereIn('reference', $references)
            ->get(['id_product', 'reference'])
            ->keyBy('reference');

        $attributesByReference = $prestashop->table($attributeTable)
            ->whereIn('reference', $references)
            ->get(['id_product', 'id_product_attribute', 'reference'])
            ->keyBy('reference');

        $resolved = [];
        foreach ($references as $reference) {
            $product = $productsByReference->get($reference);
            $attribute = $attributesByReference->get($reference);

            if ($product) {
                $resolved[$reference] = [
                    'id_product' => (int) $product->id_product,
                    'id_product_attribute' => 0,
                ];
            } elseif ($attribute) {
                $resolved[$reference] = [
                    'id_product' => (int) $attribute->id_product,
                    'id_product_attribute' => (int) $attribute->id_product_attribute,
                ];
            }
        }

        $productIds = collect($resolved)->pluck('id_product')->unique()->values()->all();
        if (empty($productIds)) {
            return collect($references)->mapWithKeys(fn ($reference) => [
                $reference => ['status' => 'not_found', 'quantity' => 'OUT OF STOCK'],
            ])->all();
        }

        $productsWithCombinations = $prestashop->table($attributeTable)
            ->whereIn('id_product', $productIds)
            ->distinct()
            ->pluck('id_product')
            ->flip();

        $packItems = $prestashop->table($packTable)
            ->whereIn('id_product_pack', $productIds)
            ->get(['id_product_pack', 'id_product_item', 'id_product_attribute_item', 'quantity'])
            ->groupBy('id_product_pack');

        $stockPairs = collect($resolved)->map(fn ($item) => [
            'product' => $item['id_product'],
            'attribute' => $item['id_product_attribute'],
        ]);
        foreach ($packItems as $items) {
            foreach ($items as $item) {
                $stockPairs->push([
                    'product' => (int) $item->id_product_item,
                    'attribute' => (int) $item->id_product_attribute_item,
                ]);
            }
        }
        $stockPairs = $stockPairs
            ->unique(fn ($item) => $item['product'] . '-' . $item['attribute'])
            ->values();

        // getPrestashopStockQty() uses the first matching row. Preserve that behaviour
        // while loading every required product/combination in a single query.
        $stockRowsByPair = $prestashop->table($stockTable)
            ->where(function ($query) use ($stockPairs) {
                foreach ($stockPairs as $pair) {
                    $query->orWhere(function ($pairQuery) use ($pair) {
                        $pairQuery->where('id_product', $pair['product'])
                            ->where('id_product_attribute', $pair['attribute']);
                    });
                }
            })
            ->orderBy('id_stock_available')
            ->get(['id_product', 'id_product_attribute', 'quantity'])
            ->groupBy(fn ($row) => $row->id_product . '-' . $row->id_product_attribute);

        // Direct products use getPrestashopStockQty() (first row). Pack components
        // use pack::availablePackQty() (keyBy(), therefore the last row).
        $stockByPair = $stockRowsByPair->map(fn ($rows) => (int) $rows->first()->quantity);
        $packStockByPair = $stockRowsByPair->map(fn ($rows) => (int) $rows->last()->quantity);

        $incomingByPair = $this->getOmsIncomingForProductBatch($stockPairs->all());
        $results = [];

        foreach ($references as $reference) {
            $item = $resolved[$reference] ?? null;
            if ($item === null) {
                $results[$reference] = ['status' => 'not_found', 'quantity' => 'OUT OF STOCK'];
                continue;
            }

            $idProduct = $item['id_product'];
            $idProductAttribute = $item['id_product_attribute'];
            if ($idProductAttribute === 0 && $productsWithCombinations->has($idProduct)) {
                $results[$reference] = ['status' => 'not_found', 'quantity' => 'OUT OF STOCK'];
                continue;
            }

            $key = $idProduct . '-' . $idProductAttribute;
            $quantity = (int) ($stockByPair->get($key, 0));
            if ($quantity > 0) {
                $results[$reference] = ['status' => 'in_stock', 'quantity' => $quantity];
                continue;
            }

            $components = $packItems->get($idProduct, collect());
            if ($components->isEmpty()) {
                $incoming = $incomingByPair[$key] ?? ['quantity' => 0, 'eta' => null, 'shipment_id' => null];
                $results[$reference] = $incoming['quantity'] > 0 && $incoming['eta']
                    ? [
                        'status' => 'eta',
                        'eta' => $incoming['eta'],
                        'quantity' => $incoming['quantity'],
                        'shipment_id' => $incoming['shipment_id'],
                    ]
                    : ['status' => 'out_of_stock', 'quantity' => 'OUT OF STOCK'];
                continue;
            }

            $expectedPacks = null;
            $etaCandidates = [];
            foreach ($components as $component) {
                $componentKey = $component->id_product_item . '-' . $component->id_product_attribute_item;
                $incoming = $incomingByPair[$componentKey] ?? ['quantity' => 0, 'eta' => null];
                $qtyInPack = max((int) $component->quantity, 1);
                $possiblePacks = (int) floor(((int) $packStockByPair->get($componentKey, 0) + (int) $incoming['quantity']) / $qtyInPack);
                $expectedPacks = $expectedPacks === null ? $possiblePacks : min($expectedPacks, $possiblePacks);

                if (!empty($incoming['eta'])) {
                    $etaCandidates[] = $incoming['eta'];
                }
            }

            if (($expectedPacks ?? 0) <= 0) {
                $results[$reference] = ['status' => 'out_of_stock', 'quantity' => 'OUT OF STOCK'];
            } elseif (!empty($etaCandidates)) {
                sort($etaCandidates);
                $results[$reference] = [
                    'status' => 'eta',
                    'eta' => (string) $etaCandidates[0],
                    'quantity' => $expectedPacks,
                    'is_pack' => true,
                ];
            } else {
                $results[$reference] = ['status' => 'out_of_stock', 'quantity' => 'OUT OF STOCK'];
            }
        }

        return $results;
    }

    /**
     * Fetch pending OMS quantities and ETAs for every requested product pair at once.
     */
    private function getOmsIncomingForProductBatch(array $pairs): array
    {
        if (empty($pairs)) {
            return [];
        }

        $pairKeys = collect($pairs)
            ->mapWithKeys(fn ($pair) => [$pair['product'] . '-' . $pair['attribute'] => true]);
        $productIds = collect($pairs)->pluck('product')->unique()->values()->all();

        $rows = DB::table('oms_billed_order_lines as bol')
            ->join('oms_billed_orders as bo', 'bo.id', '=', 'bol.billed_order_id')
            ->join('oms_supplier_invoices as si', 'si.id', '=', 'bo.supplier_invoice_id')
            ->leftJoin('shipping_erp as se', 'se.id_erp', '=', 'si.id')
            ->leftJoin('shipping as s', 's.id', '=', 'se.id_shipping')
            ->leftJoin(DB::raw('(
                SELECT sd.id_shipping, MAX(sd.date) as eta_date
                FROM shipping_delay sd
                GROUP BY sd.id_shipping
            ) as eta_map'), 'eta_map.id_shipping', '=', 's.id')
            ->leftJoin(DB::raw('(
                SELECT rl.billed_order_line_id, SUM(rl.qty_received) as qty_received_sum
                FROM oms_reception_lines rl
                GROUP BY rl.billed_order_line_id
            ) as rl_sum'), 'rl_sum.billed_order_line_id', '=', 'bol.id')
            ->whereIn('bol.product_id', $productIds)
            ->where(function ($query) {
                $query->whereNull('si.status')
                    ->orWhere('si.status', '!=', 'cancelled');
            })
            ->selectRaw('
                bol.product_id,
                COALESCE(bol.product_attribute_id, 0) as product_attribute_id,
                bol.qty_billed,
                COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0) as qty_received_real,
                s.id as shipment_id,
                eta_map.eta_date as eta_date
            ')
            ->get();

        $incoming = [];
        foreach ($rows as $row) {
            $key = (int) $row->product_id . '-' . (int) $row->product_attribute_id;
            if (!$pairKeys->has($key)) {
                continue;
            }

            $outstanding = max(0, (int) $row->qty_billed - (int) $row->qty_received_real);
            if ($outstanding <= 0) {
                continue;
            }

            if (!isset($incoming[$key])) {
                $incoming[$key] = ['quantity' => 0, 'shipment_id' => null, 'eta' => null];
            }
            $incoming[$key]['quantity'] += $outstanding;

            if (!empty($row->shipment_id) && !empty($row->eta_date)
                && ($incoming[$key]['eta'] === null || $row->eta_date < $incoming[$key]['eta'])) {
                $incoming[$key]['eta'] = (string) $row->eta_date;
                $incoming[$key]['shipment_id'] = (int) $row->shipment_id;
            }
        }

        return $incoming;
    }
    private function computeEtaOrOutOfStock(string $reference): array
    {
        $product = product::where('reference', $reference)->first();
        $attr = null;

        if ($product) {
            $idProduct = (int) $product->id_product;
            $idProductAttr = 0;
        } else {
            $attr = product_attribute::where('reference', $reference)->first();
            if (!$attr) {
                return ['status' => 'not_found', 'quantity' => 'OUT OF STOCK'];
            }

            $idProduct = (int) $attr->id_product;
            $idProductAttr = (int) $attr->id_product_attribute;
            $product = product::where('id_product', $idProduct)->first();
        }

        if (!$product) {
            return ['status' => 'not_found', 'quantity' => 'OUT OF STOCK'];
        }

        $hasCombinations = product_attribute::where('id_product', $idProduct)->count() > 0;
        if ($hasCombinations && $idProductAttr === 0 && (string) $product->reference === $reference) {
            return ['status' => 'not_found', 'quantity' => 'OUT OF STOCK'];
        }

        $qty = $this->getPrestashopStockQty($idProduct, $idProductAttr);
        if ($qty > 0) {
            return ['status' => 'in_stock', 'quantity' => $qty];
        }

        $packInfo = pack::availablePackQty($idProduct);
        $isPack = is_array($packInfo) && !empty($packInfo['is_pack']) && $packInfo['is_pack'] === true;

        if (!$isPack) {
            $incoming = $this->getOmsIncomingForProduct($idProduct, $idProductAttr);
            if ($incoming['quantity'] > 0 && $incoming['eta']) {
                return [
                    'status' => 'eta',
                    'eta' => $incoming['eta'],
                    'quantity' => $incoming['quantity'],
                    'shipment_id' => $incoming['shipment_id'],
                ];
            }

            return ['status' => 'out_of_stock', 'quantity' => 'OUT OF STOCK'];
        }

        $components = $packInfo['components'] ?? [];
        if (empty($components)) {
            return ['status' => 'out_of_stock', 'quantity' => 'OUT OF STOCK'];
        }

        $expectedPacks = null;
        $etaCandidates = [];

        foreach ($components as $component) {
            $idCompProduct = (int) ($component['id_product'] ?? 0);
            $idCompAttr = (int) ($component['id_product_attribute'] ?? 0);
            $qtyInPack = max((int) ($component['qty_in_pack'] ?? 1), 1);

            $compStock = array_key_exists('stock', $component)
                ? (int) $component['stock']
                : $this->getPrestashopStockQty($idCompProduct, $idCompAttr);

            $incoming = $this->getOmsIncomingForProduct($idCompProduct, $idCompAttr);
            $incomingQty = (int) ($incoming['quantity'] ?? 0);
            if (!empty($incoming['eta'])) {
                $etaCandidates[] = $incoming['eta'];
            }

            $possiblePacks = (int) floor(($compStock + $incomingQty) / $qtyInPack);
            $expectedPacks = is_null($expectedPacks) ? $possiblePacks : min($expectedPacks, $possiblePacks);
        }

        $expectedPacks = $expectedPacks ?? 0;
        if ($expectedPacks <= 0) {
            return ['status' => 'out_of_stock', 'quantity' => 'OUT OF STOCK'];
        }

        if (!empty($etaCandidates)) {
            sort($etaCandidates);
            return [
                'status' => 'eta',
                'eta' => (string) $etaCandidates[0],
                'quantity' => $expectedPacks,
                'is_pack' => true,
            ];
        }

        return ['status' => 'out_of_stock', 'quantity' => 'OUT OF STOCK'];
    }

    private function resolveRequestedProduct(Request $request): array
    {
        $idProduct = 0;
        $idProductAttr = 0;
        $product = null;
        $reference = trim((string) $request->reference);

        if ($reference !== '') {
            $product = product::where('reference', $reference)->first();
            if ($product) {
                $idProduct = (int) $product->id_product;
                $idProductAttr = 0;
            } else {
                $attr = product_attribute::where('reference', $reference)->first();
                if ($attr) {
                    $idProduct = (int) $attr->id_product;
                    $idProductAttr = (int) $attr->id_product_attribute;
                    $product = product::where('id_product', $idProduct)->first();
                }
            }
        } else {
            $idProduct = (int) $request->id_product;
            $idProductAttr = (int) $request->id_product_attribute;
            $product = product::where('id_product', $idProduct)->first();
            if ($product && $reference === '') {
                $reference = $idProductAttr > 0
                    ? (string) optional(product_attribute::where('id_product_attribute', $idProductAttr)->first())->reference
                    : (string) $product->reference;
            }
        }

        return [$idProduct, $idProductAttr, $product, $reference];
    }

    private function getPrestashopStockQty(int $idProduct, int $idProductAttr = 0): int
    {
        $stock = stock_available::where('id_product', $idProduct)
            ->where('id_product_attribute', $idProductAttr)
            ->first();

        return $stock ? (int) $stock->quantity : 0;
    }

    private function getOmsIncomingForProduct(int $idProduct, int $idProductAttr = 0): array
    {
        $rows = DB::table('oms_billed_order_lines as bol')
            ->join('oms_billed_orders as bo', 'bo.id', '=', 'bol.billed_order_id')
            ->join('oms_supplier_invoices as si', 'si.id', '=', 'bo.supplier_invoice_id')
            ->leftJoin('shipping_erp as se', 'se.id_erp', '=', 'si.id')
            ->leftJoin('shipping as s', 's.id', '=', 'se.id_shipping')
            ->leftJoin(DB::raw('(
                SELECT sd.id_shipping, MAX(sd.date) as eta_date
                FROM shipping_delay sd
                GROUP BY sd.id_shipping
            ) as eta_map'), 'eta_map.id_shipping', '=', 's.id')
            ->leftJoin(DB::raw('(
                SELECT rl.billed_order_line_id, SUM(rl.qty_received) as qty_received_sum
                FROM oms_reception_lines rl
                GROUP BY rl.billed_order_line_id
            ) as rl_sum'), 'rl_sum.billed_order_line_id', '=', 'bol.id')
            ->where('bol.product_id', $idProduct)
            ->where(function ($query) use ($idProductAttr) {
                if ($idProductAttr > 0) {
                    $query->where('bol.product_attribute_id', $idProductAttr);
                } else {
                    $query->where(function ($sub) {
                        $sub->whereNull('bol.product_attribute_id')
                            ->orWhere('bol.product_attribute_id', 0);
                    });
                }
            })
            ->where(function ($query) {
                $query->whereNull('si.status')
                    ->orWhere('si.status', '!=', 'cancelled');
            })
            ->selectRaw('
                bol.id,
                bol.qty_billed,
                COALESCE(rl_sum.qty_received_sum, bol.qty_received, 0) as qty_received_real,
                s.id as shipment_id,
                eta_map.eta_date as eta_date
            ')
            ->get();

        $quantity = 0;
        $selectedShipmentId = null;
        $selectedEta = null;

        foreach ($rows as $row) {
            $outstanding = max(0, (int) $row->qty_billed - (int) $row->qty_received_real);
            if ($outstanding <= 0) {
                continue;
            }

            $quantity += $outstanding;

            if (!empty($row->shipment_id) && !empty($row->eta_date)) {
                if ($selectedEta === null || $row->eta_date < $selectedEta) {
                    $selectedEta = (string) $row->eta_date;
                    $selectedShipmentId = (int) $row->shipment_id;
                }
            }
        }

        return [
            'quantity' => $quantity,
            'shipment_id' => $selectedShipmentId,
            'eta' => $selectedEta,
        ];
    }
}
