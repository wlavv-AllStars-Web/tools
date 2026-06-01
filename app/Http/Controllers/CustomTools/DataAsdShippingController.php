<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class DataAsdShippingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $countries = $this->countriesWithShippingValues();

        return View::make('customTools.data.asd-shipping.index', [
            'breadcrumbs' => [
                ['name' => trans('data'), 'url' => route('data.index')],
                ['name' => 'ASD shipping', 'url' => route('data.asd_shipping.index'), 'no_translation' => 1],
            ],
            'activeCountries' => $countries->filter(fn ($country) => $this->hasShippingValues($country))->values(),
            'inactiveCountries' => $countries->reject(fn ($country) => $this->hasShippingValues($country))->values(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'id_country' => ['required', 'integer'],
            'country' => ['required', 'string', 'max:64'],
            'status' => ['nullable', 'boolean'],
            'value_1' => ['nullable', 'numeric', 'min:0'],
            'value_2' => ['nullable', 'numeric', 'min:0'],
            'value_3' => ['nullable', 'numeric', 'min:0'],
        ]);

        $idCountry = (int) $data['id_country'];
        $values = [
            'value_1' => (float) ($data['value_1'] ?? 0),
            'value_2' => (float) ($data['value_2'] ?? 0),
            'value_3' => (float) ($data['value_3'] ?? 0),
        ];

        if (!$this->countryExists($idCountry)) {
            abort(404);
        }

        $table = $this->shippingTable();

        if (!$this->hasShippingValues((object) $values)) {
            DB::connection('mysql2')
                ->table($table)
                ->where('id_country', $idCountry)
                ->delete();

            return back()->with('success', 'Porte removido.');
        }

        $payload = [
            'id_country' => $idCountry,
            'country' => strtolower(trim($data['country'])),
            'status' => $request->boolean('status') ? 1 : 0,
            'value_1' => $values['value_1'],
            'value_2' => $values['value_2'],
            'value_3' => $values['value_3'],
            'updated_at' => now()->toDateString(),
            'deleted' => 0,
        ];

        $existing = DB::connection('mysql2')
            ->table($table)
            ->where('id_country', $idCountry)
            ->first();

        if ($existing) {
            DB::connection('mysql2')
                ->table($table)
                ->where('id_country', $idCountry)
                ->update($payload);
        } else {
            DB::connection('mysql2')
                ->table($table)
                ->insert($payload);
        }

        return back()->with('success', 'Porte atualizado.');
    }

    private function countriesWithShippingValues()
    {
        $countryTable = $this->psTable('country');
        $countryLangTable = $this->psTable('country_lang');
        $shippingTable = $this->shippingTable();
        $langId = $this->preferredLangId();

        return DB::connection('mysql2')
            ->table($countryTable . ' as c')
            ->leftJoin($countryLangTable . ' as cl', function ($join) use ($langId) {
                $join->on('cl.id_country', '=', 'c.id_country')
                    ->where('cl.id_lang', '=', $langId);
            })
            ->leftJoin($shippingTable . ' as s', 's.id_country', '=', 'c.id_country')
            ->select(
                'c.id_country',
                'c.iso_code',
                'c.active as prestashop_active',
                DB::raw('COALESCE(NULLIF(s.country, ""), NULLIF(cl.name, ""), LOWER(c.iso_code)) as country'),
                DB::raw('COALESCE(s.status, 1) as status'),
                DB::raw('COALESCE(s.value_1, 0) as value_1'),
                DB::raw('COALESCE(s.value_2, 0) as value_2'),
                DB::raw('COALESCE(s.value_3, 0) as value_3'),
                's.updated_at'
            )
            ->orderBy('country')
            ->get();
    }

    private function hasShippingValues(object $country): bool
    {
        return (float) ($country->value_1 ?? 0) != 0.0
            || (float) ($country->value_2 ?? 0) != 0.0
            || (float) ($country->value_3 ?? 0) != 0.0;
    }

    private function countryExists(int $idCountry): bool
    {
        return DB::connection('mysql2')
            ->table($this->psTable('country'))
            ->where('id_country', $idCountry)
            ->exists();
    }

    private function preferredLangId(): int
    {
        $locale = app()->getLocale();

        return (int) (DB::connection('mysql2')
            ->table($this->psTable('lang'))
            ->where('iso_code', $locale)
            ->value('id_lang') ?: 2);
    }

    private function shippingTable(): string
    {
        return $this->psTable('custom_asd_shipping');
    }

    private function psTable(string $table): string
    {
        return (string) env('DB2_DB_prefix', 'ps_') . $table;
    }
}
