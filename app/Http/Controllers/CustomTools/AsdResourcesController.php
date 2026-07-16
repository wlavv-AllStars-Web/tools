<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\asdResources\ASDBrandResource;
use App\Models\modules\asdResources\PrestashopManufacturer;
use App\Models\prestashop\AsdImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;
use ZipArchive;

class ASDResourcesController extends Controller
{
    private int $asdShopId = 3;
    
    public $actions;
    public $breadcrumbs;
    
    public function __construct(){
        $this->breadcrumbs[] = ['name' => 'marketing', 'url' => route('marketing.index')];
        $this->breadcrumbs[] = ['name' => 'ASD resources', 'url' => route('data.resources.index'), 'no_translation' => 1];
    }

    public function index()
    {
        $brands = PrestashopManufacturer::query()
            ->from('ps_manufacturer as m')
            ->select('m.id_manufacturer', 'm.name', 'm.active')
            ->join('ps_manufacturer_shop as ms', 'ms.id_manufacturer', '=', 'm.id_manufacturer')
            ->where('ms.id_shop', $this->asdShopId)
            ->orderBy('m.name')
            ->get();
            
        $resources = ASDBrandResource::query()->where('id_shop', $this->asdShopId)->get()->keyBy('id_manufacturer');
        
        $breadcrumbs = $this->breadcrumbs;
        return view('customTools.asdResources.index', compact('brands', 'resources', 'breadcrumbs'));
    }

    public function edit(int $id_manufacturer)
    {
        $brand = $this->getAsdBrandOrFail($id_manufacturer);

        $resource = ASDBrandResource::query()->firstOrCreate(
            [
                'id_manufacturer' => $id_manufacturer,
                'id_shop' => $this->asdShopId,
            ]
        );
        
        $breadcrumbs = $this->breadcrumbs;

        return view('customTools.asdResources.edit', compact('brand', 'resource', 'breadcrumbs'));
    }

    public function update(Request $request, int $id_manufacturer)
    {
        $brand = $this->getAsdBrandOrFail($id_manufacturer);

        $validated = $request->validate([
            'facebook_url' => ['nullable', 'url', 'max:500'],
            'website_url' => ['nullable', 'url', 'max:500'],

            'catalog_file' => ['nullable', 'file', 'mimes:pdf,xlsx', 'max:51200'],
            'import_file' => ['nullable', 'file', 'mimes:csv,txt', 'max:20480'],
            'logos_zip' => ['nullable', 'file', 'mimes:zip', 'max:102400'],

            'pictures_selected_count' => ['nullable', 'integer', 'max:' . (int) ini_get('max_file_uploads')],
            'pictures' => ['nullable', 'array'],
            'pictures.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
        ]);

        $resource = ASDBrandResource::query()->firstOrCreate([
            'id_manufacturer' => $id_manufacturer,
            'id_shop' => $this->asdShopId,
        ]);

        $brandFolder = $this->brandFolder($brand->id_manufacturer);
        $brandSlug = $this->brandSlug($brand->name);

        $data = [
            'facebook_url' => $validated['facebook_url'] ?? null,
            'website_url' => $validated['website_url'] ?? null,
            'last_update' => now()->toDateString(),
        ];

        if ($request->hasFile('catalog_file')) {
            $data['catalog_file'] = $this->uploadSingleFile(
                $request->file('catalog_file'),
                $brandFolder,
                $brandSlug . '_catalog'
            );
        }

        if ($request->hasFile('import_file')) {
            $data['import_file'] = $this->uploadSingleFile(
                $request->file('import_file'),
                $brandFolder,
                $brandSlug . '_import'
            );
        }

        if ($request->hasFile('logos_zip')) {
            $data['logos_zip'] = $this->uploadSingleFile(
                $request->file('logos_zip'),
                $brandFolder,
                $brandSlug . '_logos'
            );
        }

        if ($request->hasFile('pictures')) {
            $selectedPictures = (int) $request->input('pictures_selected_count', 0);
            $receivedPictures = count($request->file('pictures', []));

            if ($selectedPictures > $receivedPictures) {
                throw ValidationException::withMessages([
                    'pictures' => 'Only ' . $receivedPictures . ' of ' . $selectedPictures . ' selected images reached the server. Upload at most ' . (int) ini_get('max_file_uploads') . ' images at a time.',
                ]);
            }

            $failedPictures = [];

            foreach ($request->file('pictures') as $picture) {
                if (!$picture) {
                    continue;
                }

                if (!$picture->isValid() || !$this->uploadPictureAndThumb($picture, $brandFolder)) {
                    $failedPictures[] = $picture->getClientOriginalName();
                }
            }

            if (!empty($failedPictures)) {
                throw ValidationException::withMessages([
                    'pictures' => 'Could not process these images: ' . implode(', ', $failedPictures),
                ]);
            }

            $data['images_zip'] = $this->rebuildImagesZip($brandFolder, $brandSlug);
        }

        $resource->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ASD brand resources updated successfully.',
            ]);
        }

        return redirect()
            ->route('data.resources.edit', $brand->id_manufacturer)
            ->with('success', 'ASD brand resources updated successfully.');
    }

    public function images(Request $request, int $id_manufacturer)
    {
        $breadcrumbs = $this->breadcrumbs;
        $brand = $this->getAsdBrandOrFail($id_manufacturer);

        $references = $this->getBrandReferences($id_manufacturer);

        $rows = $references->map(function ($item) use ($id_manufacturer) {
            $reference = trim((string) $item->reference);
            $imageName = trim(strip_tags(html_entity_decode((string) ($item->image_name ?? ''))));
            $imageCode = trim(strip_tags(html_entity_decode((string) ($item->image_code ?? ''))));
            $imagePath = AsdImage::resolveImagePathForRow(
                $id_manufacturer,
                (int) $item->id_product_attribute,
                $reference,
                $imageName,
                $imageCode
            );

            return [
                'reference' => $reference,
                'product_name' => $item->product_name,
                'id_product' => $item->id_product,
                'id_product_attribute' => $item->id_product_attribute,
                'image_name' => $imageName,
                'image_code' => $imageCode,
                'lookup_value' => $this->imageLookupValue($reference, $imageCode),
                'image_path' => $imagePath,
                'has_image' => $imagePath !== null,
            ];
        });

        $totalReferences = $rows->count();
        $totalImages = $rows->where('has_image', true)->count();
        $totalMissing = $totalReferences - $totalImages;
        $imageFilter = $request->query('filter') === 'missing' ? 'missing' : 'all';

        if ($imageFilter === 'missing') {
            $rows = $rows->where('has_image', false)->values();
        }

        return view('customTools.asdResources.images', compact(
            'brand',
            'rows',
            'totalReferences',
            'totalImages',
            'totalMissing', 'imageFilter', 'breadcrumbs'
        ));
    }

    public function api()
    {
        $brands = PrestashopManufacturer::query()
            ->from('ps_manufacturer as m')
            ->select('m.id_manufacturer', 'm.name', 'm.active')
            ->join('ps_manufacturer_shop as ms', 'ms.id_manufacturer', '=', 'm.id_manufacturer')
            ->where('ms.id_shop', $this->asdShopId)
            ->where('m.active', 1)
            ->orderBy('m.name')
            ->get();

        $resources = ASDBrandResource::query()
            ->where('id_shop', $this->asdShopId)
            ->get()
            ->keyBy('id_manufacturer');

        $data = $brands->map(function ($brand) use ($resources) {
            $resource = $resources->get($brand->id_manufacturer);

            return [
                'id_manufacturer' => $brand->id_manufacturer,
                'name' => $brand->name,
                'facebook_url' => $resource?->facebook_url,
                'website_url' => $resource?->website_url,
                'last_update' => $resource?->last_update,
                'catalog_file' => $this->assetOrNull($resource?->catalog_file),
                'import_file' => $this->assetOrNull($resource?->import_file),
                'logos_zip' => $this->assetOrNull($resource?->logos_zip),
                'images_zip' => $this->assetOrNull($resource?->images_zip),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    private function getAsdBrandOrFail(int $idManufacturer)
    {
        return PrestashopManufacturer::query()
            ->from('ps_manufacturer as m')
            ->select('m.id_manufacturer', 'm.name', 'm.active')
            ->join('ps_manufacturer_shop as ms', 'ms.id_manufacturer', '=', 'm.id_manufacturer')
            ->where('ms.id_shop', $this->asdShopId)
            ->where('m.id_manufacturer', $idManufacturer)
            ->firstOrFail();
    }

    private function getBrandReferences(int $idManufacturer)
    {
        $products = DB::connection('mysql2')
            ->table('ps_product as p')
            ->join('ps_product_shop as pshop', function ($join) {
                $join->on('pshop.id_product', '=', 'p.id_product')
                    ->where('pshop.id_shop', $this->asdShopId);
            })
            ->leftJoin('ps_product_lang as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', '=', 1)
                    ->where('pl.id_shop', $this->asdShopId);
            })
            ->leftJoin('ps_custom_product as cp', 'cp.id_product', '=', 'p.id_product')
            ->select(
                'p.id_product',
                DB::raw('0 as id_product_attribute'),
                'p.reference',
                DB::raw('COALESCE(pl.description_short, "") as image_name'),
                DB::raw('COALESCE(cp.image_code, "") as image_code'),
                DB::raw('COALESCE(pl.name, "") as product_name')
            )
            ->where('p.id_manufacturer', $idManufacturer)
            ->whereNotNull('p.reference')
            ->where('p.reference', '!=', '')
            ->where('p.reference', 'not like', '%-Z');

        $attributes = DB::connection('mysql2')
            ->table('ps_product_attribute as pa')
            ->join('ps_product as p', 'p.id_product', '=', 'pa.id_product')
            ->join('ps_product_shop as pshop', function ($join) {
                $join->on('pshop.id_product', '=', 'p.id_product')
                    ->where('pshop.id_shop', $this->asdShopId);
            })
            ->leftJoin('ps_product_lang as pl', function ($join) {
                $join->on('pl.id_product', '=', 'p.id_product')
                    ->where('pl.id_lang', '=', 1)
                    ->where('pl.id_shop', $this->asdShopId);
            })
            ->leftJoin('ps_custom_product_attribute as cpa', 'cpa.id_product_attribute', '=', 'pa.id_product_attribute')
            ->select(
                'p.id_product',
                'pa.id_product_attribute',
                'pa.reference',
                DB::raw('COALESCE(pl.description_short, "") as image_name'),
                DB::raw('COALESCE(cpa.image_code, "") as image_code'),
                DB::raw('COALESCE(pl.name, "") as product_name')
            )
            ->where('p.id_manufacturer', $idManufacturer)
            ->whereNotNull('pa.reference')
            ->where('pa.reference', '!=', '')
            ->where('pa.reference', 'not like', '%-Z');

        return $products
            ->union($attributes)
            ->orderBy('reference')
            ->get()
            ->unique('reference')
            ->values();
    }

    private function imageLookupValue(string $reference, string $imageCode): string
    {
        return $imageCode !== '' ? $imageCode : $reference;
    }

    private function uploadSingleFile($file, string $folder, string $baseName): string
    {
        $destinationPath = public_path($folder);

        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $filename = $baseName . '.' . $extension;

        $file->move($destinationPath, $filename);

        return $folder . '/' . $filename;
    }

    private function uploadPictureAndThumb($file, string $brandFolder): bool
    {
        $folder600 = $brandFolder . '/600';
        $folderThumb = $brandFolder . '/thumb';

        $path600 = public_path($folder600);
        $pathThumb = public_path($folderThumb);

        if (!is_dir($path600)) {
            mkdir($path600, 0755, true);
        }

        if (!is_dir($pathThumb)) {
            mkdir($pathThumb, 0755, true);
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = $this->safeImageFilename($originalName) ?: 'image_' . now()->format('YmdHis');
        $filename = $safeName . '.webp';

        $image600Relative = $folder600 . '/' . $filename;
        $thumbRelative = $folderThumb . '/' . $filename;

        $sourceImage = $this->createImageResourceFromUpload($file->getPathname());

        if (!$sourceImage) {
            return false;
        }

        $imageCreated = imagewebp($sourceImage, public_path($image600Relative), 90);

        imagedestroy($sourceImage);

        if (!$imageCreated) {
            return false;
        }

        return $this->createThumb125(
            public_path($image600Relative),
            public_path($thumbRelative)
        );
    }

    private function createImageResourceFromUpload(string $sourcePath)
    {
        $info = getimagesize($sourcePath);

        if (!$info) {
            return null;
        }

        $mime = $info['mime'] ?? null;

        try {
            return match ($mime) {
                'image/jpeg' => imagecreatefromjpeg($sourcePath),
                'image/png' => imagecreatefrompng($sourcePath),
                'image/webp' => imagecreatefromwebp($sourcePath),
                default => null,
            };
        } catch (Throwable) {
            return null;
        }
    }

    private function safeImageFilename(string $originalName): string
    {
        $filename = strtolower(Str::ascii(trim($originalName)));
        $filename = preg_replace('/\s+/', '_', $filename) ?? '';

        return preg_replace('/[^a-z0-9_-]/', '', $filename) ?? '';
    }

    private function rebuildImagesZip(string $brandFolder, string $brandSlug): ?string
    {
        $folder600 = public_path($brandFolder . '/600');

        if (!is_dir($folder600)) {
            return null;
        }

        $zipRelativePath = $brandFolder . '/' . $brandSlug . '_images.zip';
        $zipFullPath = public_path($zipRelativePath);

        if (file_exists($zipFullPath)) {
            unlink($zipFullPath);
        }

        $zip = new ZipArchive();

        if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        foreach (scandir($folder600) as $file) {
            if (in_array($file, ['.', '..'], true)) {
                continue;
            }

            $fullPath = $folder600 . DIRECTORY_SEPARATOR . $file;

            if (is_file($fullPath) && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'webp') {
                $zip->addFile($fullPath, $file);
            }
        }

        $zip->close();

        return $zipRelativePath;
    }

    private function createThumb125(string $sourcePath, string $destinationPath): bool
    {
        if (!file_exists($sourcePath)) {
            return false;
        }

        $info = getimagesize($sourcePath);

        if (!$info) {
            return false;
        }

        [$width, $height] = $info;
        $sourceImage = imagecreatefromwebp($sourcePath);

        if (!$sourceImage) {
            return false;
        }

        $thumbSize = 125;
        $thumbImage = imagecreatetruecolor($thumbSize, $thumbSize);

        imagealphablending($thumbImage, false);
        imagesavealpha($thumbImage, true);

        $transparent = imagecolorallocatealpha($thumbImage, 0, 0, 0, 127);
        imagefilledrectangle($thumbImage, 0, 0, $thumbSize, $thumbSize, $transparent);

        $ratio = min($thumbSize / $width, $thumbSize / $height);

        $newWidth = (int) round($width * $ratio);
        $newHeight = (int) round($height * $ratio);

        $dstX = (int) floor(($thumbSize - $newWidth) / 2);
        $dstY = (int) floor(($thumbSize - $newHeight) / 2);

        imagecopyresampled(
            $thumbImage,
            $sourceImage,
            $dstX,
            $dstY,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        $destinationDir = dirname($destinationPath);

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $thumbCreated = imagewebp($thumbImage, $destinationPath, 90);

        imagedestroy($sourceImage);
        imagedestroy($thumbImage);

        return $thumbCreated;
    }

    private function brandFolder(int $idManufacturer): string
    {
        return 'uploads/asd/brands/' . $idManufacturer;
    }

    private function brandSlug(string $brandName): string
    {
        return Str::slug($brandName, '_') ?: 'brand';
    }

    private function assetOrNull(?string $path): ?string
    {
        return $path ? asset($path) : null;
    }
}
