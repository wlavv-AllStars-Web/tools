<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class afterSalesController extends Controller
{
    public array $breadcrumbs = [];

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = ['name' => 'After Sales', 'url' => route('afterSales.index')];
    }

    public function index()
    {
        return View::make('areas.afterSales.index')->with([
            'breadcrumbs' => $this->breadcrumbs,
            'accessList' => [
                [
                    'name' => 'Returns',
                    'url' => route('sales.tools.returns.index'),
                    'icon' => 'fa-arrow-rotate-left',
                ],
                [
                    'name' => 'Warranties',
                    'url' => route('sales.tools.warranties.index'),
                    'icon' => 'fa-shield-heart',
                ],
                [
                    'name' => 'Return / Warranty availability',
                    'url' => route('web.tools.return_warranty.index'),
                    'icon' => 'fa-box-open',
                ],
            ],
        ]);
    }
}
