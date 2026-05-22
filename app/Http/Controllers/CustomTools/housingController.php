<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class housingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return View::make('customTools/housing/index')->with([
            'hideHeader' => true,
            'canEditLogistics' => $this->canEditLogisticsFields(),
            'canEditAdminFields' => $this->isAdminUser(),
        ]);
    }

    /**
     * Legacy compatibility.
     * Keeps the original route alive but internally uses the new location update flow.
     */
    public function saveData(Request $request): JsonResponse
    {
        return $this->editLocation($request);
    }

    public function requestData(Request $request)
    {
        $validated = $request->validate([
            'barcode' => ['nullable', 'string', 'max:191'],
            'id_product' => ['nullable', 'integer', 'min:1'],
            'id_product_attribute' => ['nullable', 'integer', 'min:0'],
        ]);

        $search = trim((string) ($validated['barcode'] ?? ''));
        $idProduct = (int) ($validated['id_product'] ?? 0);
        $idProductAttribute = (int) ($validated['id_product_attribute'] ?? 0);

        if ($idProduct > 0) {
            $resolved = $this->resolveTarget($idProduct, $idProductAttribute);

            if (!$resolved) {
                return view('customTools/housing/info', [
                    'search' => $search,
                    'mode' => 'empty',
                    'product' => null,
                    'products' => collect(),
                    'canEditLogistics' => $this->canEditLogisticsFields(),
                    'canEditAdminFields' => $this->isAdminUser(),
                    'history' => collect(),
                    'orderSummary' => [],
                    'orderRows' => collect(),
                    'housing' => null,
                    'isHousingSearch' => false,
                ])->render();
            }

            $product = $this->decorateResolvedForView($resolved);

            return view('customTools/housing/info', [
                'search' => $search,
                'mode' => 'single',
                'product' => $product,
                'products' => collect(),
                'canEditLogistics' => $this->canEditLogisticsFields(),
                    'canEditAdminFields' => $this->isAdminUser(),
                'history' => $this->isAdminUser() ? $this->getHistory($product) : collect(),
                'orderSummary' => $this->getOrderSummary($product),
                'orderRows' => $this->getOrderRows($product),
                'housing' => null,
                'isHousingSearch' => false,
            ])->render();
        }

        if ($search === '') {
            return view('customTools/housing/info', [
                'search' => $search,
                'mode' => 'empty',
                'product' => null,
                'products' => collect(),
                'canEditLogistics' => $this->canEditLogisticsFields(),
                    'canEditAdminFields' => $this->isAdminUser(),
                'history' => collect(),
                'orderSummary' => [],
                'orderRows' => collect(),
                'housing' => null,
                'isHousingSearch' => false,
            ])->render();
        }

        $result = $this->findProducts($search);

        return view('customTools/housing/info', [
            'search' => $search,
            'mode' => $result['mode'],
            'product' => $result['product'],
            'products' => $result['products'],
            'canEditLogistics' => $this->canEditLogisticsFields(),
                    'canEditAdminFields' => $this->isAdminUser(),
            'history' => ($this->isAdminUser() && $result['product']) ? $this->getHistory($result['product']) : collect(),
            'orderSummary' => $result['product'] ? $this->getOrderSummary($result['product']) : [],
            'orderRows' => $result['product'] ? $this->getOrderRows($result['product']) : collect(),
            'housing' => $result['housing'] ?? null,
            'isHousingSearch' => $result['is_housing_search'] ?? false,
        ])->render();
    }

    public function editLocation(Request $request): JsonResponse
    {
        abort_unless($this->canEditLogisticsFields(), 403);

        $validated = $request->validate([
            'id_product' => ['required', 'integer', 'min:1'],
            'id_product_attribute' => ['nullable', 'integer', 'min:0'],
            'stand' => ['required', 'string', 'max:120'],
            'search_term' => ['nullable', 'string', 'max:191'],
        ]);

        $resolved = $this->resolveTarget((int) $validated['id_product'], (int) ($validated['id_product_attribute'] ?? 0));
        if (!$resolved) {
            return response()->json(['ok' => false, 'message' => 'Product not found.'], 404);
        }

        $newLocation = trim((string) $validated['stand']);
        $changes = [];

        if ($resolved['type'] === 'attribute') {
            $oldHousing = $this->getAttributeHousing((int) $resolved['attribute']->id_product_attribute);
            if ($oldHousing !== $newLocation) {
                $this->setAttributeHousing(
                    (int) $resolved['product']->id_product,
                    (int) $resolved['attribute']->id_product_attribute,
                    $newLocation
                );
                $resolved['attribute']->housing = $newLocation;

                $changes[] = [
                    'field_name' => 'housing',
                    'old_value' => $oldHousing,
                    'new_value' => $newLocation,
                ];
            }
        } else {
            $oldLocation = (string) ($resolved['product']->location ?? '');

            if ($oldLocation !== $newLocation) {
                $resolved['product']->location = $newLocation;
                $resolved['product']->save();

                $changes[] = [
                    'field_name' => 'location',
                    'old_value' => $oldLocation,
                    'new_value' => $newLocation,
                ];
            }
        }

        if (empty($changes)) {
            return response()->json(['ok' => true, 'message' => 'No changes detected.']);
        }

        $this->storeHistoryBatch($resolved, 'update_location', $changes, [
            'search_term' => $validated['search_term'] ?? null,
        ]);

        return response()->json(['ok' => true, 'message' => 'Location updated successfully.']);
    }

    public function editMeasures(Request $request): JsonResponse
    {
        abort_unless($this->canEditLogisticsFields(), 403);

        $validated = $request->validate([
            'id_product' => ['required', 'integer', 'min:1'],
            'id_product_attribute' => ['nullable', 'integer', 'min:0'],
            'weight' => ['required', 'numeric', 'min:0'],
            'width' => ['required', 'numeric', 'min:0'],
            'height' => ['required', 'numeric', 'min:0'],
            'depth' => ['required', 'numeric', 'min:0'],
            'search_term' => ['nullable', 'string', 'max:191'],
        ]);

        $resolved = $this->resolveTarget((int) $validated['id_product'], (int) ($validated['id_product_attribute'] ?? 0));
        if (!$resolved) {
            return response()->json(['ok' => false, 'message' => 'Product not found.'], 404);
        }

        $target = $resolved['product'];
        $changes = [];

        foreach (['weight', 'width', 'height', 'depth'] as $field) {
            $newValue = $this->normalizeDecimal($validated[$field]);
            $oldValue = $this->normalizeDecimal($target->{$field});

            if ($oldValue !== $newValue) {
                $target->{$field} = $validated[$field];
                $changes[] = [
                    'field_name' => $field,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                ];
            }
        }

        if (empty($changes)) {
            return response()->json(['ok' => true, 'message' => 'No changes detected.']);
        }

        $target->save();

        $this->storeHistoryBatch($resolved, 'update_measures', $changes, [
            'search_term' => $validated['search_term'] ?? null,
            'stored_at' => 'product_level',
        ]);

        return response()->json(['ok' => true, 'message' => 'Measures updated successfully.']);
    }

    public function editReference(Request $request): JsonResponse
    {
        abort_unless($this->isAdminUser(), 403);

        $validated = $request->validate([
            'id_product' => ['required', 'integer', 'min:1'],
            'id_product_attribute' => ['nullable', 'integer', 'min:0'],
            'reference' => ['required', 'string', 'max:64'],
            'search_term' => ['nullable', 'string', 'max:191'],
        ]);

        $resolved = $this->resolveTarget((int) $validated['id_product'], (int) ($validated['id_product_attribute'] ?? 0));
        if (!$resolved) {
            return response()->json(['ok' => false, 'message' => 'Product not found.'], 404);
        }

        $newReference = trim((string) $validated['reference']);
        $target = $resolved['type'] === 'attribute' ? $resolved['attribute'] : $resolved['product'];
        $oldReference = trim((string) ($target->reference ?? ''));

        if ($oldReference === $newReference) {
            return response()->json(['ok' => true, 'message' => 'No changes detected.']);
        }

        $target->reference = $newReference;
        $target->save();

        $this->storeHistoryBatch($resolved, 'update_reference', [[
            'field_name' => 'reference',
            'old_value' => $oldReference,
            'new_value' => $newReference,
        ]], [
            'search_term' => $validated['search_term'] ?? null,
        ]);

        return response()->json(['ok' => true, 'message' => 'Reference updated successfully.']);
    }

    public function editEan13(Request $request): JsonResponse
    {
        abort_unless($this->canEditLogisticsFields(), 403);

        $validated = $request->validate([
            'id_product' => ['required', 'integer', 'min:1'],
            'id_product_attribute' => ['nullable', 'integer', 'min:0'],
            'ean13' => ['required', 'string', 'max:32'],
            'search_term' => ['nullable', 'string', 'max:191'],
        ]);

        $resolved = $this->resolveTarget((int) $validated['id_product'], (int) ($validated['id_product_attribute'] ?? 0));
        if (!$resolved) {
            return response()->json(['ok' => false, 'message' => 'Product not found.'], 404);
        }

        $newEan13 = trim((string) $validated['ean13']);
        $target = $resolved['type'] === 'attribute' ? $resolved['attribute'] : $resolved['product'];
        $oldEan13 = trim((string) ($target->ean13 ?? ''));

        if ($oldEan13 === $newEan13) {
            return response()->json(['ok' => true, 'message' => 'No changes detected.']);
        }

        $target->ean13 = $newEan13;
        $target->save();

        $this->storeHistoryBatch($resolved, 'update_ean13', [[
            'field_name' => 'ean13',
            'old_value' => $oldEan13,
            'new_value' => $newEan13,
        ]], [
            'search_term' => $validated['search_term'] ?? null,
        ]);

        return response()->json(['ok' => true, 'message' => 'EAN13 updated successfully.']);
    }

    public function editStock(Request $request): JsonResponse
    {
        abort_unless($this->isAdminUser(), 403);

        $validated = $request->validate([
            'id_product' => ['required', 'integer', 'min:1'],
            'id_product_attribute' => ['nullable', 'integer', 'min:0'],
            'stock' => ['required', 'integer'],
            'search_term' => ['nullable', 'string', 'max:191'],
        ]);

        $resolved = $this->resolveTarget((int) $validated['id_product'], (int) ($validated['id_product_attribute'] ?? 0));
        if (!$resolved) {
            return response()->json(['ok' => false, 'message' => 'Product not found.'], 404);
        }

        $stockRow = $resolved['type'] === 'attribute'
            ? optional($resolved['attribute']->stock)->first()
            : optional($resolved['product']->stock)->first();

        if (!$stockRow) {
            return response()->json(['ok' => false, 'message' => 'Stock row not found.'], 404);
        }

        $oldStock = (int) $stockRow->quantity;
        $newStock = (int) $validated['stock'];

        if ($oldStock === $newStock) {
            return response()->json(['ok' => true, 'message' => 'No changes detected.']);
        }

        $stockRow->quantity = $newStock;
        $stockRow->save();

        $this->storeHistoryBatch($resolved, 'update_stock', [[
            'field_name' => 'stock',
            'old_value' => (string) $oldStock,
            'new_value' => (string) $newStock,
        ]], [
            'search_term' => $validated['search_term'] ?? null,
        ]);

        return response()->json(['ok' => true, 'message' => 'Stock updated successfully.']);
    }


    public function editStockArrive(Request $request): JsonResponse
    {
        abort_unless($this->isAdminUser(), 403);

        $validated = $request->validate([
            'id_product' => ['required', 'integer', 'min:1'],
            'id_product_attribute' => ['nullable', 'integer', 'min:0'],
            'stock_arrive' => ['required', 'numeric'],
            'search_term' => ['nullable', 'string', 'max:191'],
        ]);

        $resolved = $this->resolveTarget((int) $validated['id_product'], (int) ($validated['id_product_attribute'] ?? 0));
        if (!$resolved) {
            return response()->json(['ok' => false, 'message' => 'Product not found.'], 404);
        }

        [$targetModel, $fieldName] = $this->resolveQuantityArriveTarget($resolved);

        if (!$targetModel || !$fieldName) {
            return response()->json(['ok' => false, 'message' => 'Stock arrive field not available on this record.'], 422);
        }

        $oldValue = $this->normalizeDecimal($targetModel->{$fieldName} ?? 0);
        $newValue = $this->normalizeDecimal($validated['stock_arrive']);

        if ($oldValue === $newValue) {
            return response()->json(['ok' => true, 'message' => 'No changes detected.']);
        }

        $targetModel->{$fieldName} = $validated['stock_arrive'];
        $targetModel->save();

        $this->storeHistoryBatch($resolved, 'update_stock_arrive', [[
            'field_name' => 'stock_arrive',
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]], [
            'search_term' => $validated['search_term'] ?? null,
            'stored_at' => $resolved['type'] === 'attribute' && $fieldName === 'quantity_arrive' ? 'attribute_level' : 'product_level',
        ]);

        return response()->json(['ok' => true, 'message' => 'Stock arrive updated successfully.']);
    }

    private function findProducts(string $search): array
    {
        $search = trim($search);

        if ($this->isHousingCode($search)) {
            $matches = collect()
                ->merge($this->queryAttributeHousing($search))
                ->merge($this->queryProductsBy('location', $search))
                ->unique(fn ($item) => $item['row_key'])
                ->values();

            return [
                'mode' => 'housing_list',
                'product' => null,
                'products' => $matches->map(fn ($item) => $this->decorateForList($item)),
                'housing' => $search,
                'is_housing_search' => true,
            ];
        }

        $matches = collect()
            ->merge($this->queryAttributesBy('ean13', $search))
            ->merge($this->queryAttributesBy('reference', $search))
            ->merge($this->queryProductsBy('ean13', $search))
            ->merge($this->queryProductsBy('reference', $search))
            ->unique(fn ($item) => $item['row_key'])
            ->values();

        if ($matches->count() === 1) {
            $product = $this->decorateForView($matches->first());

            return [
                'mode' => 'single',
                'product' => $product,
                'products' => collect(),
                'housing' => null,
                'is_housing_search' => false,
            ];
        }

        if ($matches->count() > 1) {
            return [
                'mode' => 'multiple',
                'product' => null,
                'products' => $matches->map(fn ($item) => $this->decorateForList($item)),
                'housing' => null,
                'is_housing_search' => false,
            ];
        }

        return [
            'mode' => 'empty',
            'product' => null,
            'products' => collect(),
            'housing' => null,
            'is_housing_search' => false,
        ];
    }

    private function isHousingCode(string $value): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9]{2}-[A-Za-z0-9]{2}-[A-Za-z0-9]{2}$/', trim($value));
    }
    private function queryAttributesBy(string $field, string $value): Collection
    {
        if (!$this->prestashopColumnExists('product_attribute', $field)) {
            return collect();
        }

        return product_attribute::with(['product', 'stock'])
            ->where($field, $value)
            ->limit(25)
            ->get()
            ->map(function ($attribute) {
                $attribute->housing = $this->getAttributeHousing((int) $attribute->id_product_attribute);

                return [
                    'type' => 'attribute',
                    'row_key' => 'a-' . $attribute->id_product . '-' . $attribute->id_product_attribute,
                    'item' => $attribute,
                ];
            });
    }

    private function queryAttributeHousing(string $value): Collection
    {
        if ($this->prestashopColumnExists('product_attribute', 'housing')) {
            return $this->queryAttributesBy('housing', $value);
        }

        $customTable = $this->psPrefix() . 'custom_product_attribute';
        if (!Schema::connection('mysql2')->hasTable($customTable) || !Schema::connection('mysql2')->hasColumn($customTable, 'location')) {
            return collect();
        }

        $attributeIds = DB::connection('mysql2')
            ->table($customTable)
            ->where('location', $value)
            ->limit(25)
            ->pluck('id_product_attribute')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($attributeIds)) {
            return collect();
        }

        return product_attribute::with(['product', 'stock'])
            ->whereIn('id_product_attribute', $attributeIds)
            ->get()
            ->map(function ($attribute) {
                $attribute->housing = $this->getAttributeHousing((int) $attribute->id_product_attribute);

                return [
                    'type' => 'attribute',
                    'row_key' => 'a-' . $attribute->id_product . '-' . $attribute->id_product_attribute,
                    'item' => $attribute,
                ];
            });
    }

    private function queryProductsBy(string $field, string $value): Collection
    {
        if (!$this->prestashopColumnExists('product', $field)) {
            return collect();
        }

        return product::with(['stock'])
            ->where($field, $value)
            ->limit(25)
            ->get()
            ->map(function ($product) {
                return [
                    'type' => 'product',
                    'row_key' => 'p-' . $product->id_product . '-0',
                    'item' => $product,
                ];
            });
    }

    private function resolveTarget(int $idProduct, int $idProductAttribute = 0): ?array
    {
        $productModel = product::find($idProduct);
        if (!$productModel) {
            return null;
        }

        if ($idProductAttribute > 0) {
            $attributeModel = product_attribute::with(['product', 'stock'])
                ->where('id_product', $idProduct)
                ->where('id_product_attribute', $idProductAttribute)
                ->first();

            if (!$attributeModel) {
                return null;
            }

            $attributeModel->housing = $this->getAttributeHousing((int) $attributeModel->id_product_attribute);

            return [
                'type' => 'attribute',
                'product' => $productModel,
                'attribute' => $attributeModel,
            ];
        }

        $productModel->load('stock');

        return [
            'type' => 'product',
            'product' => $productModel,
            'attribute' => null,
        ];
    }

    private function decorateResolvedForView(array $resolved)
    {
        $item = $resolved['type'] === 'attribute' ? $resolved['attribute'] : $resolved['product'];

        return $this->decorateForView([
            'type' => $resolved['type'],
            'item' => $item,
        ]);
    }

    private function decorateForView(array $match)
    {
        $item = $match['item'];
        $isAttribute = $match['type'] === 'attribute';
        $baseProduct = $isAttribute ? $item->product : $item;
        $stockRow = optional($item->stock)->first();

        $productLocation = (string) ($baseProduct->location ?? '');
        $attributeHousing = $isAttribute ? $this->getAttributeHousing((int) $item->id_product_attribute) : '';

        return (object) [
            'id_product' => (int) $baseProduct->id_product,
            'id_product_attribute' => $isAttribute ? (int) $item->id_product_attribute : 0,
            'entity_type' => $isAttribute ? 'attribute' : 'product',
            'name' => $this->getProductName((int) $baseProduct->id_product),
            'reference' => (string) ($item->reference ?? $baseProduct->reference ?? ''),
            'ean13' => (string) ($item->ean13 ?? $baseProduct->ean13 ?? ''),
            'product_location' => $productLocation,
            'attribute_housing' => $attributeHousing,
            'operational_location' => $isAttribute ? $attributeHousing : $productLocation,
            'location_source' => $isAttribute ? 'attribute_housing' : 'product_location',
            'stock' => (int) ($stockRow->quantity ?? 0),
            'quantity_arrive' => $this->getQuantityArriveValue($baseProduct, $item, $isAttribute),
            'weight' => (string) ($baseProduct->weight ?? 0),
            'width' => (string) ($baseProduct->width ?? 0),
            'height' => (string) ($baseProduct->height ?? 0),
            'depth' => (string) ($baseProduct->depth ?? 0),
        ];
    }

    private function decorateForList(array $match): array
    {
        $item = $match['item'];
        $isAttribute = $match['type'] === 'attribute';
        $baseProduct = $isAttribute ? $item->product : $item;
        $stockRow = optional($item->stock)->first();

        $productLocation = (string) ($baseProduct->location ?? '');
        $attributeHousing = $isAttribute ? $this->getAttributeHousing((int) $item->id_product_attribute) : '';

        $decorated = [
            'id_product' => (int) $baseProduct->id_product,
            'id_product_attribute' => $isAttribute ? (int) $item->id_product_attribute : 0,
            'entity_type' => $isAttribute ? 'attribute' : 'product',
            'name' => $this->getProductName((int) $baseProduct->id_product),
            'reference' => (string) ($item->reference ?? $baseProduct->reference ?? ''),
            'ean13' => (string) ($item->ean13 ?? $baseProduct->ean13 ?? ''),
            'product_location' => $productLocation,
            'attribute_housing' => $attributeHousing,
            'operational_location' => $isAttribute ? $attributeHousing : $productLocation,
            'location_source' => $isAttribute ? 'attribute_housing' : 'product_location',
            'stock' => (int) ($stockRow->quantity ?? 0),
        ];

        $decorated['order_badges'] = $this->getOrderSummary((object) $decorated);

        return $decorated;
    }

    private function getProductName(int $idProduct): string
    {
        $prefix = $this->psPrefix();
        $langTable = $prefix . 'product_lang';

        if (!Schema::connection('mysql2')->hasTable($langTable)) {
            return '';
        }

        $query = DB::connection('mysql2')->table($langTable)->where('id_product', $idProduct);

        if (Schema::connection('mysql2')->hasColumn($langTable, 'id_shop')) {
            $query->orderBy('id_shop');
        }

        if (Schema::connection('mysql2')->hasColumn($langTable, 'id_lang')) {
            $query->orderBy('id_lang');
        }

        return (string) ($query->value('name') ?? '');
    }

    private function resolveQuantityArriveTarget(array $resolved): array
    {
        if ($resolved['type'] === 'attribute' && isset($resolved['attribute']->quantity_arrive)) {
            return [$resolved['attribute'], 'quantity_arrive'];
        }

        if (isset($resolved['product']->quantity_arrive)) {
            return [$resolved['product'], 'quantity_arrive'];
        }

        return [null, null];
    }

    private function getQuantityArriveValue($productModel, $item, bool $isAttribute)
    {
        if ($isAttribute && isset($item->quantity_arrive)) {
            return $item->quantity_arrive;
        }

        if (isset($productModel->quantity_arrive)) {
            return $productModel->quantity_arrive;
        }

        return 0;
    }

    private function getOrderSummary(object $product): array
    {
        $rows = $this->getOrderRows($product);
        $palette = $this->trackedOrderStates();
        $summary = [];

        foreach ($palette as $key => $state) {
            $summary[$key] = [
                'label' => $state['label'],
                'color' => $state['color'],
                'count' => 0,
            ];
        }

        foreach ($rows as $row) {
            $key = $this->matchTrackedStateKey($row->state_id, $row->state_name);
            if ($key && isset($summary[$key])) {
                $summary[$key]['count']++;
            }
        }

        return $summary;
    }

    private function getOrderRows(object $product): Collection
    {
        $prefix = $this->psPrefix();
        $orderTable = $prefix . 'orders';
        $detailTable = $prefix . 'order_detail';
        $stateLangTable = $prefix . 'order_state_lang';

        foreach ([$orderTable, $detailTable, $stateLangTable] as $table) {
            if (!Schema::connection('mysql2')->hasTable($table)) {
                return collect();
            }
        }

        $tracked = $this->trackedOrderStates();
        $stateIds = collect($tracked)
            ->pluck('id')
            ->filter(fn ($id) => !is_null($id))
            ->values()
            ->all();

        $warrantyAliases = collect($tracked['warranty']['aliases'] ?? [])->map(fn ($item) => mb_strtolower((string) $item))->all();

        $query = DB::connection('mysql2')
            ->table($detailTable . ' as od')
            ->join($orderTable . ' as o', 'o.id_order', '=', 'od.id_order')
            ->leftJoin($stateLangTable . ' as osl', function ($join) {
                $join->on('osl.id_order_state', '=', 'o.current_state');
            })
            ->select([
                'o.id_order',
                'o.reference as order_reference',
                'o.current_state as state_id',
                'osl.name as state_name',
                'od.product_id',
                'od.product_attribute_id',
                'od.product_reference',
                'od.product_ean13',
                'od.product_name',
                'od.product_quantity',
                'od.product_quantity_in_stock',
                'od.product_quantity_refunded',
                'od.product_quantity_return',
            ])
            ->where('od.product_id', (int) $product->id_product)
            ->where(function ($builder) use ($product) {
                if ((int) ($product->id_product_attribute ?? 0) > 0) {
                    $builder->where('od.product_attribute_id', (int) $product->id_product_attribute);
                } else {
                    $builder->where(function ($q) {
                        $q->whereNull('od.product_attribute_id')
                            ->orWhere('od.product_attribute_id', 0);
                    });
                }
            })
            ->where(function ($builder) use ($stateIds, $warrantyAliases) {
                if (!empty($stateIds)) {
                    $builder->whereIn('o.current_state', $stateIds);
                }

                foreach ($warrantyAliases as $index => $alias) {
                    if ($index === 0 && empty($stateIds)) {
                        $builder->whereRaw('LOWER(osl.name) like ?', ['%' . $alias . '%']);
                    } else {
                        $builder->orWhereRaw('LOWER(osl.name) like ?', ['%' . $alias . '%']);
                    }
                }
            })
            ->groupBy([
                'o.id_order',
                'o.reference',
                'o.current_state',
                'osl.name',
                'od.product_id',
                'od.product_attribute_id',
                'od.product_reference',
                'od.product_ean13',
                'od.product_name',
                'od.product_quantity',
                'od.product_quantity_in_stock',
                'od.product_quantity_refunded',
                'od.product_quantity_return',
            ])
            ->orderByDesc('o.id_order')
            ->limit(50)
            ->get();

        return $query->map(function ($row) {
            $stateKey = $this->matchTrackedStateKey($row->state_id, $row->state_name);
            $palette = $this->trackedOrderStates();
            $row->state_key = $stateKey;
            $row->state_color = $stateKey && isset($palette[$stateKey]) ? $palette[$stateKey]['color'] : '#6c757d';
            $row->state_label = $stateKey && isset($palette[$stateKey]) ? $palette[$stateKey]['label'] : ($row->state_name ?: 'Unknown');
            return $row;
        });
    }

    private function trackedOrderStates(): array
    {
        return [
            'packing' => [
                'id' => 3,
                'label' => 'Packing in progress',
                'color' => '#048DCD',
                'aliases' => ['packing in progress'],
            ],
            'backorder' => [
                'id' => 15,
                'label' => 'On Backorder',
                'color' => '#F78E1F',
                'aliases' => ['on backorder', 'backorder'],
            ],
            'warranty' => [
                'id' => null,
                'label' => 'Warranty',
                'color' => 'pink',
                'aliases' => ['warranty'],
            ],
            'waiting_info' => [
                'id' => 30,
                'label' => 'Waiting info',
                'color' => '#acacac',
                'aliases' => ['waiting info'],
            ],
        ];
    }

    private function matchTrackedStateKey($stateId, ?string $stateName): ?string
    {
        $stateName = mb_strtolower(trim((string) $stateName));

        foreach ($this->trackedOrderStates() as $key => $state) {
            if (!is_null($state['id']) && (int) $state['id'] === (int) $stateId) {
                return $key;
            }

            foreach ($state['aliases'] as $alias) {
                if ($stateName !== '' && str_contains($stateName, mb_strtolower((string) $alias))) {
                    return $key;
                }
            }
        }

        return null;
    }

    private function prestashopColumnExists(string $tableSuffix, string $column): bool
    {
        return Schema::connection('mysql2')->hasColumn($this->psPrefix() . $tableSuffix, $column);
    }

    private function getAttributeHousing(int $idProductAttribute): string
    {
        if ($idProductAttribute <= 0) {
            return '';
        }

        if ($this->prestashopColumnExists('product_attribute', 'housing')) {
            return (string) (DB::connection('mysql2')
                ->table($this->psPrefix() . 'product_attribute')
                ->where('id_product_attribute', $idProductAttribute)
                ->value('housing') ?? '');
        }

        $customTable = $this->psPrefix() . 'custom_product_attribute';
        if (!Schema::connection('mysql2')->hasTable($customTable) || !Schema::connection('mysql2')->hasColumn($customTable, 'location')) {
            return '';
        }

        return (string) (DB::connection('mysql2')
            ->table($customTable)
            ->where('id_product_attribute', $idProductAttribute)
            ->value('location') ?? '');
    }

    private function setAttributeHousing(int $idProduct, int $idProductAttribute, string $housing): void
    {
        if ($this->prestashopColumnExists('product_attribute', 'housing')) {
            DB::connection('mysql2')
                ->table($this->psPrefix() . 'product_attribute')
                ->where('id_product_attribute', $idProductAttribute)
                ->update(['housing' => $housing]);

            return;
        }

        $customTable = $this->psPrefix() . 'custom_product_attribute';
        if (!Schema::connection('mysql2')->hasTable($customTable) || !Schema::connection('mysql2')->hasColumn($customTable, 'location')) {
            throw new \RuntimeException('Attribute housing field is not available.');
        }

        $exists = DB::connection('mysql2')
            ->table($customTable)
            ->where('id_product_attribute', $idProductAttribute)
            ->exists();

        if ($exists) {
            DB::connection('mysql2')
                ->table($customTable)
                ->where('id_product_attribute', $idProductAttribute)
                ->update([
                    'id_product' => $idProduct,
                    'location' => $housing,
                ]);

            return;
        }

        DB::connection('mysql2')
            ->table($customTable)
            ->insert([
                'id_product' => $idProduct,
                'id_product_attribute' => $idProductAttribute,
                'location' => $housing,
            ]);
    }

    private function psPrefix(): string
    {
        return (string) (env('DB2_prefix') ?: env('DB2_DB_prefix') ?: 'ps_');
    }

    public function bulkLookupProduct(Request $request): JsonResponse
    {
        abort_unless($this->canEditLogisticsFields(), 403);

        $validated = $request->validate([
            'scan' => ['required', 'string', 'max:191'],
        ]);

        $scan = trim((string) $validated['scan']);

        if ($this->isHousingCode($scan)) {
            return response()->json([
                'ok' => false,
                'message' => 'Scan a product EAN/reference here, not another housing code.',
            ], 422);
        }

        $matches = collect()
            ->merge($this->queryAttributesBy('ean13', $scan))
            ->merge($this->queryAttributesBy('reference', $scan))
            ->merge($this->queryProductsBy('ean13', $scan))
            ->merge($this->queryProductsBy('reference', $scan))
            ->unique(fn ($item) => $item['row_key'])
            ->values();

        if ($matches->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => 'Product not found for scan: ' . $scan,
            ], 404);
        }

        if ($matches->count() > 1) {
            return response()->json([
                'ok' => false,
                'message' => 'Multiple products found. Use a more specific EAN/reference.',
                'products' => $matches->map(fn ($item) => $this->decorateForList($item))->values(),
            ], 409);
        }

        return response()->json([
            'ok' => true,
            'product' => $this->decorateForList($matches->first()),
        ]);
    }

    public function bulkSaveHousing(Request $request): JsonResponse
    {
        abort_unless($this->canEditLogisticsFields(), 403);

        $validated = $request->validate([
            'housing' => ['required', 'string', 'max:20'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id_product' => ['required'],
            'products.*.id_product_attribute' => ['nullable'],
        ]);

        $housing = trim((string) $validated['housing']);

        if (!$this->isHousingCode($housing)) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid housing format. Expected XX-XX-XX.',
            ], 422);
        }

        $updated = 0;
        $skipped = 0;

        DB::connection('mysql2')->beginTransaction();

        try {
            foreach ($validated['products'] as $item) {
                $idProduct = (int) ($item['id_product'] ?? 0);
                $idProductAttribute = (int) ($item['id_product_attribute'] ?? 0);

                $resolved = $this->resolveTarget($idProduct, $idProductAttribute);

                if (!$resolved) {
                    $skipped++;
                    continue;
                }

                if ($resolved['type'] === 'attribute') {
                    $oldValue = $this->getAttributeHousing((int) $resolved['attribute']->id_product_attribute);

                    if ($oldValue === $housing) {
                        $skipped++;
                        continue;
                    }

                    $this->setAttributeHousing(
                        (int) $resolved['product']->id_product,
                        (int) $resolved['attribute']->id_product_attribute,
                        $housing
                    );
                    $resolved['attribute']->housing = $housing;

                    $this->storeHistoryBatch($resolved, 'bulk_update_housing', [[
                        'field_name' => 'housing',
                        'old_value' => $oldValue,
                        'new_value' => $housing,
                    ]], [
                        'bulk_housing' => $housing,
                    ]);
                } else {
                    $oldValue = (string) ($resolved['product']->location ?? '');

                    if ($oldValue === $housing) {
                        $skipped++;
                        continue;
                    }

                    $resolved['product']->location = $housing;
                    $resolved['product']->save();

                    $this->storeHistoryBatch($resolved, 'bulk_update_housing', [[
                        'field_name' => 'location',
                        'old_value' => $oldValue,
                        'new_value' => $housing,
                    ]], [
                        'bulk_housing' => $housing,
                    ]);
                }

                $updated++;
            }

            DB::connection('mysql2')->commit();

            return response()->json([
                'ok' => true,
                'message' => 'Bulk housing saved. Updated: ' . $updated . '. Skipped: ' . $skipped . '.',
                'updated' => $updated,
                'skipped' => $skipped,
            ]);
        } catch (\Throwable $e) {
            DB::connection('mysql2')->rollBack();

            return response()->json([
                'ok' => false,
                'message' => 'Error saving bulk housing.',
            ], 500);
        }
    }
    private function normalizeDecimal($value): string
    {
        return number_format((float) $value, 6, '.', '');
    }

    private function getHistory(object $product): Collection
    {
        if (!$this->isAdminUser()) {
            return collect();
        }

        if (!Schema::hasTable('housing_tool_history')) {
            return collect();
        }

        $numericFields = ['weight', 'width', 'height', 'depth', 'stock', 'stock_arrive', 'quantity_arrive'];

        return DB::table('housing_tool_history')
            ->where('id_product', (int) $product->id_product)
            ->where('id_product_attribute', (int) ($product->id_product_attribute ?? 0))
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(function ($row) use ($numericFields) {
                if (in_array((string) $row->field_name, $numericFields, true)) {
                    if (is_numeric($row->old_value)) {
                        $row->old_value = number_format((float) $row->old_value, 2, '.', '');
                    }
                    if (is_numeric($row->new_value)) {
                        $row->new_value = number_format((float) $row->new_value, 2, '.', '');
                    }
                }

                return $row;
            });
    }

    private function storeHistoryBatch(array $resolved, string $operation, array $changes, array $meta = []): void
    {
        if (!Schema::hasTable('housing_tool_history')) {
            return;
        }

        foreach ($changes as $change) {
            DB::table('housing_tool_history')->insert([
                'user_id' => optional(auth()->user())->id,
                'user_name' => optional(auth()->user())->name,
                'operation' => $operation,
                'id_product' => (int) $resolved['product']->id_product,
                'id_product_attribute' => (int) optional($resolved['attribute'])->id_product_attribute,
                'field_name' => $change['field_name'] ?? null,
                'old_value' => isset($change['old_value']) ? (string) $change['old_value'] : null,
                'new_value' => isset($change['new_value']) ? (string) $change['new_value'] : null,
                'search_term' => $meta['search_term'] ?? null,
                'meta' => !empty($meta) ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function canEditLogisticsFields(): bool
    {
        return (bool) auth()->check();
    }

    private function isAdminUser(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return strtolower(trim((string) ($user->role ?? ''))) === 'admin';
    }
}
