<?php
namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\modules\tv\tv;
use App\Models\prestashop\manufacturers;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class tvController extends Controller
{
    public array $breadcrumbs = [];
    public array $actions = [];

    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('marketing'), 'url' => route('marketing.index')];
        $this->breadcrumbs[] = [ 'name' =>  'TV', 'url' => route('marketing.tools.tv.index'), 'no_translation' => 1];
        $this->actions[]     = [];

    }
    
    public function index()
    {
        $items = tv::all();
    	$manufacturers = manufacturers::orderBy('name')->get();
        $breadcrumbs = $this->breadcrumbs;
        
        return view('customTools.tv.index', compact('items','manufacturers', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_manufacturer' => ['required', 'integer'],
            'media_type' => ['required', 'in:image,video_upload,youtube'],
            'src' => ['nullable', 'file'],
            'youtube_code' => ['nullable', 'string', 'max:80'],
            'text' => ['nullable', 'string', 'max:255'],
        ]);

        $manufacturerId = (int) $validated['id_manufacturer'];
        $text = $validated['text'] ?? '';
        $mediaType = $validated['media_type'];
        $src = null;

        if ($mediaType === 'youtube') {
            $youtubeCode = $this->normalizeYoutubeCode((string) $request->input('youtube_code', ''));

            if ($youtubeCode === '') {
                throw ValidationException::withMessages(['youtube_code' => 'Please insert a valid YouTube video code.']);
            }

            $src = 'youtube:' . $youtubeCode;
        }

        if (in_array($mediaType, ['image', 'video_upload'], true)) {
            if (!$request->hasFile('src')) {
                throw ValidationException::withMessages(['src' => 'Please upload a file.']);
            }

            $file = $request->file('src');
            $allowedMimes = $mediaType === 'image'
                ? ['image/jpeg', 'image/png', 'image/webp', 'image/gif']
                : ['video/mp4', 'video/webm', 'video/ogg'];

            if (!in_array($file->getMimeType(), $allowedMimes, true)) {
                throw ValidationException::withMessages(['src' => 'The uploaded file type is not valid for the selected media type.']);
            }

            if ($mediaType === 'video_upload' && $file->getSize() > 10 * 1024 * 1024) {
                throw ValidationException::withMessages(['src' => 'Uploaded videos must be smaller than 10MB.']);
            }

            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            if (!is_dir(public_path('uploads/tv'))) {
                mkdir(public_path('uploads/tv'), 0775, true);
            }
            $file->move(public_path('uploads/tv'), $filename);
            $src = '/uploads/tv/' . $filename;
        }

        if ($request->has('active')) {
            tv::where('active', 1)->update(['active' => 0]);
        }
    
        $item = tv::where('id_manufacturer', $manufacturerId)->first();
    
        if ($item) {
            if ($src) {
                $item->src = $src;
            }
            $item->text = $text;
            $item->active = $request->has('active') ? 1 : 0;
            $item->save();
        } else {
            tv::create([
                'id_manufacturer' => $manufacturerId,
                'src' => $src,
                'text' => $text,
                'active' => $request->has('active') ? 1 : 0,
            ]);
        }
    
        return redirect()->back()->with('success', 'Item saved successfully!');
    }

    public function toggleActive(Request $request, $id)
    {
        $item = tv::findOrFail($id);
    
        if (!$item->active) {
            tv::where('active', 1)->update(['active' => 0]);
    
            $item->active = 1;
        } else {
            $item->active = 0;
        }
    
        $item->save();
    
        return redirect()->back();
    }
    
    public function tv()
    {
        $item = tv::where('active', 1)->first();
        return view('tv', compact('item'));
    }
    
    
    public function changeText(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:tv,id',
            'text' => 'nullable|string|max:255',
        ]);
    
        $item = tv::find($request->id);
        $item->text = $request->text;
        $item->save();
    
        return response()->json(['success' => true]);
    }

    private function normalizeYoutubeCode(string $value): string
    {
        $value = trim($value);

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,20})~', $value, $matches)) {
            $value = $matches[1];
        }

        return preg_match('/^[A-Za-z0-9_-]{6,20}$/', $value) ? $value : '';
    }
}
