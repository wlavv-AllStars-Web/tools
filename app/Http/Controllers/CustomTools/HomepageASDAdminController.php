<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\homepageASD\HomepageASDItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HomepageASDAdminController extends Controller{
    
    public $actions;
    public $breadcrumbs;
    
    public function __construct(){
        $this->breadcrumbs[] = [ 'name' => 'Design', 'url' => route('design.index'), 'no_translation' => 1];
        $this->breadcrumbs[] = [ 'name' => 'ASD homepage', 'url' => route('design.tools.homepage.asd.index'), 'no_translation' => 1];
    }
    
    public function index(){
        $this->middleware('auth');
        $item = HomepageASDItem::firstOrCreate( ['id' => 1], ['title' => 'Homepage image'] );
        $breadcrumbs = $this->breadcrumbs;
        $currentImageUrl = $this->resourcesUrl($item->image_path);
        
        return view('customTools.homepageASD.index', compact('item', 'breadcrumbs', 'currentImageUrl'));
    }

    public function update(Request $request){
        $this->middleware('auth');
        $item = HomepageASDItem::firstOrCreate( ['id' => 1], ['title' => 'Homepage image'] );

        $validated = $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $imagePath = $item->image_path;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $dbFolder = $this->resourcesHomepageFolder();
            $storageFolder = $this->resourcesHomepageStorageFolder();
            $destinationPath = public_path($storageFolder);
            
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $filename = now()->format('YmdHis') . '_' .
                Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) .
                '_' . Str::lower(Str::random(8)) .
                '.webp';

            $image = @imagecreatefromstring($file->get());

            if (!$image) {
                throw ValidationException::withMessages([
                    'image' => 'Unable to process the uploaded image.',
                ]);
            }

            try {
                $this->writeWebpVariants($image, $destinationPath, $filename);
            } finally {
                imagedestroy($image);
            }

            $this->deleteImageVariants($imagePath);
            
            $imagePath = $dbFolder . '/' . $filename;
        }

        $item->update([
            'image_path' => $imagePath,
            'link_url' => $validated['link_url'] ?? null,
            'title' => $validated['title'] ?? null,
        ]);

        return redirect()
            ->route('design.tools.homepage.asd.index')
            ->with('success', 'Homepage updated successfully.');
    }
    
    public function api(){
        $item = HomepageASDItem::query()->where('id', 1)->first();
    
        if (!$item || !$item->image_path) return response()->json([ 'success' => false, 'data' => null, ], 404);

        return response()->json([
            'success' => true,
            'data' => [ 'url' => $item->link_url, 'image' => $this->resourcesUrl($item->image_path), 'title' => $item->title ]
        ]);
    }

    private function resourcesHomepageFolder(): string
    {
        return trim((string) config('allstars.services.resources.homepage_asd_path', 'asd/homepage'), '/');
    }

    private function resourcesHomepageStorageFolder(): string
    {
        return trim((string) config('allstars.services.resources.homepage_asd_storage_path', 'uploads/asd/homepage'), '/');
    }

    private function resourcesUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = preg_replace('#^uploads/#', '', ltrim($path, '/'));

        return rtrim((string) config('allstars.services.resources.homepage_asd_base_url'), '/') . '/' . $path;
    }

    /**
     * Save the full-size banner and the 1305px-wide variant used by ASD's srcset.
     */
    private function writeWebpVariants(\GdImage $image, string $destinationPath, string $filename): void
    {
        $originalPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;

        if (!imagewebp($image, $originalPath, 85)) {
            throw ValidationException::withMessages([
                'image' => 'Unable to save the uploaded image.',
            ]);
        }

        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $targetWidth = min(1305, $sourceWidth);
        $targetHeight = max(1, (int) round($sourceHeight * ($targetWidth / $sourceWidth)));
        $resized = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

        try {
            $responsivePath = $destinationPath . DIRECTORY_SEPARATOR . pathinfo($filename, PATHINFO_FILENAME) . '-1305w.webp';

            if (!imagewebp($resized, $responsivePath, 85)) {
                throw ValidationException::withMessages([
                    'image' => 'Unable to save the responsive image variant.',
                ]);
            }
        } finally {
            imagedestroy($resized);
        }
    }

    private function deleteImageVariants(?string $imagePath): void
    {
        if (!$imagePath) {
            return;
        }

        $filename = basename($imagePath);
        $folder = public_path($this->resourcesHomepageStorageFolder());
        $originalPath = $folder . DIRECTORY_SEPARATOR . $filename;
        $responsivePath = $folder . DIRECTORY_SEPARATOR . pathinfo($filename, PATHINFO_FILENAME) . '-1305w.webp';

        foreach ([$originalPath, $responsivePath] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
