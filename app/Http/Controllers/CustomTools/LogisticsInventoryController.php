<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class LogisticsInventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $date = $this->date($request);

        if (!$this->isManager($request)) {
            if ($this->canPrepareInventory($request)) {
                return redirect()->route('logistics.tools.inventory.prepare', ['date' => $date]);
            }

            if ($this->canCountInventory($request)) {
                return redirect()->route('logistics.tools.inventory.work', ['date' => $date]);
            }

            abort(403);
        }

        $schedules = $this->schedulesFor($date);

        $preparationSchedules = $schedules->where('preparation_done', false)->values();
        $inventorySchedules = $schedules->where('preparation_done', true)->where('inventory_done', false)->values();
        $validationSchedules = $schedules->where('inventory_done', true)->where('verification_done', false)->values();

        return View::make('customTools.logistics.inventory.index', [
            'breadcrumbs' => $this->breadcrumbs(),
            'date' => $date,
            'schedules' => $schedules,
            'preparationSchedules' => $preparationSchedules,
            'inventorySchedules' => $inventorySchedules,
            'validationSchedules' => $validationSchedules,
            'preparationGroups' => $this->scheduleHousingGroups($preparationSchedules),
            'inventoryGroups' => $this->scheduleHousingGroups($inventorySchedules),
            'validationGroups' => $this->scheduleHousingGroups($validationSchedules),
            'isManager' => $this->isManager($request),
            'stats' => $this->stats($date),
        ]);
    }

    public function prepare(Request $request)
    {
        abort_unless($this->canPrepareInventory($request), 403);

        $date = $this->date($request);
        $schedules = $this->schedulesFor($date)->where('preparation_done', false)->values();
        $selectedCell = strtoupper(trim((string) $request->query('cell')));

        return View::make('customTools.logistics.inventory.prepare', [
            'breadcrumbs' => $this->breadcrumbs('Preparacao'),
            'date' => $date,
            'schedules' => $schedules,
            'scheduleGroups' => $this->scheduleHousingGroups($schedules),
            'selectedCell' => $selectedCell,
        ]);
    }

    public function prepareStore(Request $request)
    {
        abort_unless($this->canPrepareInventory($request), 403);

        $data = $request->validate([
            'date' => ['required', 'date'],
            'cell' => ['required', 'string', 'max:64'],
        ]);

        $cell = strtoupper(trim($data['cell']));
        $schedule = DB::table('logistics_inventory_schedules')
            ->where('inventory_date', $data['date'])
            ->where('cell', $cell)
            ->first();

        if (!$schedule) {
            return back()->with('warning', 'Esta celula nao esta planeada para preparacao nesta data.');
        }

        if ($schedule->inventory_done) {
            return back()->with('warning', 'Esta celula ja foi inventariada.');
        }

        if ($schedule->preparation_done) {
            return redirect()
                ->route('logistics.tools.inventory.prepare', ['date' => $schedule->inventory_date])
                ->with('success', 'Esta celula ja estava preparada.');
        }

        $products = $this->productsForCell($schedule->cell);

        DB::transaction(function () use ($schedule, $products, $request) {
            foreach ($products as $product) {
                DB::table('logistics_inventory_counts')->updateOrInsert(
                    [
                        'schedule_id' => $schedule->id,
                        'id_stock_available' => $product->id_stock_available,
                    ],
                    [
                        'id_product' => $product->id_product,
                        'id_product_attribute' => $product->id_product_attribute,
                        'reference' => $product->reference,
                        'ean13' => $product->ean13,
                        'location' => $product->location,
                        'current_quantity' => $product->quantity,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            DB::table('logistics_inventory_schedules')
                ->where('id', $schedule->id)
                ->update([
                    'preparation_done' => true,
                    'preparation_operator_id' => $request->user()->id,
                    'preparation_done_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('logistics.tools.inventory.prepare', ['date' => $schedule->inventory_date])
            ->with('success', 'Preparacao concluida.');
    }

    public function work(Request $request)
    {
        abort_unless($this->canCountInventory($request), 403);

        return View::make('customTools.logistics.inventory.work', [
            'breadcrumbs' => $this->breadcrumbs('Inventario'),
        ]);
    }

    public function workStore(Request $request)
    {
        abort_unless($this->canCountInventory($request), 403);

        $data = $request->validate([
            'cell_scan' => ['required', 'string', 'max:64'],
        ]);

        $cell = strtoupper(trim($data['cell_scan']));
        $schedule = DB::table('logistics_inventory_schedules')
            ->where('cell', $cell)
            ->where('preparation_done', true)
            ->where('inventory_done', false)
            ->where('verification_done', false)
            ->orderBy('inventory_date')
            ->first();

        if (!$schedule) {
            return back()
                ->with('warning', 'Indisponivel.');
        }

        $request->session()->put($this->cellConfirmationSessionKey($schedule->id), true);

        return redirect()->route('logistics.tools.inventory.count', ['schedule' => $schedule->id]);
    }

    public function count(Request $request, int $schedule)
    {
        abort_unless($this->canCountInventory($request), 403);

        $schedule = $this->scheduleById($schedule);

        if (!$schedule->preparation_done || $schedule->inventory_done) {
            return redirect()
                ->route($this->postInventoryRoute($request), ['date' => $schedule->inventory_date])
                ->with('warning', 'Esta celula nao esta disponivel para inventario.');
        }

        if ((int) $this->countsQuery($schedule->id)->count() === 0) {
            foreach ($this->productsForCell($schedule->cell) as $product) {
                DB::table('logistics_inventory_counts')->updateOrInsert(
                    ['schedule_id' => $schedule->id, 'id_stock_available' => $product->id_stock_available],
                    [
                        'id_product' => $product->id_product,
                        'id_product_attribute' => $product->id_product_attribute,
                        'reference' => $product->reference,
                        'ean13' => $product->ean13,
                        'location' => $product->location,
                        'current_quantity' => $product->quantity,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        if ((int) $this->countsQuery($schedule->id)->whereNull('ean13')->count() > 0) {
            foreach ($this->productsForCell($schedule->cell) as $product) {
                DB::table('logistics_inventory_counts')
                    ->where('schedule_id', $schedule->id)
                    ->where('id_stock_available', $product->id_stock_available)
                    ->update([
                        'reference' => $product->reference,
                        'ean13' => $product->ean13,
                        'updated_at' => now(),
                    ]);
            }
        }

        $recountActive = (int) $this->countsQuery($schedule->id)->where('recount_requested', true)->count() > 0;
        if ($request->boolean('selected')) {
            $request->session()->put($this->cellConfirmationSessionKey($schedule->id), true);
        }

        $cellConfirmed = (bool) $request->session()->get($this->cellConfirmationSessionKey($schedule->id), false);

        return View::make('customTools.logistics.inventory.count', [
            'breadcrumbs' => $this->breadcrumbs('Inventario'),
            'schedule' => $schedule,
            'recountActive' => $recountActive,
            'cellConfirmed' => $cellConfirmed,
            'counts' => $this->groupCountsByReference($this->countsQuery($schedule->id)
                ->when($recountActive, fn ($query) => $query->where('recount_requested', true))
                ->orderBy('location')
                ->orderBy('reference')
                ->get()),
        ]);
    }

    public function countStore(Request $request, int $schedule)
    {
        abort_unless($this->canCountInventory($request), 403);

        $schedule = $this->scheduleById($schedule);

        if (!$schedule->preparation_done || $schedule->inventory_done) {
            return redirect()
                ->route('logistics.tools.inventory.index', ['date' => $schedule->inventory_date])
                ->with('warning', 'Esta celula nao esta disponivel para inventario.');
        }

        if ($request->has('cell_scan')) {
            $cellCode = strtoupper(trim((string) $request->input('cell_scan')));

            if ($cellCode !== strtoupper((string) $schedule->cell)) {
                return back()->with('warning', 'Indisponivel.');
            }

            $request->session()->put($this->cellConfirmationSessionKey($schedule->id), true);

            return back();
        }

        if (!$request->session()->get($this->cellConfirmationSessionKey($schedule->id), false)) {
            return back()->with('warning', 'Confirme primeiro a celula por scan.');
        }

        if ($request->boolean('finish')) {
            $recountActive = (int) $this->countsQuery($schedule->id)->where('recount_requested', true)->count() > 0;

            DB::transaction(function () use ($request, $schedule) {
                $recountActive = (int) DB::table('logistics_inventory_counts')
                    ->where('schedule_id', $schedule->id)
                    ->where('recount_requested', true)
                    ->count() > 0;

                if ($recountActive) {
                    DB::table('logistics_inventory_counts')
                        ->where('schedule_id', $schedule->id)
                        ->where('recount_requested', true)
                        ->whereNull('counted_quantity')
                        ->update([
                            'counted_quantity' => 0,
                            'counted_by' => $request->user()->id,
                            'counted_at' => now(),
                            'updated_at' => now(),
                        ]);

                    DB::table('logistics_inventory_counts')
                        ->where('schedule_id', $schedule->id)
                        ->where('recount_requested', true)
                        ->update([
                            'recount_requested' => false,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('logistics_inventory_counts')
                        ->where('schedule_id', $schedule->id)
                        ->whereNull('counted_quantity')
                        ->update([
                            'counted_quantity' => 0,
                            'counted_by' => $request->user()->id,
                            'counted_at' => now(),
                            'updated_at' => now(),
                        ]);
                }

                DB::table('logistics_inventory_schedules')
                    ->where('id', $schedule->id)
                    ->update([
                        'inventory_done' => true,
                        'inventory_operator_id' => $request->user()->id,
                        'inventory_done_at' => now(),
                        'updated_at' => now(),
                    ]);
            });

            $request->session()->forget($this->cellConfirmationSessionKey($schedule->id));

            return redirect()
                ->route('logistics.tools.inventory.work');
        }

        $code = strtoupper(trim((string) $request->input('scan_code')));

        if ($code === '') {
            return back()->with('warning', 'Leia o EAN ou referencia do produto.');
        }

        $recountActive = (int) $this->countsQuery($schedule->id)->where('recount_requested', true)->count() > 0;
        $matches = DB::table('logistics_inventory_counts')
            ->where('schedule_id', $schedule->id)
            ->when($recountActive, fn ($query) => $query->where('recount_requested', true))
            ->where(function ($query) use ($code) {
                $query->whereRaw('UPPER(reference) = ?', [$code])
                    ->orWhereRaw('UPPER(ean13) = ?', [$code]);
            })
            ->get();

        if ($matches->isEmpty()) {
            return back()->with('warning', $recountActive ? 'Este produto nao esta marcado para recontagem.' : 'Produto nao encontrado nesta celula.');
        }

        $matchedReferences = $matches
            ->map(fn ($row) => $this->normalizedReference($row->reference))
            ->unique()
            ->values();

        if ($matchedReferences->count() > 1) {
            return back()->with('warning', 'Referencia ambigua nesta celula. Use o EAN do produto.');
        }

        $row = $matches->first();
        $reference = $this->normalizedReference($row->reference);
        $countQuery = DB::table('logistics_inventory_counts')
            ->where('schedule_id', $schedule->id)
            ->when($recountActive, fn ($query) => $query->where('recount_requested', true));

        if ($reference !== '') {
            $countQuery->whereRaw('UPPER(reference) = ?', [$reference]);
        } else {
            $countQuery->where('id', $row->id);
        }

        $currentCountedQuantity = (int) $countQuery->max('counted_quantity');

        $updateQuery = DB::table('logistics_inventory_counts')
            ->where('schedule_id', $schedule->id)
            ->when($recountActive, fn ($query) => $query->where('recount_requested', true));

        if ($reference !== '') {
            $updateQuery->whereRaw('UPPER(reference) = ?', [$reference]);
        } else {
            $updateQuery->where('id', $row->id);
        }

        $updateQuery->update([
            'counted_quantity' => $currentCountedQuantity + 1,
            'counted_by' => $request->user()->id,
            'counted_at' => now(),
            'updated_at' => now(),
        ]);

        return back();
    }

    public function admin(Request $request)
    {
        $date = $this->date($request);

        return View::make('customTools.logistics.inventory.admin', [
            'breadcrumbs' => $this->breadcrumbs('Gestao'),
            'date' => $date,
            'schedules' => $this->schedulesFor($date),
            'stats' => $this->stats($date),
            'warehouseRows' => $this->warehouseRowsData(),
            'unavailableCells' => $this->pendingInventoryCells(),
        ]);
    }

    public function verify(Request $request, int $schedule)
    {
        $schedule = $this->scheduleById($schedule);

        if (!$schedule->inventory_done || $schedule->verification_done) {
            abort(403);
        }

        $counts = DB::table('logistics_inventory_counts')
            ->where('schedule_id', $schedule->id)
            ->get();

        if ($counts->contains(fn ($count) => $count->counted_quantity === null)) {
            return back()->with('error', 'Existem produtos sem contagem. Valide ou envie para recontagem antes de concluir.');
        }

        DB::beginTransaction();
        DB::connection('mysql2')->beginTransaction();

        try {
            $verifiedAt = now();
            $this->applyVerifiedInventoryStock($schedule, $counts, (int) $request->user()->id, $verifiedAt);

            DB::table('logistics_inventory_schedules')
                ->where('id', $schedule->id)
                ->update([
                    'verification_done' => true,
                    'verification_operator_id' => $request->user()->id,
                    'verification_done_at' => $verifiedAt,
                    'updated_at' => $verifiedAt,
                ]);

            DB::connection('mysql2')->commit();
            DB::commit();
        } catch (\Throwable $exception) {
            if (DB::connection('mysql2')->transactionLevel() > 0) {
                DB::connection('mysql2')->rollBack();
            }

            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            report($exception);

            return back()->with('error', 'Nao foi possivel aplicar o stock do inventario.');
        }

        return back()->with('success', 'Inventario validado.');
    }

    public function requestRecount(Request $request, int $count)
    {
        $row = DB::table('logistics_inventory_counts as c')
            ->join('logistics_inventory_schedules as s', 's.id', '=', 'c.schedule_id')
            ->where('c.id', $count)
            ->select('c.*', 's.inventory_done', 's.verification_done', 's.inventory_date')
            ->first();

        if (!$row) {
            abort(404);
        }

        if (!$row->inventory_done || $row->verification_done) {
            abort(403);
        }

        DB::transaction(function () use ($row) {
            $reference = $this->normalizedReference($row->reference);
            $counts = DB::table('logistics_inventory_counts')
                ->where('schedule_id', $row->schedule_id);

            if ($reference !== '') {
                $counts->whereRaw('UPPER(reference) = ?', [$reference]);
            } else {
                $counts->where('id', $row->id);
            }

            $counts->update([
                'counted_quantity' => null,
                'counted_by' => null,
                'counted_at' => null,
                'recount_requested' => true,
                'updated_at' => now(),
            ]);

            DB::table('logistics_inventory_schedules')
                ->where('id', $row->schedule_id)
                ->update([
                    'inventory_done' => false,
                    'inventory_operator_id' => null,
                    'inventory_done_at' => null,
                    'verification_done' => false,
                    'verification_operator_id' => null,
                    'verification_done_at' => null,
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('logistics.tools.inventory.admin.verification', ['date' => $row->inventory_date])
            ->with('success', 'Produto enviado para recontagem.');
    }

    public function saveVerificationComment(Request $request, int $count)
    {
        $data = $request->validate([
            'verification_comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $row = DB::table('logistics_inventory_counts as c')
            ->join('logistics_inventory_schedules as s', 's.id', '=', 'c.schedule_id')
            ->where('c.id', $count)
            ->select('c.id', 's.inventory_done', 's.verification_done', 's.inventory_date')
            ->first();

        if (!$row) {
            abort(404);
        }

        if (!$row->inventory_done || $row->verification_done) {
            abort(403);
        }

        DB::table('logistics_inventory_counts')
            ->where('id', $row->id)
            ->update([
                'verification_comment' => trim((string) ($data['verification_comment'] ?? '')) ?: null,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('logistics.tools.inventory.admin.verification', ['date' => $row->inventory_date])
            ->with('success', 'Comentario guardado.');
    }

    public function map(Request $request)
    {
        $date = $this->date($request);

        return View::make('customTools.logistics.inventory.map', [
            'breadcrumbs' => $this->breadcrumbs('Mapa'),
            'date' => $date,
            'warehouseRows' => $this->warehouseRowsData(),
        ]);
    }

    public function mapColumns(Request $request)
    {
        return response()->json([
            'columns' => $this->warehouseColumnsData(strtoupper(trim((string) $request->query('row')))),
        ]);
    }

    public function mapCells(Request $request)
    {
        return response()->json([
            'cells' => $this->warehouseCellsData(
                strtoupper(trim((string) $request->query('row'))),
                strtoupper(trim((string) $request->query('column')))
            ),
            'unavailable' => $this->pendingInventoryCells(),
        ]);
    }

    public function mapProducts(Request $request)
    {
        return response()->json([
            'products' => $this->warehouseProductsData(strtoupper(trim((string) $request->query('cell')))),
        ]);
    }

    public function scheduleStore(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'cells' => ['required', 'array'],
            'cells.*' => ['nullable', 'string', 'max:64'],
        ]);

        $pendingCells = $this->pendingInventoryCells();

        foreach ($data['cells'] as $cell) {
            $cell = trim((string) $cell);
            if ($cell !== '' && !in_array(strtoupper($cell), $pendingCells, true)) {
                $this->schedule($data['date'], $cell);
            }
        }

        return back()->with('success', 'Plano atualizado.');
    }

    public function scheduleDestroy(int $schedule)
    {
        $schedule = $this->scheduleById($schedule);

        if ($schedule->verification_done) {
            return back()->with('warning', 'Nao e possivel remover uma celula de inventario ja validada.');
        }

        DB::table('logistics_inventory_schedules')->where('id', $schedule->id)->delete();

        return back()->with('success', 'Celula removida.');
    }

    public function verification(Request $request)
    {
        $date = $this->date($request);
        $cell = strtoupper(trim((string) $request->query('cell')));

        $rows = DB::table('logistics_inventory_counts as c')
            ->join('logistics_inventory_schedules as s', 's.id', '=', 'c.schedule_id')
            ->where('s.inventory_date', $date)
            ->where('s.inventory_done', true)
            ->when($cell !== '', fn ($query) => $query->where('s.cell', $cell))
            ->select('c.*', 's.cell')
            ->orderBy('s.cell')
            ->orderBy('c.location')
            ->orderBy('c.reference')
            ->get();
        $pendingSales = $this->pendingSalesQuantitiesForCounts($rows);
        $rows = $rows->map(function ($row) use ($pendingSales) {
            $key = (int) $row->id_product . ':' . (int) $row->id_product_attribute;
            $row->pending_sales_by_state = $pendingSales[$key] ?? [
                'payment_accepted' => 0,
                'preparation' => 0,
                'backorders' => 0,
                'waiting_info' => 0,
            ];
            $row->pending_sales_quantity = array_sum($row->pending_sales_by_state);

            return $row;
        });
        $rows = $this->groupVerificationRowsByReference($rows);

        return View::make('customTools.logistics.inventory.verification', [
            'breadcrumbs' => $this->breadcrumbs('Verificacao'),
            'date' => $date,
            'selectedCell' => $cell,
            'rows' => $rows,
            'schedules' => $this->schedulesFor($date)->where('inventory_done', true)->values(),
            'stats' => $this->stats($date),
        ]);
    }

    public function report(Request $request)
    {
        $date = $this->date($request);

        return View::make('customTools.logistics.inventory.report', [
            'breadcrumbs' => $this->breadcrumbs('Relatorio'),
            'date' => $date,
            'report' => $this->reportData($date),
        ]);
    }

    public function reportCsv(Request $request)
    {
        $date = $this->date($request);
        $report = $this->reportData($date);
        $filename = 'inventory_report_' . str_replace('-', '', $date) . '.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['date', 'cell', 'line', 'column', 'inventory_users', 'verification_users', 'inventory_done_at', 'verification_done_at', 'note', 'product_comments']);

            foreach ($report['schedules'] as $schedule) {
                fputcsv($handle, [
                    $schedule->inventory_date,
                    $schedule->cell,
                    $schedule->line,
                    $schedule->column,
                    $schedule->inventory_users,
                    $schedule->verification_users,
                    $schedule->inventory_done_at,
                    $schedule->verification_done_at,
                    $schedule->date_note,
                    $schedule->verification_comments,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function reportPdf(Request $request)
    {
        $date = $this->date($request);
        $report = $this->reportData($date);
        $filename = 'inventory_report_' . str_replace('-', '', $date) . '.pdf';

        $tcpdfCachePath = storage_path('framework/cache/tcpdf/');
        if (!is_dir($tcpdfCachePath)) {
            mkdir($tcpdfCachePath, 0775, true);
        }

        if (!defined('K_PATH_CACHE')) {
            define('K_PATH_CACHE', $tcpdfCachePath);
        }

        require_once app_path('Libraries/tcpdf/tcpdf.php');

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Webtools');
        $pdf->SetAuthor('All Stars');
        $pdf->SetTitle($filename);
        $pdf->SetMargins(8, 8, 8);
        $pdf->SetAutoPageBreak(true, 8);
        $pdf->AddPage();
        $pdf->writeHTML(View::make('customTools.logistics.inventory.report_pdf', compact('date', 'report'))->render(), true, false, true, false, '');

        return response($pdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function breadcrumbs(?string $page = null): array
    {
        $breadcrumbs = [
            ['name' => trans('Logistics'), 'url' => route('logistics.index')],
            ['name' => 'Inventory', 'url' => route('logistics.tools.inventory.index'), 'no_translation' => 1],
        ];

        if ($page) {
            $breadcrumbs[] = ['name' => $page, 'url' => '#', 'no_translation' => 1];
        }

        return $breadcrumbs;
    }

    private function date(Request $request): string
    {
        return Carbon::parse($request->input('date', now()->toDateString()))->toDateString();
    }

    private function schedule(string $date, string $cell): object
    {
        $cell = strtoupper(trim($cell));

        DB::table('logistics_inventory_schedules')->updateOrInsert(
            ['inventory_date' => $date, 'cell' => $cell],
            ['updated_at' => now(), 'created_at' => now()]
        );

        return DB::table('logistics_inventory_schedules')
            ->where('inventory_date', $date)
            ->where('cell', $cell)
            ->first();
    }

    private function scheduleById(int $id): object
    {
        $schedule = DB::table('logistics_inventory_schedules')->where('id', $id)->first();

        if (!$schedule) {
            abort(404);
        }

        return $schedule;
    }

    private function schedulesFor(string $date)
    {
        return DB::table('logistics_inventory_schedules')
            ->where('inventory_date', $date)
            ->orderBy('cell')
            ->get()
            ->map(function ($schedule) {
                $schedule->total_rows = $this->countsQuery($schedule->id)->count();
                $schedule->counted_rows = $this->countsQuery($schedule->id)->whereNotNull('counted_quantity')->count();
                $schedule->diff_rows = $this->countsQuery($schedule->id)
                    ->whereNotNull('counted_quantity')
                    ->whereColumn('counted_quantity', '!=', 'current_quantity')
                    ->count();

                return $schedule;
            });
    }

    private function stats(string $date): array
    {
        $schedules = DB::table('logistics_inventory_schedules')->where('inventory_date', $date);
        $scheduleIds = (clone $schedules)->pluck('id');
        $counts = DB::table('logistics_inventory_counts')->whereIn('schedule_id', $scheduleIds);

        return [
            'cells' => (clone $schedules)->count(),
            'prepared' => (clone $schedules)->where('preparation_done', true)->count(),
            'done' => (clone $schedules)->where('inventory_done', true)->count(),
            'verified' => (clone $schedules)->where('verification_done', true)->count(),
            'rows' => (clone $counts)->count(),
            'differences' => (clone $counts)
                ->whereNotNull('counted_quantity')
                ->whereColumn('counted_quantity', '!=', 'current_quantity')
                ->count(),
        ];
    }

    private function countsQuery(int $scheduleId)
    {
        return DB::table('logistics_inventory_counts')->where('schedule_id', $scheduleId);
    }

    private function scheduleHousingGroups($schedules)
    {
        return $schedules
            ->map(function ($schedule) {
                $parsed = $this->parseHousingCell((string) $schedule->cell);
                $schedule->housing_group = $parsed['row'] ?? strtoupper(strtok((string) $schedule->cell, '-') ?: (string) $schedule->cell);

                return $schedule;
            })
            ->groupBy('housing_group')
            ->map(function ($items, $housing) {
                return (object) [
                    'housing' => $housing,
                    'count' => $items->count(),
                    'diff_rows' => $items->sum(fn ($schedule) => (int) ($schedule->diff_rows ?? 0)),
                    'counted_rows' => $items->sum(fn ($schedule) => (int) ($schedule->counted_rows ?? 0)),
                    'total_rows' => $items->sum(fn ($schedule) => (int) ($schedule->total_rows ?? 0)),
                    'schedules' => $items->sortBy('cell')->values(),
                ];
            })
            ->sortKeys()
            ->values();
    }

    private function cellConfirmationSessionKey(int $scheduleId): string
    {
        return 'logistics_inventory_cell_confirmed_' . $scheduleId;
    }

    private function normalizedReference(?string $reference): string
    {
        return strtoupper(trim((string) $reference));
    }

    private function groupCountsByReference($counts)
    {
        return $counts
            ->groupBy(fn ($row) => $this->normalizedReference($row->reference) ?: 'stock:' . $row->id_stock_available)
            ->map(function ($items) {
                $first = $items->first();
                $countedValues = $items
                    ->pluck('counted_quantity')
                    ->filter(fn ($quantity) => $quantity !== null);

                $first->ean13 = $items
                    ->pluck('ean13')
                    ->filter()
                    ->unique()
                    ->implode(' / ');
                $first->current_quantity = $items->sum('current_quantity');
                $first->counted_quantity = $countedValues->isEmpty() ? null : (int) $countedValues->max();

                return $first;
            })
            ->sortBy('reference')
            ->values();
    }

    private function groupVerificationRowsByReference($rows)
    {
        return $rows
            ->groupBy(fn ($row) => $row->cell . '|' . ($this->normalizedReference($row->reference) ?: 'stock:' . $row->id_stock_available))
            ->map(function ($items) {
                $first = $items->first();
                $countedValues = $items
                    ->pluck('counted_quantity')
                    ->filter(fn ($quantity) => $quantity !== null);
                $pendingSalesByState = [
                    'payment_accepted' => 0,
                    'preparation' => 0,
                    'backorders' => 0,
                    'waiting_info' => 0,
                ];

                foreach ($items as $item) {
                    foreach ($pendingSalesByState as $state => $quantity) {
                        $pendingSalesByState[$state] += (int) ($item->pending_sales_by_state[$state] ?? 0);
                    }
                }

                $locations = $items
                    ->pluck('location')
                    ->filter()
                    ->unique()
                    ->values();

                $comments = $items
                    ->pluck('verification_comment')
                    ->filter()
                    ->unique()
                    ->values();

                $first->id = $first->id;
                $first->location = $locations->count() > 1 ? $locations->implode(' / ') : ($locations->first() ?: null);
                $first->current_quantity = (int) $items->max('current_quantity');
                $first->counted_quantity = $countedValues->isEmpty() ? null : (int) $countedValues->max();
                $first->pending_sales_by_state = $pendingSalesByState;
                $first->pending_sales_quantity = array_sum($pendingSalesByState);
                $first->verification_comment = $comments->implode(' | ');
                $first->grouped_rows = $items->count();

                return $first;
            })
            ->sortBy([
                ['cell', 'asc'],
                ['reference', 'asc'],
            ])
            ->values();
    }

    private function applyVerifiedInventoryStock(object $schedule, $counts, int $validatedBy, Carbon $validatedAt): void
    {
        $stockTable = $this->psTable('stock_available');

        foreach ($counts->groupBy(fn ($count) => $this->normalizedReference($count->reference) ?: 'stock:' . $count->id_stock_available) as $groupKey => $referenceCounts) {
            $sourceCount = $referenceCounts->first();
            $newQuantity = (int) $referenceCounts->max('counted_quantity');
            $reference = str_starts_with((string) $groupKey, 'stock:') ? '' : (string) $groupKey;
            $targetStocks = $reference !== ''
                ? $this->stockRowsForReference($reference)
                : DB::connection('mysql2')
                    ->table($stockTable)
                    ->where('id_stock_available', (int) $sourceCount->id_stock_available)
                    ->lockForUpdate()
                    ->get();

            if ($targetStocks->isEmpty()) {
                throw new \RuntimeException('Stock rows not found for reference: ' . ($reference ?: $sourceCount->id_stock_available));
            }

            foreach ($targetStocks->unique('id_stock_available') as $stock) {
                $previousQuantity = (int) $stock->quantity;

                if ($previousQuantity === $newQuantity) {
                    continue;
                }

                DB::connection('mysql2')
                    ->table($stockTable)
                    ->where('id_stock_available', (int) $stock->id_stock_available)
                    ->update([
                        'quantity' => $newQuantity,
                    ]);

                DB::table('logistics_inventory_stock_logs')->insert([
                    'schedule_id' => (int) $schedule->id,
                    'count_id' => (int) $sourceCount->id,
                    'id_stock_available' => (int) $stock->id_stock_available,
                    'id_product' => (int) $stock->id_product,
                    'id_product_attribute' => (int) $stock->id_product_attribute,
                    'reference' => $stock->reference ?: $sourceCount->reference,
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $newQuantity,
                    'quantity_delta' => $newQuantity - $previousQuantity,
                    'reason' => 'inventory',
                    'validated_by' => $validatedBy,
                    'validated_at' => $validatedAt,
                    'created_at' => $validatedAt,
                    'updated_at' => $validatedAt,
                ]);
            }
        }
    }

    private function stockRowsForReference(string $reference)
    {
        $reference = $this->normalizedReference($reference);
        $productTable = $this->psTable('product');
        $attributeTable = $this->psTable('product_attribute');
        $stockTable = $this->psTable('stock_available');

        $products = DB::connection('mysql2')
            ->table($productTable . ' as p')
            ->leftJoin($this->psTable('pack') . ' as pack', 'pack.id_product_pack', '=', 'p.id_product')
            ->join($stockTable . ' as s', function ($join) {
                $join->on('s.id_product', '=', 'p.id_product')
                    ->where('s.id_product_attribute', '=', 0);
            })
            ->whereNull('pack.id_product_pack')
            ->where(function ($query) {
                $query->whereNull('p.cache_is_pack')->orWhere('p.cache_is_pack', 0);
            })
            ->whereRaw('UPPER(p.reference) = ?', [$reference])
            ->select('s.id_stock_available', 's.id_product', 's.id_product_attribute', 'p.reference', 's.quantity');

        $attributes = DB::connection('mysql2')
            ->table($attributeTable . ' as pa')
            ->join($productTable . ' as p', 'p.id_product', '=', 'pa.id_product')
            ->leftJoin($this->psTable('pack') . ' as pack', 'pack.id_product_pack', '=', 'pa.id_product')
            ->join($stockTable . ' as s', 's.id_product_attribute', '=', 'pa.id_product_attribute')
            ->whereNull('pack.id_product_pack')
            ->where(function ($query) {
                $query->whereNull('p.cache_is_pack')->orWhere('p.cache_is_pack', 0);
            })
            ->whereRaw('UPPER(pa.reference) = ?', [$reference])
            ->select('s.id_stock_available', 's.id_product', 's.id_product_attribute', 'pa.reference', 's.quantity');

        return $products
            ->lockForUpdate()
            ->get()
            ->merge($attributes->lockForUpdate()->get())
            ->unique('id_stock_available')
            ->values();
    }

    private function productsForCell(string $cell)
    {
        $cell = strtoupper(trim($cell));
        $like = $cell . '-%';
        $productTable = $this->psTable('product');
        $customProductTable = $this->psTable('custom_product');
        $attributeTable = $this->psTable('product_attribute');
        $customAttributeTable = $this->psTable('custom_product_attribute');
        $stockTable = $this->psTable('stock_available');

        $products = DB::connection('mysql2')
            ->table($productTable . ' as p')
            ->leftJoin($customProductTable . ' as cp', 'cp.id_product', '=', 'p.id_product')
            ->leftJoin($this->psTable('pack') . ' as pack', 'pack.id_product_pack', '=', 'p.id_product')
            ->join($stockTable . ' as s', function ($join) {
                $join->on('s.id_product', '=', 'p.id_product')
                    ->where('s.id_product_attribute', '=', 0);
            })
            ->whereNull('pack.id_product_pack')
            ->where(function ($query) {
                $query->whereNull('p.cache_is_pack')->orWhere('p.cache_is_pack', 0);
            })
            ->where(function ($query) use ($cell, $like) {
                $query->where('p.location', $cell)
                    ->orWhere('p.location', 'like', $like);
            })
            ->select(
                's.id_stock_available',
                's.id_product',
                's.id_product_attribute',
                'p.reference',
                'p.ean13',
                'p.location',
                DB::raw('COALESCE(cp.stock_arrive, 0) as stock_arrive'),
                's.quantity'
            );

        $attributes = DB::connection('mysql2')
            ->table($attributeTable . ' as pa')
            ->join($productTable . ' as p', 'p.id_product', '=', 'pa.id_product')
            ->join($customAttributeTable . ' as cpa', 'cpa.id_product_attribute', '=', 'pa.id_product_attribute')
            ->leftJoin($this->psTable('pack') . ' as pack', 'pack.id_product_pack', '=', 'pa.id_product')
            ->join($stockTable . ' as s', 's.id_product_attribute', '=', 'pa.id_product_attribute')
            ->whereNull('pack.id_product_pack')
            ->where(function ($query) {
                $query->whereNull('p.cache_is_pack')->orWhere('p.cache_is_pack', 0);
            })
            ->where(function ($query) use ($cell, $like) {
                $query->where('cpa.location', $cell)->orWhere('cpa.location', 'like', $like);
            })
            ->select(
                's.id_stock_available',
                's.id_product',
                's.id_product_attribute',
                'pa.reference',
                'pa.ean13',
                'cpa.location',
                DB::raw('COALESCE(cpa.stock_arrive, 0) as stock_arrive'),
                's.quantity'
            );

        return $products
            ->get()
            ->merge($attributes->get())
            ->unique('id_stock_available')
            ->values();
    }

    private function warehouseRowsData(): array
    {
        return $this->warehouseLocationRows()
            ->map(fn ($item) => $this->parseHousingCell(strtoupper((string) $item->location)))
            ->filter()
            ->groupBy('row')
            ->map(fn ($items, $row) => [
                'row' => $row,
                'columns_count' => $items->pluck('column')->unique()->count(),
                'cells_count' => $items->pluck('cell')->unique()->count(),
            ])
            ->sortKeys()
            ->values()
            ->all();
    }

    private function warehouseColumnsData(string $row): array
    {
        return $this->warehouseLocationRows()
            ->map(fn ($item) => $this->parseHousingCell(strtoupper((string) $item->location)))
            ->filter(fn ($item) => $item && $item['row'] === $row)
            ->groupBy('column')
            ->map(fn ($items, $column) => [
                'column' => $column,
                'cells_count' => $items->pluck('cell')->unique()->count(),
            ])
            ->sortKeys()
            ->values()
            ->all();
    }

    private function warehouseCellsData(string $row, string $column): array
    {
        $lastInventoryByCell = DB::table('logistics_inventory_schedules')
            ->where('verification_done', true)
            ->select('cell', DB::raw('MAX(inventory_date) as last_inventory_date'))
            ->groupBy('cell')
            ->pluck('last_inventory_date', 'cell')
            ->mapWithKeys(fn ($date, $cell) => [strtoupper((string) $cell) => $date]);

        return $this->warehouseLocationRows()
            ->map(function ($item) {
                $parsed = $this->parseHousingCell(strtoupper((string) $item->location));

                if (!$parsed) {
                    return null;
                }

                return $parsed + [
                    'product_key' => (int) $item->id_product . ':' . (int) $item->id_product_attribute,
                ];
            })
            ->filter(fn ($item) => $item && $item['row'] === $row && $item['column'] === $column)
            ->groupBy('cell')
            ->map(function ($items, $cell) use ($lastInventoryByCell) {
                $lastDate = $lastInventoryByCell[$cell] ?? null;

                return [
                    'cell' => $cell,
                    'product_count' => $items->pluck('product_key')->unique()->count(),
                    'last_inventory_date' => $lastDate,
                    'age_status' => $this->inventoryAgeStatus($lastDate),
                ];
            })
            ->sortKeys()
            ->values()
            ->all();
    }

    private function warehouseProductsData(string $cell): array
    {
        $products = $this->productsForCell($cell);
        $activeOrders = $this->activeOrdersByProduct($products);
        $lastInventory = $this->lastInventoryByProduct($products);

        return $products
            ->map(function ($product) use ($activeOrders, $lastInventory) {
                $key = (int) $product->id_product . ':' . (int) $product->id_product_attribute;
                $last = $lastInventory[$key] ?? null;

                return [
                    'key' => $key,
                    'reference' => $product->reference ?: '-',
                    'current_quantity' => (int) $product->quantity,
                    'stock_arrive' => (int) ($product->stock_arrive ?? 0),
                    'active_orders' => (int) ($activeOrders[$key] ?? 0),
                    'last_inventory_date' => $last->last_inventory_date ?? null,
                    'last_inventory_user' => $last->user_name ?? null,
                ];
            })
            ->groupBy('reference')
            ->map(function ($items, $reference) {
                $last = $items
                    ->filter(fn ($item) => !empty($item['last_inventory_date']))
                    ->sortByDesc('last_inventory_date')
                    ->first();

                return [
                    'reference' => $reference,
                    'variants' => $items->count(),
                    'current_quantity' => $items->sum('current_quantity'),
                    'stock_arrive' => $items->sum('stock_arrive'),
                    'active_orders' => $items->sum('active_orders'),
                    'last_inventory_date' => $last['last_inventory_date'] ?? null,
                    'last_inventory_user' => $last['last_inventory_user'] ?? null,
                ];
            })
            ->sortBy('reference')
            ->values()
            ->all();
    }

    private function activeOrdersByProduct($products): array
    {
        $result = [];
        $activeStates = [2, 3, 15, 30];

        foreach ($products as $product) {
            $key = (int) $product->id_product . ':' . (int) $product->id_product_attribute;
            $query = DB::connection('mysql2')
                ->table($this->psTable('order_detail') . ' as od')
                ->join($this->psTable('orders') . ' as o', 'o.id_order', '=', 'od.id_order')
                ->where('od.product_id', (int) $product->id_product)
                ->whereIn('o.current_state', $activeStates);

            if ((int) $product->id_product_attribute > 0) {
                $query->where('od.product_attribute_id', (int) $product->id_product_attribute);
            } else {
                $query->where(function ($q) {
                    $q->whereNull('od.product_attribute_id')->orWhere('od.product_attribute_id', 0);
                });
            }

            $result[$key] = (int) $query->count();
        }

        return $result;
    }

    private function pendingSalesQuantitiesForCounts($rows): array
    {
        if ($rows->isEmpty()) {
            return [];
        }

        $states = [
            2 => 'payment_accepted',
            3 => 'preparation',
            15 => 'backorders',
            30 => 'waiting_info',
        ];
        $result = [];

        foreach ($rows->unique(fn ($row) => (int) $row->id_product . ':' . (int) $row->id_product_attribute) as $row) {
            $key = (int) $row->id_product . ':' . (int) $row->id_product_attribute;
            $result[$key] = [
                'payment_accepted' => 0,
                'preparation' => 0,
                'backorders' => 0,
                'waiting_info' => 0,
            ];

            $query = DB::connection('mysql2')
                ->table($this->psTable('order_detail') . ' as od')
                ->join($this->psTable('orders') . ' as o', 'o.id_order', '=', 'od.id_order')
                ->where('od.product_id', (int) $row->id_product)
                ->whereIn('o.current_state', array_keys($states));

            if ((int) $row->id_product_attribute > 0) {
                $query->where('od.product_attribute_id', (int) $row->id_product_attribute);
            } else {
                $query->where(function ($q) {
                    $q->whereNull('od.product_attribute_id')->orWhere('od.product_attribute_id', 0);
                });
            }

            foreach ($query->select('o.current_state', DB::raw('SUM(od.product_quantity) as quantity'))->groupBy('o.current_state')->get() as $stateRow) {
                $label = $states[(int) $stateRow->current_state] ?? null;

                if ($label) {
                    $result[$key][$label] = (int) $stateRow->quantity;
                }
            }

            $packQuery = DB::connection('mysql2')
                ->table($this->psTable('order_detail') . ' as od')
                ->join($this->psTable('orders') . ' as o', 'o.id_order', '=', 'od.id_order')
                ->join($this->psTable('pack') . ' as pack', 'pack.id_product_pack', '=', 'od.product_id')
                ->where('pack.id_product_item', (int) $row->id_product)
                ->whereIn('o.current_state', array_keys($states));

            if ((int) $row->id_product_attribute > 0) {
                $packQuery->where('pack.id_product_attribute_item', (int) $row->id_product_attribute);
            } else {
                $packQuery->where('pack.id_product_attribute_item', 0);
            }

            foreach ($packQuery->select('o.current_state', DB::raw('SUM(od.product_quantity * pack.quantity) as quantity'))->groupBy('o.current_state')->get() as $stateRow) {
                $label = $states[(int) $stateRow->current_state] ?? null;

                if ($label) {
                    $result[$key][$label] += (int) $stateRow->quantity;
                }
            }
        }

        return $result;
    }

    private function lastInventoryByProduct($products): array
    {
        if ($products->isEmpty()) {
            return [];
        }

        $query = DB::table('logistics_inventory_counts as c')
            ->join('logistics_inventory_schedules as s', 's.id', '=', 'c.schedule_id')
            ->leftJoin('users as u', 'u.id', '=', 'c.counted_by')
            ->where('s.verification_done', true)
            ->whereNotNull('c.counted_at')
            ->where(function ($builder) use ($products) {
                foreach ($products->values() as $index => $product) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $builder->{$method}(function ($q) use ($product) {
                        $q->where('c.id_product', (int) $product->id_product)
                            ->where('c.id_product_attribute', (int) $product->id_product_attribute);
                    });
                }
            })
            ->select(
                'c.id_product',
                'c.id_product_attribute',
                's.inventory_date as last_inventory_date',
                'u.name as user_name',
                'c.counted_at'
            )
            ->orderByDesc('c.counted_at')
            ->get();

        return $query
            ->unique(fn ($row) => (int) $row->id_product . ':' . (int) $row->id_product_attribute)
            ->mapWithKeys(fn ($row) => [(int) $row->id_product . ':' . (int) $row->id_product_attribute => $row])
            ->all();
    }

    private function inventoryAgeStatus(?string $lastDate): string
    {
        if (!$lastDate) {
            return 'danger';
        }

        $months = Carbon::parse($lastDate)->diffInMonths(now());

        if ($months >= 6) {
            return 'danger';
        }

        if ($months >= 3) {
            return 'warning';
        }

        return 'ok';
    }

    private function warehouseLocationRows()
    {
        $productRows = DB::connection('mysql2')
            ->table($this->psTable('product') . ' as p')
            ->leftJoin($this->psTable('pack') . ' as pack', 'pack.id_product_pack', '=', 'p.id_product')
            ->whereNull('pack.id_product_pack')
            ->where(function ($query) {
                $query->whereNull('p.cache_is_pack')->orWhere('p.cache_is_pack', 0);
            })
            ->whereNotNull('p.location')
            ->where('p.location', '!=', '')
            ->select(
                'p.id_product',
                DB::raw('0 as id_product_attribute'),
                'p.location'
            )
            ->get();

        $attributeRows = DB::connection('mysql2')
            ->table($this->psTable('product_attribute') . ' as pa')
            ->join($this->psTable('product') . ' as p', 'p.id_product', '=', 'pa.id_product')
            ->join($this->psTable('custom_product_attribute') . ' as cpa', 'cpa.id_product_attribute', '=', 'pa.id_product_attribute')
            ->leftJoin($this->psTable('pack') . ' as pack', 'pack.id_product_pack', '=', 'pa.id_product')
            ->whereNull('pack.id_product_pack')
            ->where(function ($query) {
                $query->whereNull('p.cache_is_pack')->orWhere('p.cache_is_pack', 0);
            })
            ->whereNotNull('cpa.location')
            ->where('cpa.location', '!=', '')
            ->select(
                'pa.id_product',
                'pa.id_product_attribute',
                'cpa.location'
            )
            ->get();

        return $productRows->merge($attributeRows);
    }

    private function pendingInventoryCells(): array
    {
        return DB::table('logistics_inventory_schedules')
            ->where('verification_done', false)
            ->pluck('cell')
            ->map(fn ($cell) => strtoupper((string) $cell))
            ->unique()
            ->values()
            ->all();
    }

    private function reportData(string $date): array
    {
        $schedules = DB::table('logistics_inventory_schedules as s')
            ->leftJoin('users as vu', 'vu.id', '=', 's.verification_operator_id')
            ->where('s.inventory_date', $date)
            ->where('s.inventory_done', true)
            ->select('s.*', 'vu.name as verification_user_name')
            ->orderBy('s.cell')
            ->get();

        $scheduleIds = $schedules->pluck('id')->all();
        $counts = DB::table('logistics_inventory_counts as c')
            ->leftJoin('users as cu', 'cu.id', '=', 'c.counted_by')
            ->whereIn('c.schedule_id', $scheduleIds)
            ->select('c.*', 'cu.name as counted_user_name')
            ->get()
            ->groupBy('schedule_id');

        $reportSchedules = $schedules->map(function ($schedule) use ($counts) {
            $parsed = $this->parseHousingCell((string) $schedule->cell) ?: [
                'row' => '-',
                'column' => '-',
                'cell' => $schedule->cell,
            ];

            $scheduleCounts = $counts[$schedule->id] ?? collect();
            $inventoryUsers = $scheduleCounts
                ->pluck('counted_user_name')
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');
            $inventoryDate = $schedule->inventory_done_at ? Carbon::parse($schedule->inventory_done_at)->toDateString() : null;
            $verificationDate = $schedule->verification_done_at ? Carbon::parse($schedule->verification_done_at)->toDateString() : null;

            $schedule->line = $parsed['row'];
            $schedule->column = $parsed['row'] . '-' . $parsed['column'];
            $schedule->inventory_users = $inventoryUsers ?: '-';
            $schedule->verification_users = $schedule->verification_user_name ?: '-';
            $schedule->date_note = $schedule->verification_done && $inventoryDate !== $verificationDate
                ? 'Data de verificacao diferente da data de inventario'
                : '';
            $schedule->total_rows = $scheduleCounts->count();
            $schedule->diff_rows = $scheduleCounts
                ->filter(fn ($count) => $count->counted_quantity !== null && (int) $count->counted_quantity !== (int) $count->current_quantity)
                ->count();
            $schedule->verification_comments = $scheduleCounts
                ->filter(fn ($count) => !empty($count->verification_comment))
                ->map(fn ($count) => ($count->reference ?: '-') . ': ' . $count->verification_comment)
                ->values()
                ->implode(' | ');

            return $schedule;
        });

        return [
            'schedules' => $reportSchedules,
            'lines' => $reportSchedules->pluck('line')->unique()->sort()->values(),
            'columns' => $reportSchedules->pluck('column')->unique()->sort()->values(),
            'cells' => $reportSchedules->pluck('cell')->unique()->sort()->values(),
            'inventory_users' => $reportSchedules->pluck('inventory_users')
                ->flatMap(fn ($users) => collect(explode(',', $users))->map(fn ($user) => trim($user)))
                ->filter(fn ($user) => $user !== '-')
                ->unique()
                ->values(),
            'verification_users' => $reportSchedules->pluck('verification_users')
                ->filter(fn ($user) => $user !== '-')
                ->unique()
                ->values(),
            'notes' => $reportSchedules
                ->filter(fn ($schedule) => $schedule->date_note !== '')
                ->map(fn ($schedule) => $schedule->cell . ': ' . $schedule->date_note)
                ->values(),
        ];
    }

    private function parseHousingCell(string $location): ?array
    {
        if (!preg_match('/^([A-Za-z0-9]{2})-([A-Za-z0-9]{2})-([A-Za-z0-9]{2})$/', $location, $matches)) {
            return null;
        }

        return [
            'row' => strtoupper($matches[1]),
            'column' => strtoupper($matches[2]),
            'cell' => strtoupper($matches[0]),
        ];
    }

    private function psTable(string $table): string
    {
        $prefix = (string) env('DB2_DB_prefix', 'ps_');

        return str_contains($prefix, '.') ? $prefix . $table : $prefix . $table;
    }

    private function isManager(Request $request): bool
    {
        return in_array($request->user()?->role, ['admin', 'manager'], true);
    }

    private function canPrepareInventory(Request $request): bool
    {
        if ($this->isManager($request)) {
            return true;
        }

        return $request->user()?->role === 'user'
            && in_array($this->inventoryProfile($request), ['preparation', 'both'], true);
    }

    private function canCountInventory(Request $request): bool
    {
        if ($this->isManager($request)) {
            return true;
        }

        return $request->user()?->role === 'user'
            && in_array($this->inventoryProfile($request), ['inventory', 'both'], true);
    }

    private function postInventoryRoute(Request $request): string
    {
        return $this->isManager($request)
            ? 'logistics.tools.inventory.index'
            : 'logistics.tools.inventory.work';
    }

    private function inventoryProfile(Request $request): ?string
    {
        $profile = strtolower(trim((string) $request->user()?->inventory_profile));
        $profile = str_replace(['ç', 'ã', 'á', 'é', 'í', 'ó', 'ú'], ['c', 'a', 'a', 'e', 'i', 'o', 'u'], $profile);

        return match ($profile) {
            'preparation', 'inventory_preparation', 'prep', 'preparacao' => 'preparation',
            'inventory', 'inventory_count', 'count', 'inventario' => 'inventory',
            'both', 'all', 'preparation_inventory', 'inventario_preparacao' => 'both',
            default => null,
        };
    }
}
