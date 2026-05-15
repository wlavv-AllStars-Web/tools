<?php

namespace App\Http\Controllers\Modules\oms;

use App\Http\Controllers\Controller;
use App\Models\modules\oms\OrderNote;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, OrderNote $orderNote)
    {
        return back()->with('warning', 'Billing flow was not replaced in this tranche. Keep the existing BillingService integration.');
    }
}
