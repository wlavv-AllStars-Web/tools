<?php

declare(strict_types=1);

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Services\Prestashop\CustomerCartAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class CustomerCartAccessController extends Controller
{
    public function __construct(private readonly CustomerCartAccessService $cartAccessService)
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return $this->view();
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $email = mb_strtolower(trim($data['email']));
        $stores = $this->cartAccessService->storesForEmail($email);

        if ($stores->isEmpty()) {
            return $this->view($email, collect(), null)
                ->withErrors(['email' => 'Não foi encontrado um cliente ativo para este email em ASM ou ASD.']);
        }

        if ($stores->count() === 1) {
            $result = $this->cartAccessService->createOrReuse($email, (string) $stores->first()->store);

            return $this->view($email, collect(), $result);
        }

        return $this->view($email, $stores, null);
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'store' => ['required', 'in:ASM,ASD'],
        ]);

        $email = mb_strtolower(trim($data['email']));
        $result = $this->cartAccessService->createOrReuse($email, $data['store']);

        return $this->view($email, collect(), $result);
    }

    private function view(?string $email = null, $stores = null, ?array $result = null)
    {
        return View::make('customTools.customer-cart-access.index', [
            'breadcrumbs' => [
                ['name' => 'Web', 'url' => route('web.index')],
                ['name' => 'Customer cart access', 'url' => route('web.tools.customer_cart_access.index'), 'no_translation' => true],
            ],
            'email' => $email,
            'stores' => $stores ?? collect(),
            'result' => $result,
        ]);
    }
}
