<?php

namespace App\Http\Controllers\Modules\oms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\modules\oms\LogisticContainer;

class LogisticContainerController extends Controller
{
    public function index()
    {
        $containers = LogisticContainer::orderBy('type')->orderBy('name')->get();
        $indexRoute = request()->routeIs('admin.tools.oms.*')
            ? 'admin.tools.oms.logistic_containers.index'
            : (request()->routeIs('logistics.tools.oms.*') ? 'logistics.tools.oms.logistic_containers.index' : 'erp.oms.logistic_containers.index');
        $routePrefix = request()->routeIs('admin.tools.oms.*')
            ? 'admin.tools.oms.logistic_containers'
            : (request()->routeIs('logistics.tools.oms.*') ? 'logistics.tools.oms.logistic_containers' : 'erp.oms.logistic_containers');
        $breadcrumbs = $this->breadcrumbs('Logistic containers', route($indexRoute));

        return view('modules.oms.logistic_containers.index', compact('containers', 'breadcrumbs', 'routePrefix'));
    }

    public function create()
    {
        $breadcrumbs = $this->breadcrumbs('Create container');

        return view('modules.oms.logistic_containers.create', compact('breadcrumbs'));
    }

    public function store(Request $request)
    {
        LogisticContainer::create($this->validateData($request));
        return redirect()->route($request->routeIs('admin.tools.oms.*')
            ? 'admin.tools.oms.logistic_containers.index'
            : ($request->routeIs('logistics.tools.oms.*') ? 'logistics.tools.oms.logistic_containers.index' : 'erp.oms.logistic_containers.index'));
    }

    public function edit($id)
    {
        $container = LogisticContainer::findOrFail($id);
        $breadcrumbs = $this->breadcrumbs('Edit container');

        return view('modules.oms.logistic_containers.edit', compact('container', 'breadcrumbs'));
    }

    public function update(Request $request, $id)
    {
        $container = LogisticContainer::findOrFail($id);
        $container->update($this->validateData($request));
        return redirect()->route($request->routeIs('admin.tools.oms.*')
            ? 'admin.tools.oms.logistic_containers.index'
            : ($request->routeIs('logistics.tools.oms.*') ? 'logistics.tools.oms.logistic_containers.index' : 'erp.oms.logistic_containers.index'));
    }

    public function destroy($id)
    {
        LogisticContainer::findOrFail($id)->delete();
        return back();
    }

    private function validateData(Request $request)
    {
        return $request->validate([
            'name' => 'required',
            'type' => 'required|in:box,pallet,container',
            'width_cm' => 'required|numeric',
            'height_cm' => 'required|numeric',
            'depth_cm' => 'required|numeric',
            'max_weight_kg' => 'required|numeric',
            'max_pallets' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
        ]);
    }

    private function breadcrumbs(string $currentName, ?string $currentUrl = null): array
    {
        $dashboardRoute = request()->routeIs('admin.tools.oms.*')
            ? 'admin.tools.oms.dashboard'
            : 'erp.oms.dashboard';

        return [
            request()->routeIs('logistics.tools.oms.*')
                ? ['name' => 'logistics', 'url' => route('logistics.index')]
                : ['name' => 'administration', 'url' => route('administration.index')],
            ['name' => 'OMS', 'url' => route($dashboardRoute), 'no_translation' => 1],
            ['name' => $currentName, 'url' => $currentUrl, 'no_translation' => 1],
        ];
    }
}
