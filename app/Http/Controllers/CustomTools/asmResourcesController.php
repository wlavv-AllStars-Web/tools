<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class asmResourcesController extends Controller
{
    private int $shopId = 2;

    private array $languages = ['EN', 'ES', 'FR'];
    
    public $actions;
    public $breadcrumbs;
    
    public function __construct(){
        $this->breadcrumbs[] = [ 'name' =>  trans('marketing'), 'url' => route('marketing.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('marketing.asm_resources.index'), 'url' => route('marketing.resources.index')];
    }
    
    public function index(){
        $this->middleware('auth');
        $breadcrumbs = $this->breadcrumbs;

        $brands = DB::connection('mysql2')
            ->table('ps_manufacturer as m')
            ->select('m.id_manufacturer', 'm.name', 'm.active', 'cm.youtube')
            ->join('ps_manufacturer_shop as ms', 'ms.id_manufacturer', '=', 'm.id_manufacturer')
            ->leftJoin('ps_custom_manufacturer as cm', 'cm.id_manufacturer', '=', 'm.id_manufacturer')
            ->where('ms.id_shop', $this->shopId)
            ->orderBy('m.name')
            ->get();

        return view('customTools.asmResources.index', [
            'brands' => $brands,
            'languages' => $this->languages,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function api()
    {
        $brands = DB::connection('mysql2')
            ->table('ps_manufacturer as m')
            ->select('m.id_manufacturer', 'm.name', 'm.active', 'cm.youtube')
            ->join('ps_manufacturer_shop as ms', 'ms.id_manufacturer', '=', 'm.id_manufacturer')
            ->leftJoin('ps_custom_manufacturer as cm', 'cm.id_manufacturer', '=', 'm.id_manufacturer')
            ->where('ms.id_shop', $this->shopId)
            ->where('m.active', 1)
            ->orderBy('m.name')
            ->get();

        $data = $brands->map(function ($brand) {
            return [
                'id_manufacturer' => (int) $brand->id_manufacturer,
                'name' => $brand->name,
                'youtube' => $brand->youtube,
                'banners' => collect($this->languages)
                    ->mapWithKeys(fn ($lang) => [
                        strtolower($lang) => $this->bannerUrlOrNull((int) $brand->id_manufacturer, $lang),
                    ])
                    ->all(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function upload(Request $request, int $id_manufacturer, string $lang)
    {
        $lang = strtoupper($lang);

        if (!in_array($lang, $this->languages, true)) {
            abort(404, 'Invalid language.');
        }

        $brand = $this->getShopBrandOrFail($id_manufacturer);

        $request->validate([
            'banner' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $this->uploadBannerAsWebp(
            $request->file('banner'),
            (int) $brand->id_manufacturer,
            $lang
        );

        return redirect()
            ->route('marketing.resources.index')
            ->with('success', $lang . ' banner uploaded successfully for ' . $brand->name . '.');
    }

    public function updateYoutube(Request $request, int $id_manufacturer)
    {
        $brand = $this->getShopBrandOrFail($id_manufacturer);
        $data = $request->validate([
            'youtube' => ['nullable', 'string', 'max:2048'],
        ]);

        DB::connection('mysql2')->table('ps_custom_manufacturer')->updateOrInsert(
            ['id_manufacturer' => (int) $brand->id_manufacturer],
            ['youtube' => trim((string) ($data['youtube'] ?? ''))]
        );

        return redirect()
            ->route('marketing.resources.index')
            ->with('success', 'YouTube updated successfully for ' . $brand->name . '.');
    }

    private function getShopBrandOrFail(int $idManufacturer)
    {
        $brand = DB::connection('mysql2')
            ->table('ps_manufacturer as m')
            ->select('m.id_manufacturer', 'm.name', 'm.active', 'cm.youtube')
            ->join('ps_manufacturer_shop as ms', 'ms.id_manufacturer', '=', 'm.id_manufacturer')
            ->leftJoin('ps_custom_manufacturer as cm', 'cm.id_manufacturer', '=', 'm.id_manufacturer')
            ->where('ms.id_shop', $this->shopId)
            ->where('m.id_manufacturer', $idManufacturer)
            ->first();

        abort_if(!$brand, 404, 'Brand not found for this shop.');

        return $brand;
    }

    private function uploadBannerAsWebp($file, int $idManufacturer, string $lang): void
    {
        $folder = 'uploads/asm/product';
        $destinationPath = public_path($folder);

        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $sourceImage = $this->createImageResourceFromUpload($file->getPathname());

        if (!$sourceImage) {
            throw new \RuntimeException('Invalid image file.');
        }

        $filename = $idManufacturer . '_' . strtoupper($lang) . '.webp';
        $fullPath = public_path($folder . '/' . $filename);

        imagewebp($sourceImage, $fullPath, 90);

        imagedestroy($sourceImage);
    }

    private function bannerUrlOrNull(int $idManufacturer, string $lang): ?string
    {
        $path = 'uploads/asm/product/' . $idManufacturer . '_' . strtoupper($lang) . '.webp';

        return file_exists(public_path($path)) ? asset($path) : null;
    }

    private function createImageResourceFromUpload(string $sourcePath)
    {
        $info = getimagesize($sourcePath);

        if (!$info) {
            return null;
        }

        $mime = $info['mime'] ?? null;

        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => null,
        };
    }
}
