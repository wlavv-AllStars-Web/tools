<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;
use App\Services\Web\ProductExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class WebProductExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, ProductExportService $service)
    {
        $files = $service->files();
        $selected = $request->query('file');
        $data = $service->read($selected);

        return View::make('areas.web.product-export')->with([
            'breadcrumbs' => [
                ['name' => trans('web'), 'url' => route('web.index')],
                ['name' => 'product_export', 'url' => route('web.product_export.index')],
            ],
            'files' => $files,
            'selectedFile' => $data['filename'],
            'headers' => $data['headers'],
            'rows' => $data['rows'],
            'counters' => $this->counters($data['rows']),
        ]);
    }

    public function generate(ProductExportService $service): RedirectResponse
    {
        try {
            $result = $service->export();

            return redirect()
                ->route('web.product_export.index', ['file' => $result['filename']])
                ->with('success', trans('messages.product_export_generated', [
                    'filename' => $result['filename'],
                    'rows' => $result['rows'],
                ]));
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('web.product_export.index')
                ->with('error', trans('messages.product_export_failed', ['error' => $e->getMessage()]));
        }
    }

    public function download(string $filename, ProductExportService $service): BinaryFileResponse|Response
    {
        $path = $service->pathFor($filename);

        if (! is_file($path)) {
            abort(404);
        }

        return response()->download($path, basename($path), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function counters($rows): array
    {
        $uniqueReferences = $rows
            ->pluck('reference')
            ->filter()
            ->unique()
            ->count();

        $totalProducts = $rows->sum(function (array $row) {
            $productIds = collect(explode(',', (string) ($row['id_product'] ?? '')))
                ->map(fn ($id) => trim($id))
                ->filter()
                ->unique()
                ->count();

            $attributeIds = collect(explode(',', (string) ($row['id_product_attribute'] ?? '')))
                ->map(fn ($id) => trim($id))
                ->filter()
                ->unique()
                ->count();

            return max($productIds, $attributeIds, 1);
        });

        $stockValueEur = round($rows->sum(fn (array $row) => (float) ($row['quantity'] ?? 0) * (float) ($row['wholesale_price_eur'] ?? 0)), 2);

        return [
            'unique_references' => $uniqueReferences,
            'total_products' => $totalProducts,
            'stock_value_eur' => $stockValueEur,
        ];
    }
}
