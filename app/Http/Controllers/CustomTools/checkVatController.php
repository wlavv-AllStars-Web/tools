<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\checkVat\checkVat;
use App\Models\prestashop\address;
use App\Services\Vat\VatValidationRequestService;
use App\Services\Mail\StoreMailer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class CheckVatController extends Controller
{
    public $actions = [];

    public function index()
    {
        $indexRoute = request()->routeIs('finance.tools.vat.*') ? 'finance.tools.vat.check' : 'checkVat.index';
        $verifyRoute = request()->routeIs('finance.tools.vat.*') ? 'finance.tools.vat.verify' : 'checkVat.verify';

        $this->actions[] = [
            'name' => 'VERIFY',
            'icon' => '<i class="f-left fa fa-trash"></i>',
            'url' => route($verifyRoute),
            'class' => 'btn btn-warning',
        ];

        $data = [
            'actions' => $this->actions,
            'breadcrumbs' => [
                ['name' => 'finance', 'url' => route('finance.index')],
                ['name' => 'VAT', 'url' => route($indexRoute), 'no_translation' => 1],
            ],
            'verified' => checkVat::groupBy('vat_number')->get(),
            'counters' => checkVat::getCounters(),
        ];

        return View::make('customTools/checkVat/index')->with($data);
    }

    public function verify()
    {
        $addresses = address::select('ps_customer.id_customer', 'ps_address.vat_number')
            ->leftJoin('ps_customer', 'ps_address.id_customer', '=', 'ps_customer.id_customer')
            ->where('ps_customer.id_default_group', 4)
            ->where('ps_address.id_customer', '>', 1)
            ->where('ps_address.deleted', 0)
            ->where('ps_address.active', 1)
            ->where('ps_address.id_country', '<>', 249)
            ->orderBy('ps_customer.id_customer')
            ->groupBy('ps_address.id_address')
            ->get();

        checkVat::verify($addresses);

        StoreMailer::sendRaw(
            'asm_sales',
            'bruno.fernandes.asm@gmail.com',
            'VAT check execution confirmation',
            'VAT check execution confirmation'
        );

        return redirect()->route(request()->routeIs('finance.tools.vat.*') ? 'finance.tools.vat.check' : 'checkVat.index');
    }

    public function apiAdd(
        Request $request,
        VatValidationRequestService $service,
        ?int $id_customer = null,
        ?string $vat_number = null,
        ?string $country_iso = null,
        ?string $token = null
    ): JsonResponse {
    
        // 🔐 1. Normalizar input (GET ou POST)
        $id_customer = $id_customer ?? (int) $request->input('id_customer');
        $vat_number  = $vat_number ?? $request->input('vat_number');
        $country_iso = $country_iso ?? $request->input('country_iso');
        $token       = $token ?? $request->input('token');

        $expectedToken = (string) config('allstars.api.tokens.vat_validation');
    
        if ($expectedToken === '' || !hash_equals($expectedToken, (string) $token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 403);
        }
    
        if (!$id_customer || !$vat_number || !$country_iso) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameters'
            ], 422);
        }
    
        $country_iso = strtoupper(trim($country_iso));
        $vat_number  = trim($vat_number);
    
        try {
                $vatRequest = $service->addRequest(
                $id_customer,
                $country_iso,
                $vat_number
            );
    
            return response()->json([
                'success' => true,
                'message' => 'VAT request stored',
                'data' => [
                    'id' => $vatRequest->id,
                    'status' => $vatRequest->status
                ]
            ]);
    
        } catch (\Throwable $e) {
    
            return response()->json([
                'success' => false,
                'message' => 'Internal error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
