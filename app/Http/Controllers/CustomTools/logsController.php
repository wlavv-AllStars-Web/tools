<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\logs\logs;
use Illuminate\Http\Request;

use App\Services\Logs\LogService;

class logsController extends Controller{
    
    public function __construct(){
        $this->breadcrumbs[] = [ 'name' =>  trans('web'),  'url' => route('web.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('logs'), 'url' => route('logs.index', ['type' => 1])];
        $this->actions[]     = [];
    }
    
    public function index(Request $request){
        
        $breadcrumbs = $this->breadcrumbs;

        $logs = logs::with('user')
            ->when($request->severity, function ($q) use ($request) {
                $q->where('severity', $request->severity);
            })
            ->when($request->module, function ($q) use ($request) {
                $q->where('module', $request->module);
            })->orderByDesc('id', 'DESC')->paginate(25);

        return view('customTools.logs.index', compact('logs', 'breadcrumbs'));
    }

    public function show($id)
    {
        $log = logs::with('user')->findOrFail($id);
        $this->breadcrumbs[] = [ 'name' =>  trans('logs details'), 'url' => null];
        $breadcrumbs = $this->breadcrumbs;
        return view('customTools.logs.show', compact('log', 'breadcrumbs'));
    }
}
