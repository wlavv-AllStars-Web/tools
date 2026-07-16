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

    public function index()
    {
        return View::make('areas.marketing.product-image-review', [
            'manufacturers' => $this->manufacturerQuery()
                ->select('m.id_manufacturer', 'm.name')
                ->selectRaw('COUNT(DISTINCT p.id_product) AS product_count')
                ->groupBy('m.id_manufacturer', 'm.name')
                ->orderBy('m.name')
                ->get(),
            'breadcrumbs' => [
                ['name' => trans('marketing'), 'url' => route('marketing.index')],
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
            ->where('p.id_manufacturer', $manufacturerId)
            ->select('p.id_product', 'p.reference', 'pl.name')
            ->orderByRaw("CASE WHEN p.reference IS NULL OR TRIM(p.reference) = '' THEN 1 ELSE 0 END")
            ->orderBy('p.reference')->orderBy('p.id_product');

        $total = (clone $query)->count();
        $products = $query->forPage($page, self::PAGE_SIZE)->get();
        $images = $this->imagesByProduct($products->pluck('id_product')->map(fn ($id) => (int) $id)->all());

        return response()->json([
            'data' => $products->map(fn ($product) => [
                'id_product' => (int) $product->id_product,
                'reference' => trim((string) $product->reference) ?: '—',
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

    private function imagesByProduct(array $productIds)
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
                'thumbnail_url' => $this->imageUrl((int) $image->id_image, 'medium_default'),
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

    private function englishLanguageId(): int
    {
        return (int) (DB::connection('mysql2')->table($this->prefix().'lang')->where('iso_code', 'en')->value('id_lang') ?: 1);
    }

    private function shopId(): int { return (int) config('allstars.stores.ASM.id_shop', 2); }
    private function prefix(): string { return (string) env('DB2_DB_prefix', 'ps_'); }
}
