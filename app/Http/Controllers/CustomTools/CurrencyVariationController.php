<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\prestashop\CurrencyVariation;

class CurrencyVariationController extends Controller
{
    public function index()
    {
        $data = CurrencyVariation::orderBy('date', 'desc')->get();

        return view('currency_variation.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'usd' => 'required|numeric',
            'pound' => 'required|numeric',
            'yen' => 'required|numeric',
            'yuan' => 'required|numeric',
        ]);

        CurrencyVariation::updateOrCreate(
            ['date' => date('y-m-d')],
            [
                'usd' => $request->usd,
                'pound' => $request->pound,
                'yen' => $request->yen,
                'yuan' => $request->yuan,
            ]
        );

        return back()->with('success', 'Currency variation saved');
    }
}