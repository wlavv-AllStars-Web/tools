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
            'accessList' => [],
        ]);
    }
}
