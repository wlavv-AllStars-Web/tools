<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\currency_price_update\CurrencyPriceUpdateBatch;
use App\Services\Purchase\CurrencyPriceUpdateService;
use Illuminate\Http\Request;
use RuntimeException;

class CurrencyPriceUpdateController extends Controller
{
    public function __construct(private readonly CurrencyPriceUpdateService $currencyPriceUpdates)
    {
        $this->middleware('auth');
        $this->middleware(function (Request $request, $next) {
            abort_unless((int) $request->user()->id === 43, 403);

            return $next($request);
        });
    }

    public function index()
    {
        return view('customTools.currencyPriceUpdates.index', [
            'currencies' => $this->currencyPriceUpdates->currencies(),
            'breadcrumbs' => $this->breadcrumbs('Currency price updates'),
            'actions' => [[
                'name' => 'currencyPriceUpdates.logs',
                'icon' => '<i class="fa fa-history"></i>',
                'url' => route('purchase.tools.currency_price_updates.logs'),
                'class' => 'btn-info',
            ]],
        ]);
    }

    public function confirm(Request $request)
    {
        $data = $request->validate(['currency_id' => ['required', 'integer']]);

        try {
            $currency = $this->currencyPriceUpdates->currency((int) $data['currency_id']);
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors(['currency_id' => $exception->getMessage()]);
        }

        return view('customTools.currencyPriceUpdates.confirm', [
            'currency' => $currency,
            'brands' => $this->currencyPriceUpdates->brandsForCurrency((int) $currency->id_currency),
            'breadcrumbs' => $this->breadcrumbs('Currency price updates'),
            'actions' => [],
        ]);
    }

    public function preview(Request $request)
    {
        $data = $this->validatedRate($request);

        try {
            $preview = $this->currencyPriceUpdates->preview($data['currency_id'], $data['new_purchase_rate'], $data['new_sale_rate']);
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors(['new_purchase_rate' => $exception->getMessage()]);
        }

        return view('customTools.currencyPriceUpdates.preview', $preview + [
            'breadcrumbs' => $this->breadcrumbs('Currency price updates'),
            'actions' => [],
            'applyEnabled' => $this->priceUpdatesAreEnabled(),
        ]);
    }

    public function apply(Request $request)
    {
        abort_unless($this->priceUpdatesAreEnabled(), 403, 'Currency price updates are currently in preview-only mode.');

        $data = $this->validatedRate($request, true);

        try {
            $batch = $this->currencyPriceUpdates->apply(
                $data['currency_id'],
                $data['current_purchase_rate'],
                $data['current_sale_rate'],
                $data['new_purchase_rate'],
                $data['new_sale_rate'],
                $request->user()
            );
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors(['new_purchase_rate' => $exception->getMessage()]);
        }

        return redirect()
            ->route('purchase.tools.currency_price_updates.logs.show', $batch)
            ->with('success', 'EUR prices updated using the selected purchase and sale rates. The operation can be reverted from this log.');
    }

    public function logs(Request $request)
    {
        $batches = CurrencyPriceUpdateBatch::query()
            ->when($request->filled('currency'), fn ($query) => $query->where('currency_iso', $request->string('currency')->upper()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->withCount('lines')
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('customTools.currencyPriceUpdates.logs', [
            'batches' => $batches,
            'currencies' => $this->currencyPriceUpdates->currencies(),
            'breadcrumbs' => $this->breadcrumbs('Currency price update logs'),
            'actions' => [[
                'name' => 'purchase.tools.currency_price_updates.index',
                'icon' => '<i class="fa fa-repeat"></i>',
                'url' => route('purchase.tools.currency_price_updates.index'),
                'class' => 'btn-primary',
            ]],
        ]);
    }

    public function showLog(Request $request, CurrencyPriceUpdateBatch $batch)
    {
        $lines = $batch->lines()
            ->when($request->filled('reference'), fn ($query) => $query->where('reference', 'like', '%' . $request->string('reference') . '%'))
            ->orderBy('manufacturer')
            ->orderBy('reference')
            ->paginate(100)
            ->withQueryString();

        return view('customTools.currencyPriceUpdates.log_show', [
            'batch' => $batch,
            'lines' => $lines,
            'breadcrumbs' => $this->breadcrumbs('Currency price update log'),
            'actions' => [[
                'name' => 'currencyPriceUpdates.logs',
                'icon' => '<i class="fa fa-history"></i>',
                'url' => route('purchase.tools.currency_price_updates.logs'),
                'class' => 'btn-info',
            ]],
        ]);
    }

    public function exportCsv(CurrencyPriceUpdateBatch $batch)
    {
        $filename = sprintf(
            'currency-price-update-%d-%s-%s.csv',
            $batch->id,
            strtolower($batch->currency_iso),
            optional($batch->created_at)->format('Y-m-d-His') ?: now()->format('Y-m-d-His')
        );

        return response()->streamDownload(function () use ($batch) {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Batch ID', 'Date', 'Status', 'Currency', 'Old purchase rate', 'New purchase rate', 'Old sale rate', 'New sale rate',
                'Brand', 'Reference', 'Type',
                'Purchase base currency', 'Sale base currency',
                'Old purchase EUR', 'New purchase EUR', 'Purchase difference EUR',
                'Old sale EUR', 'New sale EUR', 'Sale difference EUR',
                'Purchase updated', 'Sale updated', 'Result',
            ], ';');

            $batch->lines()->orderBy('manufacturer')->orderBy('reference')->chunkById(500, function ($lines) use ($output, $batch) {
                foreach ($lines as $line) {
                    fputcsv($output, [
                        $batch->id,
                        optional($batch->created_at)->format('Y-m-d H:i:s'),
                        $batch->status,
                        $batch->currency_iso,
                        $batch->old_purchase_conversion_rate,
                        $batch->new_purchase_conversion_rate,
                        $batch->old_sale_conversion_rate,
                        $batch->new_sale_conversion_rate,
                        $line->manufacturer,
                        $line->reference,
                        $line->line_type === 'attribute' ? 'Combination' : 'Product',
                        $line->purchase_base_currency,
                        $line->sale_base_currency,
                        $line->old_purchase_eur,
                        $line->new_purchase_eur,
                        (float) $line->new_purchase_eur - (float) $line->old_purchase_eur,
                        $line->old_sale_eur,
                        $line->new_sale_eur,
                        (float) $line->new_sale_eur - (float) $line->old_sale_eur,
                        $line->purchase_updated ? 'yes' : 'no',
                        $line->sale_updated ? 'yes' : 'no',
                        $line->reason ?: 'updated',
                    ], ';');
                }
            });

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function rollback(Request $request, CurrencyPriceUpdateBatch $batch)
    {
        try {
            $this->currencyPriceUpdates->rollback($batch, $request->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['rollback' => $exception->getMessage()]);
        }

        return redirect()
            ->route('purchase.tools.currency_price_updates.logs.show', $batch)
            ->with('success', 'The recorded EUR prices were restored. Global currency rates were not changed.');
    }

    private function priceUpdatesAreEnabled(): bool
    {
        return (bool) config('currency_price_updates.apply_enabled', false);
    }

    private function validatedRate(Request $request, bool $requireCurrentRate = false): array
    {
        $rules = [
            'currency_id' => ['required', 'integer'],
            'new_purchase_rate' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'new_sale_rate' => ['required', 'numeric', 'gt:0', 'max:1000000'],
        ];

        if ($requireCurrentRate) {
            $rules['current_purchase_rate'] = ['required', 'numeric', 'gt:0', 'max:1000000'];
            $rules['current_sale_rate'] = ['required', 'numeric', 'gt:0', 'max:1000000'];
        }

        $data = $request->validate($rules);

        return [
            'currency_id' => (int) $data['currency_id'],
            'new_purchase_rate' => (float) $data['new_purchase_rate'],
            'new_sale_rate' => (float) $data['new_sale_rate'],
            'current_purchase_rate' => isset($data['current_purchase_rate']) ? (float) $data['current_purchase_rate'] : null,
            'current_sale_rate' => isset($data['current_sale_rate']) ? (float) $data['current_sale_rate'] : null,
        ];
    }

    private function breadcrumbs(string $current): array
    {
        return [
            ['name' => 'purchase', 'url' => route('purchase.index')],
            ['name' => $current, 'url' => url()->current(), 'no_translation' => true],
        ];
    }
}
