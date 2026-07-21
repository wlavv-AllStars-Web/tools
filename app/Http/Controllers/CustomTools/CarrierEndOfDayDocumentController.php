<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\carrierEndOfDay\CarrierEndOfDayDocument;
use App\Services\Prestashop\PrestashopAdminLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class CarrierEndOfDayDocumentController extends Controller
{
    public array $breadcrumbs = [];

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = ['name' => trans('Logistics'), 'url' => route('logistics.index')];
    }

    public function index(Request $request)
    {
        $date = $request->input('date') ?: now()->toDateString();
        $shipments = $this->shipmentsForDate($date);
        $byCarrier = $shipments->groupBy('carrier_name')->sortKeys();

        $documents = CarrierEndOfDayDocument::query()
            ->whereDate('document_date', $date)
            ->withCount('lines')
            ->get()
            ->keyBy('carrier_name');

        $history = CarrierEndOfDayDocument::query()
            ->orderByDesc('document_date')
            ->orderBy('carrier_name')
            ->limit(50)
            ->get();

        return View::make('customTools.carrierEndOfDay.index', [
            'date' => $date,
            'byCarrier' => $byCarrier,
            'documents' => $documents,
            'history' => $history,
            'actions' => [],
            'breadcrumbs' => array_merge($this->breadcrumbs, [
                ['name' => 'Carrier end of day', 'url' => route('logistics.tools.carrier_end_of_day.index'), 'no_translation' => 1],
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'carrier_name' => ['required', 'string'],
        ]);

        $rows = $this->shipmentsForDate($data['date'])
            ->where('carrier_name', $data['carrier_name'])
            ->values();

        if ($rows->isEmpty()) {
            return redirect()
                ->route('logistics.tools.carrier_end_of_day.index', ['date' => $data['date']])
                ->with('error', 'No shipments found for this carrier/date.');
        }

        $document = CarrierEndOfDayDocument::query()->updateOrCreate(
            [
                'document_date' => $data['date'],
                'carrier_name' => $data['carrier_name'],
            ],
            [
                'shipments_count' => $rows->count(),
                'generated_by' => auth()->id(),
                'generated_at' => now(),
            ]
        );

        DB::transaction(function () use ($document, $rows) {
            $document->lines()->delete();

            foreach ($rows as $row) {
                $document->lines()->create([
                    'source_order_carrier_id' => $row->source_order_carrier_id,
                    'order_id' => $row->order_id,
                    'order_reference' => $row->order_reference,
                    'country' => $row->country,
                    'weight' => $row->weight,
                    'width' => null,
                    'length' => null,
                    'depth' => null,
                    'tracking_number' => $row->tracking_number,
                ]);
            }
        });

        return redirect()
            ->route('logistics.tools.carrier_end_of_day.show', $document)
            ->with('success', 'Document generated successfully.');
    }

    public function show(CarrierEndOfDayDocument $document)
    {
        $document->load('lines');

        return View::make('customTools.carrierEndOfDay.show', [
            'document' => $document,
            'actions' => [],
            'breadcrumbs' => array_merge($this->breadcrumbs, [
                ['name' => 'Carrier end of day', 'url' => route('logistics.tools.carrier_end_of_day.index'), 'no_translation' => 1],
                ['name' => $document->carrier_name, 'url' => route('logistics.tools.carrier_end_of_day.show', $document), 'no_translation' => 1],
            ]),
        ]);
    }

    public function print(CarrierEndOfDayDocument $document)
    {
        $document->load('lines');

        return View::make('customTools.carrierEndOfDay.print', [
            'document' => $document,
        ]);
    }

    public function pdf(CarrierEndOfDayDocument $document)
    {
        $document->load('lines');

        $tcpdfCachePath = storage_path('framework/cache/tcpdf/');
        if (!is_dir($tcpdfCachePath)) {
            mkdir($tcpdfCachePath, 0775, true);
        }

        if (!defined('K_PATH_CACHE')) {
            define('K_PATH_CACHE', $tcpdfCachePath);
        }

        require_once app_path('Libraries/tcpdf/tcpdf.php');

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Webtools');
        $pdf->SetAuthor('All Stars');
        $pdf->SetTitle($this->filename($document));
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->writeHTML(View::make('customTools.carrierEndOfDay.pdf', ['document' => $document])->render(), true, false, true, false, '');

        return response($pdf->Output($this->filename($document), 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->filename($document) . '"',
        ]);
    }

    private function shipmentsForDate(string $date): Collection
    {
        $rows = DB::connection('mysql2')->select(
            "
            SELECT
                oc.id_order_carrier AS source_order_carrier_id,
                oc.id_order AS order_id,
                o.reference AS order_reference,
                o.id_shop,
                COALESCE(cl.name, c.iso_code) AS country,
                oc.weight,
                NULL AS width,
                NULL AS length,
                NULL AS depth,
                oc.tracking_number,
                carrier.name AS carrier_name
            FROM ps_order_carrier oc
            JOIN ps_orders o ON o.id_order = oc.id_order
            JOIN ps_carrier carrier ON carrier.id_carrier = oc.id_carrier
            LEFT JOIN ps_address a ON a.id_address = o.id_address_delivery
            LEFT JOIN ps_country c ON c.id_country = a.id_country
            LEFT JOIN ps_country_lang cl ON cl.id_country = c.id_country AND cl.id_lang = o.id_lang
            WHERE EXISTS (
                SELECT 1
                FROM ps_order_history oh
                WHERE oh.id_order = o.id_order
                AND oh.id_order_state = 4
                AND DATE(oh.date_add) = ?
            )
            ORDER BY carrier.name, oc.id_order, o.reference, oc.id_order_carrier
            ",
            [$date]
        );

        return collect($rows)->map(function ($row) {
            $row->carrier_name = match (strtoupper(trim($row->carrier_name))) {
                'DPD' => 'DPD',
                'INPOST', 'MONDIAL RELAY' => 'MONDIAL RELAY',
                'NACEX' => 'NACEX',
                'UPS', 'UPS DISTRIBUTION' => 'UPS',
                default => $row->carrier_name,
            };
            $row->store_code = ((int) ($row->id_shop ?? 0)) === (int) config('shops.ASM.id') ? 'ASM' : 'ASD';
            $row->order_admin_url = PrestashopAdminLinkService::dashboardOrderAdminUrl((int) $row->order_id, $row->store_code);

            return $row;
        });
    }

    private function filename(CarrierEndOfDayDocument $document): string
    {
        $carrier = preg_replace('/[^A-Za-z0-9_-]+/', '_', $document->carrier_name);

        return 'carrier_end_of_day_' . $document->document_date->format('Ymd') . '_' . $carrier . '.pdf';
    }
}
