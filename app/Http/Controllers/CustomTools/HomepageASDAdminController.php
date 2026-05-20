<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\homepageASD\HomepageASDItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomepageASDAdminController extends Controller{
    
    public $actions;
    public $breadcrumbs;
    
    public function __construct(){
        $this->breadcrumbs[] = [ 'name' =>  trans('marketing'), 'url' => route('marketing.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('marketing.homepage_ASD.index'), 'url' => route('marketing.homepage_ASD.index')];
    }
    
    public function index(){
        $this->middleware('auth');
        $item = HomepageASDItem::firstOrCreate( ['id' => 1], ['title' => 'Homepage image'] );
        $breadcrumbs = $this->breadcrumbs;
        
        return view('customTools.homepageASD.index', compact('item', 'breadcrumbs'));
    }

    public function update(Request $request){
        $this->middleware('auth');
        $item = HomepageASDItem::firstOrCreate( ['id' => 1], ['title' => 'Homepage image'] );

        $validated = $request->validate([
            'image' => ['nullable', 'image', 'max:5120'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $imagePath = $item->image_path;

        if ($request->hasFile('image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            $file = $request->file('image');

            $filename = now()->format('YmdHis') . '_' .
                Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) .
                '.' . $file->getClientOriginalExtension();

            $dbFolder = $this->resourcesHomepageFolder();
            $storageFolder = $this->resourcesHomepageStorageFolder();
            $destinationPath = public_path($storageFolder);
            
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            $file->move($destinationPath, $filename);
            
            $imagePath = $dbFolder . '/' . $filename;
        }

        $item->update([
            'image_path' => $imagePath,
            'link_url' => $validated['link_url'] ?? null,
            'title' => $validated['title'] ?? null,
        ]);

        return redirect()
            ->route('marketing.homepage_ASD.index')
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

        return rtrim((string) config('allstars.services.resources.base_url'), '/') . '/' . $path;
    }
}
