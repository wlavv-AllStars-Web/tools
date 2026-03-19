<?php
namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\modules\tv\tv;
use App\Models\prestashop\manufacturers;

class tvController extends Controller
{

    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('webmaster'), 'url' => route('web.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('TV'), 'url' => route('tv.index')];
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
        $manufacturerId = $request->id_manufacturer ?? 0;
    
        $text = $request->text ?? '';
        $src = null;
    
        if ($request->hasFile('src')) {
            $file = $request->file('src');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/tv'), $filename);
            $src = '/images/tv/' . $filename;
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
                'src' => $src ?? '',
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
}
