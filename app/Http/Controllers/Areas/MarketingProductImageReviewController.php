<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class MarketingProductImageReviewController extends Controller
{
    private const PAGE_SIZE = 10;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $manufacturers = $this->manufacturerQuery()
            ->select('m.id_manufacturer', 'm.name')
            ->selectRaw('COUNT(DISTINCT p.id_product) AS product_count')
            ->groupBy('m.id_manufacturer', 'm.name')
            ->orderBy('m.name')
            ->get();

        $selectedManufacturerId = (int) $request->query('manufacturer_id', 0);

        if ($selectedManufacturerId > 0 && !$manufacturers->contains('id_manufacturer', $selectedManufacturerId)) {
            abort(404);
        }

        $selectedManufacturer = $selectedManufacturerId > 0
            ? $manufacturers->firstWhere('id_manufacturer', $selectedManufacturerId)
            : null;

        return View::make('areas.marketing.product-image-review', [
            'manufacturers' => $manufacturers,
            'selectedManufacturerId' => $selectedManufacturerId,
            'selectedManufacturer' => $selectedManufacturer,
            'breadcrumbs' => [
                ['name' => 'Studio', 'url' => route('marketing.index'), 'no_translation' => 1],
                ['name' => 'product_image_review', 'url' => route('marketing.product_images.index')],
            ],
            'actions' => [],
        ]);
    }
    public function products(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'manufacturer_id' => ['required', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $manufacturerId = (int) $validated['manufacturer_id'];
        $page = (int) ($validated['page'] ?? 1);
        abort_unless($this->manufacturerQuery()->where('m.id_manufacturer', $manufacturerId)->exists(), 404);

        $prefix = $this->prefix();
        $shopId = $this->shopId();
        $languageId = $this->englishLanguageId();
        $query = DB::connection('mysql2')->table($prefix.'product as p')
            ->join($prefix.'product_shop as ps', fn ($join) => $join->on('ps.id_product', '=', 'p.id_product')->where('ps.id_shop', $shopId))
            ->leftJoin($prefix.'product_lang as pl', fn ($join) => $join->on('pl.id_product', '=', 'p.id_product')->where('pl.id_shop', $shopId)->where('pl.id_lang', $languageId))
            ->leftJoin($prefix.'custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->where('p.id_manufacturer', $manufacturerId)
            ->select('p.id_product', 'p.reference', 'pl.name', 'pl.link_rewrite', 'cp.technical_image_id')
            ->orderByRaw("CASE WHEN p.reference IS NULL OR TRIM(p.reference) = '' THEN 1 ELSE 0 END")
            ->orderBy('p.reference')->orderBy('p.id_product');

        $total = (clone $query)->count();
        $products = $query->forPage($page, self::PAGE_SIZE)->get();
        $images = $this->imagesByProduct(
            $products->pluck('id_product')->map(fn ($id) => (int) $id)->all(),
            $products->mapWithKeys(fn ($product) => [
                (int) $product->id_product => trim((string) $product->link_rewrite),
            ])->all(),
            $products->mapWithKeys(fn ($product) => [(int) $product->id_product => (int) ($product->technical_image_id ?? 0)])->all()
        );

        return response()->json([
            'data' => $products->map(fn ($product) => [
                'id_product' => (int) $product->id_product,
                'reference' => trim((string) $product->reference) ?: "\u{2014}",
                'name' => trim((string) $product->name) ?: trans('messages.product_image_review_missing_english_name'),
                'front_url' => $this->frontProductUrl((int) $product->id_product),
                'images' => $images->get((int) $product->id_product, collect())->values()->all(),
            ])->values(),
            'meta' => [
                'page' => $page,
                'per_page' => self::PAGE_SIZE,
                'total' => $total,
                'loaded' => min($page * self::PAGE_SIZE, $total),
                'has_more' => $page * self::PAGE_SIZE < $total,
            ],
        ]);
    }

    public function setTechnicalImage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'image_id' => ['required', 'integer', 'min:1'],
        ]);

        $prefix = $this->prefix();
        $productId = (int) $data['product_id'];
        $imageId = (int) $data['image_id'];
        $belongsToProduct = DB::connection('mysql2')->table($prefix.'image')
            ->where('id_image', $imageId)
            ->where('id_product', $productId)
            ->exists();

        abort_unless($belongsToProduct, 422, 'The selected image does not belong to this product.');

        DB::connection('mysql2')->table($prefix.'custom_product')->updateOrInsert(
            ['id_product' => $productId],
            ['technical_image_id' => $imageId]
        );

        return response()->json(['success' => true, 'technical_image_id' => $imageId]);
    }

    private function manufacturerQuery()
    {
        $prefix = $this->prefix();
        $shopId = $this->shopId();
        return DB::connection('mysql2')->table($prefix.'manufacturer as m')
            ->join($prefix.'manufacturer_shop as ms', fn ($join) => $join->on('ms.id_manufacturer', '=', 'm.id_manufacturer')->where('ms.id_shop', $shopId))
            ->join($prefix.'product as p', 'p.id_manufacturer', '=', 'm.id_manufacturer')
            ->join($prefix.'product_shop as ps', fn ($join) => $join->on('ps.id_product', '=', 'p.id_product')->where('ps.id_shop', $shopId))
            ->where('m.active', 1);
    }

    private function imagesByProduct(array $productIds, array $linkRewrites, array $technicalImageIds)
    {
        if ($productIds === []) return collect();
        $prefix = $this->prefix();
        $shopId = $this->shopId();
        return DB::connection('mysql2')->table($prefix.'image as i')
            ->join($prefix.'image_shop as ims', fn ($join) => $join->on('ims.id_image', '=', 'i.id_image')->where('ims.id_shop', $shopId))
            ->whereIn('i.id_product', $productIds)->orderBy('i.id_product')->orderBy('i.position')
            ->get(['i.id_product', 'i.id_image', 'i.position', 'ims.cover'])
            ->groupBy(fn ($image) => (int) $image->id_product)
            ->map(fn ($rows) => $rows->map(fn ($image) => [
                'id_image' => (int) $image->id_image,
                'position' => (int) $image->position,
                'cover' => (bool) $image->cover,
                'technical' => (int) $image->id_image === (int) ($technicalImageIds[(int) $image->id_product] ?? 0),
                'thumbnail_url' => $this->friendlyImageUrl(
                    (int) $image->id_image,
                    'tm_medium_default',
                    $linkRewrites[(int) $image->id_product] ?? '',
                    'webp'
                ),
                'large_url' => $this->imageUrl((int) $image->id_image, 'large_default'),
            ]));
    }

    private function frontProductUrl(int $idProduct): string
    {
        return rtrim((string) config('allstars.stores.ASM.base_url'), '/').'/index.php?id_product='.$idProduct.'&controller=product';
    }

    private function imageUrl(int $idImage, string $type): string
    {
        $path = implode('/', str_split((string) $idImage));
        return rtrim((string) config('allstars.stores.ASM.base_url'), '/').'/img/p/'.$path.'/'.$idImage.'-'.$type.'.jpg';
    }

    private function friendlyImageUrl(int $idImage, string $type, string $linkRewrite, string $extension = 'jpg'): string
    {
        if ($linkRewrite === '') {
            return $extension === 'jpg'
                ? $this->imageUrl($idImage, $type)
                : rtrim((string) config('allstars.stores.ASM.base_url'), '/')
                    .'/img/p/'.implode('/', str_split((string) $idImage)).'/'.$idImage.'-'.$type.'.'.$extension;
        }

        return rtrim((string) config('allstars.stores.ASM.base_url'), '/')
            .'/'.$idImage.'-'.$type.'/'.$linkRewrite.'.'.$extension;
    }

    private function englishLanguageId(): int
    {
        return (int) (DB::connection('mysql2')->table($this->prefix().'lang')->where('iso_code', 'en')->value('id_lang') ?: 1);
    }

    private function shopId(): int { return (int) config('allstars.stores.ASM.id_shop', 2); }
    private function prefix(): string { return (string) env('DB2_DB_prefix', 'ps_'); }
}
