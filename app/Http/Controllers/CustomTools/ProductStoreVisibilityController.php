<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Services\Logs\LogService;
use App\Services\oms\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductStoreVisibilityController extends Controller
{
    private const VISIBILITIES = ['both', 'catalog', 'search', 'none'];
    private const PER_PAGE = 100;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $manufacturerId = max(0, (int) $request->query('manufacturer_id', 0));
        $prefix = $this->prefix();
        $asmShopId = $this->shopId('ASM');
        $asdShopId = $this->shopId('ASD');

        $manufacturers = DB::connection('mysql2')
            ->table($prefix . 'manufacturer as m')
            ->join($prefix . 'product as p', 'p.id_manufacturer', '=', 'm.id_manufacturer')
            ->leftJoin($prefix . 'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->whereExists(function ($query) use ($prefix, $asmShopId, $asdShopId) {
                $query->selectRaw('1')
                    ->from($prefix . 'product_shop as visible_ps')
                    ->whereColumn('visible_ps.id_product', 'p.id_product')
                    ->whereIn('visible_ps.id_shop', [$asmShopId, $asdShopId]);
            })
            ->where(function ($query) use ($prefix, $asmShopId, $asdShopId) {
                $query->whereRaw('COALESCE(cp.wmdeprecated, 0) <> 1')
                    ->orWhereExists(function ($stockQuery) use ($prefix, $asmShopId, $asdShopId) {
                        $stockQuery->selectRaw('1')
                            ->from($prefix . 'stock_available as available_stock')
                            ->whereColumn('available_stock.id_product', 'p.id_product')
                            ->whereIn('available_stock.id_shop', [0, $asmShopId, $asdShopId])
                            ->where('available_stock.quantity', '>', 0);
                    });
            })
            ->select('m.id_manufacturer', 'm.name')
            ->selectRaw('COUNT(DISTINCT p.id_product) AS product_count')
            ->groupBy('m.id_manufacturer', 'm.name')
            ->orderBy('m.name')
            ->get();

        $products = null;
        $selectedManufacturer = null;

        if ($manufacturerId > 0) {
            $selectedManufacturer = $manufacturers->first(
                fn ($manufacturer) => (int) $manufacturer->id_manufacturer === $manufacturerId
            );
            abort_unless($selectedManufacturer, 404);

            $products = DB::connection('mysql2')
                ->table($prefix . 'product as p')
                ->leftJoin($prefix . 'product_shop as asm_ps', function ($join) use ($asmShopId) {
                    $join->on('asm_ps.id_product', '=', 'p.id_product')
                        ->where('asm_ps.id_shop', $asmShopId);
                })
                ->leftJoin($prefix . 'product_shop as asd_ps', function ($join) use ($asdShopId) {
                    $join->on('asd_ps.id_product', '=', 'p.id_product')
                        ->where('asd_ps.id_shop', $asdShopId);
                })
                ->where(function ($query) use ($prefix, $asmShopId, $asdShopId) {
                    $query->whereRaw('COALESCE(cp.wmdeprecated, 0) <> 1')
                        ->orWhereExists(function ($stockQuery) use ($prefix, $asmShopId, $asdShopId) {
                            $stockQuery->selectRaw('1')
                                ->from($prefix . 'stock_available as available_stock')
                                ->whereColumn('available_stock.id_product', 'p.id_product')
                                ->whereIn('available_stock.id_shop', [0, $asmShopId, $asdShopId])
                                ->where('available_stock.quantity', '>', 0);
                        });
                })
                ->where('p.id_manufacturer', $manufacturerId)
                ->where(function ($query) {
                    $query->whereNotNull('asm_ps.id_product')
                        ->orWhereNotNull('asd_ps.id_product');
                })
                ->leftJoin($prefix . 'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
                ->select([
                    'p.id_product',
                    'p.reference',
                    'asm_ps.visibility as asm_visibility',
                    'asd_ps.visibility as asd_visibility',
                ])
                ->selectSub(function ($query) use ($prefix) {
                    $query->from($prefix . 'image as image_count')
                        ->whereColumn('image_count.id_product', 'p.id_product')
                        ->selectRaw('COUNT(*)');
                }, 'image_count')
                ->selectSub(function ($query) use ($prefix, $asdShopId, $asmShopId) {
                    $query->from($prefix . 'image as cover_image')
                        ->leftJoin($prefix . 'image_shop as cover_asd', function ($join) use ($asdShopId) {
                            $join->on('cover_asd.id_image', '=', 'cover_image.id_image')
                                ->where('cover_asd.id_shop', $asdShopId);
                        })
                        ->leftJoin($prefix . 'image_shop as cover_asm', function ($join) use ($asmShopId) {
                            $join->on('cover_asm.id_image', '=', 'cover_image.id_image')
                                ->where('cover_asm.id_shop', $asmShopId);
                        })
                        ->whereColumn('cover_image.id_product', 'p.id_product')
                        ->orderByRaw('CASE WHEN cover_asd.cover = 1 THEN 0 WHEN cover_asm.cover = 1 THEN 1 ELSE 2 END')
                        ->orderBy('cover_image.position')
                        ->orderBy('cover_image.id_image')
                        ->limit(1)
                        ->select('cover_image.id_image');
                }, 'cover_image_id')
                ->orderByRaw("CASE WHEN p.reference IS NULL OR TRIM(p.reference) = '' THEN 1 ELSE 0 END")
                ->orderBy('p.reference')
                ->orderBy('p.id_product')
                ->paginate(self::PER_PAGE)
                ->withQueryString();

            $products->getCollection()->transform(function ($product) {
                $product->cover_url = $this->coverUrl(
                    $product->cover_image_id ? (int) $product->cover_image_id : null
                );
                return $product;
            });
        }

        return View::make('customTools.productStoreVisibility.index', [
            'manufacturers' => $manufacturers,
            'manufacturerId' => $manufacturerId,
            'selectedManufacturer' => $selectedManufacturer,
            'products' => $products,
            'visibilities' => self::VISIBILITIES,
            'storeFrontUrls' => [
                'ASM' => $this->storeFrontUrl('ASM'),
                'ASD' => $this->storeFrontUrl('ASD'),
            ],
            'breadcrumbs' => [
                ['name' => trans('sales'), 'url' => route('sales.index')],
                ['name' => 'Product visibility', 'url' => route('sales.tools.product_visibility.index')],
            ],
            'actions' => [],
        ]);
    }

    public function update(Request $request, int $productId, string $store): JsonResponse
    {
        $validated = $request->validate([
            'visibility' => ['required', 'string', Rule::in(self::VISIBILITIES)],
        ]);

        $store = strtoupper($store);
        abort_unless(in_array($store, ['ASM', 'ASD'], true), 404);

        $prefix = $this->prefix();
        $shopId = $this->shopId($store);
        $current = DB::connection('mysql2')
            ->table($prefix . 'product_shop as ps')
            ->join($prefix . 'product as p', 'p.id_product', '=', 'ps.id_product')
            ->where('ps.id_product', $productId)
            ->where('ps.id_shop', $shopId)
            ->first(['ps.visibility', 'p.reference']);

        if (! $current) {
            return response()->json([
                'message' => "Product {$productId} is not assigned to {$store}.",
            ], 422);
        }

        $newVisibility = $validated['visibility'];

        if ($current->visibility !== $newVisibility) {
            DB::connection('mysql2')
                ->table($prefix . 'product_shop')
                ->where('id_product', $productId)
                ->where('id_shop', $shopId)
                ->update(['visibility' => $newVisibility]);

            LogService::create(
                'UPDATE',
                'PRODUCT_VISIBILITY',
                'info',
                implode(' | ', [
                    'id_product=' . $productId,
                    'reference=' . (trim((string) $current->reference) ?: 'n/a'),
                    'store=' . $store,
                    'id_shop=' . $shopId,
                    'visibility_from=' . $current->visibility,
                    'visibility_to=' . $newVisibility,
                ])
            );
        }

        return response()->json([
            'message' => "{$store} visibility updated.",
            'product_id' => $productId,
            'store' => $store,
            'visibility' => $newVisibility,
        ]);
    }

    public function exportNone(string $store, ExportService $exportService): StreamedResponse
    {
        $store = strtoupper($store);
        abort_unless(in_array($store, ['ASM', 'ASD'], true), 404);

        $prefix = $this->prefix();
        $shopId = $this->shopId($store);
        $headers = [
            'store',
            'id_product',
            'reference',
            'product',
            'brand',
            'active',
            'visibility',
            'images',
        ];

        $rows = DB::connection('mysql2')
            ->table($prefix . 'product as p')
            ->join($prefix . 'product_shop as ps', function ($join) use ($shopId) {
                $join->on('ps.id_product', '=', 'p.id_product')
                    ->where('ps.id_shop', $shopId);
            })
            ->leftJoin($prefix . 'product_lang as pl', function ($join) use ($shopId) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_shop', $shopId)
                    ->where('pl.id_lang', 1);
            })
            ->leftJoin($prefix . 'manufacturer as m', 'm.id_manufacturer', '=', 'p.id_manufacturer')
            ->where('ps.visibility', 'none')
            ->select([
                'p.id_product',
                'p.reference',
                'pl.name as product',
                'm.name as brand',
                'ps.active',
                'ps.visibility',
            ])
            ->selectRaw('? AS store', [$store])
            ->selectSub(function ($query) use ($prefix) {
                $query->from($prefix . 'image as image_count')
                    ->whereColumn('image_count.id_product', 'p.id_product')
                    ->selectRaw('COUNT(*)');
            }, 'images')
            ->orderByRaw("CASE WHEN p.reference IS NULL OR TRIM(p.reference) = '' THEN 1 ELSE 0 END")
            ->orderBy('p.reference')
            ->orderBy('p.id_product')
            ->cursor();

        return $exportService->streamXlsx(
            'products-visibility-none-' . strtolower($store) . '-' . now()->format('Y-m-d') . '.xlsx',
            $headers,
            $rows
        );
    }

    private function prefix(): string
    {
        return (string) env('DB2_DB_prefix', 'ps_');
    }

    private function shopId(string $store): int
    {
        return (int) config('allstars.stores.' . strtoupper($store) . '.id_shop');
    }

    private function coverUrl(?int $imageId): ?string
    {
        if (! $imageId) {
            return null;
        }

        $path = implode('/', str_split((string) $imageId));
        return rtrim((string) config('allstars.stores.ASD.base_url'), '/')
            . '/img/p/' . $path . '/' . $imageId . '.jpg';
    }

    private function storeFrontUrl(string $store): string
    {
        $shopUrl = DB::connection('mysql2')
            ->table($this->prefix() . 'shop_url')
            ->where('id_shop', $this->shopId($store))
            ->orderByDesc('main')
            ->first(['domain', 'domain_ssl', 'physical_uri', 'virtual_uri']);

        if (! $shopUrl) {
            return rtrim((string) config('allstars.stores.' . $store . '.base_url'), '/');
        }

        $domain = trim((string) ($shopUrl->domain_ssl ?: $shopUrl->domain));
        $path = trim((string) $shopUrl->physical_uri, '/');
        $virtualPath = trim((string) $shopUrl->virtual_uri, '/');
        $segments = array_values(array_filter([$path, $virtualPath]));

        return 'https://' . $domain . ($segments ? '/' . implode('/', $segments) : '');
    }
}
