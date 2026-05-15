<?php

namespace App\Http\Controllers\Modules\oms;

use App\Http\Controllers\Controller;
use App\Models\modules\oms\SupplierTermLevel;
use App\Services\oms\SupplierTermsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierTermLevelController extends Controller
{
    public function __construct(
        protected SupplierTermsService $supplierTermsService
    ) {
        $this->middleware('auth');
    }

    public function index(int $supplierId)
    {
        $levels = $this->supplierTermsService->getLevelsForSupplier($supplierId, false);

        return view('modules.oms.supplier_terms.index', [
            'breadcrumbs' => [
                ['name' => 'purchase', 'url' => route('purchase.index')],
                ['name' => 'OMS', 'url' => route(request()->routeIs('admin.tools.oms.*') ? 'admin.tools.oms.dashboard' : 'erp.oms.dashboard'), 'no_translation' => 1],
                ['name' => 'Supplier Terms', 'url' => route(request()->routeIs('admin.tools.oms.*') ? 'admin.tools.oms.supplier_terms.index' : 'erp.oms.supplier_terms.index', ['supplierId' => $supplierId]), 'no_translation' => 1],
            ],
            'supplierId' => $supplierId,
            'levels' => $levels,
            'supplierTermsService' => $this->supplierTermsService,
        ]);
    }

    public function store(Request $request, int $supplierId)
    {
        $payload = $this->supplierTermsService->normalizePayload($request->all(), $supplierId);
        $this->validatePayload($payload);
        SupplierTermLevel::create($payload);

        return redirect()->back()->with('success', 'Commercial level created.');
    }

    public function update(Request $request, SupplierTermLevel $level)
    {
        $payload = $this->supplierTermsService->normalizePayload($request->all(), (int) $level->supplier_id);
        $this->validatePayload($payload);
        $level->update($payload);

        return redirect()->back()->with('success', 'Commercial level updated.');
    }

    public function destroy(SupplierTermLevel $level)
    {
        $level->delete();

        return redirect()->back()->with('success', 'Commercial level removed.');
    }

    protected function validatePayload(array $payload): void
    {
        Validator::make($payload, [
            'supplier_id' => ['required', 'integer', 'min:1'],
            'label' => ['nullable', 'string', 'max:120'],
            'min_amount' => ['required', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'gt:min_amount'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'free_shipping' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ])->validate();
    }
}
