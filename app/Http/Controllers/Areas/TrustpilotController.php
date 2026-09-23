<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;
use App\Models\prestashop\CustomTrustpilot;
use App\Models\User;
use App\Services\Logs\LogService;
use App\Services\Trustpilot\TrustpilotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TrustpilotController extends Controller
{
    public function __construct(private readonly TrustpilotService $trustpilot)
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'period_type' => ['nullable', Rule::in(['weekly', 'monthly', 'manual'])],
        ]);

        $records = CustomTrustpilot::query()
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('recorded_on', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('recorded_on', '<=', $date))
            ->when($filters['period_type'] ?? null, fn ($query, $type) => $query->where('period_type', $type))
            ->orderByDesc('recorded_on')
            ->orderByDesc('id_custom_trustpilot')
            ->paginate(20)
            ->withQueryString();

        $chartRecords = $this->trustpilot->chartRecords();
        $previousReviewCount = null;
        $historyRows = $records->getCollection()->map(function (CustomTrustpilot $record) use ($chartRecords) {
            $previous = $chartRecords
                ->filter(fn (CustomTrustpilot $item) => $item->recorded_on->lt($record->recorded_on)
                    || ($item->recorded_on->isSameDay($record->recorded_on) && $item->getKey() < $record->getKey()))
                ->last();

            return [
                'record' => $record,
                'rating_change' => $previous ? round((float) $record->rating - (float) $previous->rating, 1) : null,
            ];
        });

        $creatorNames = User::query()
            ->whereIn('id', $records->getCollection()->pluck('created_by')->filter()->unique())
            ->pluck('name', 'id');

        return view('areas.web.trustpilot.index', [
            'breadcrumbs' => [
                ['name' => 'sales', 'url' => route('sales.index')],
                ['name' => 'Trustpilot', 'url' => route('web.tools.trustpilot.index'), 'no_translation' => true],
            ],
            'dashboard' => $this->trustpilot->dashboard(),
            'records' => $records,
            'historyRows' => $historyRows,
            'chartRecords' => $chartRecords,
            'filters' => $filters,
            'canManage' => $this->canManage($request),
            'creatorNames' => $creatorNames,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request);
        $this->ensureDecreaseConfirmed($request, $attributes);

        $record = $this->trustpilot->create($attributes, (int) $request->user()->id);

        LogService::create('create', 'trustpilot', 'info', sprintf(
            'Trustpilot record #%d created: %d reviews, rating %.1f.',
            $record->getKey(), $record->review_count, $record->rating
        ));

        return redirect()->route('web.tools.trustpilot.index')->with('success', 'Trustpilot update saved.');
    }

    public function update(Request $request, CustomTrustpilot $trustpilot): RedirectResponse
    {
        $this->ensureCanManage($request);
        $attributes = $this->validatedAttributes($request);
        $this->ensureDecreaseConfirmed($request, $attributes, $trustpilot);

        $record = $this->trustpilot->update($trustpilot, $attributes);

        LogService::create('update', 'trustpilot', 'info', sprintf(
            'Trustpilot record #%d updated: %d reviews, rating %.1f.',
            $record->getKey(), $record->review_count, $record->rating
        ));

        return redirect()->route('web.tools.trustpilot.index')->with('success', 'Trustpilot record updated.');
    }

    public function destroy(Request $request, CustomTrustpilot $trustpilot): RedirectResponse
    {
        $this->ensureCanManage($request);
        $recordId = $trustpilot->getKey();
        $this->trustpilot->delete($trustpilot);

        LogService::create('delete', 'trustpilot', 'warning', "Trustpilot record #{$recordId} soft-deleted.");

        return redirect()->route('web.tools.trustpilot.index')->with('success', 'Trustpilot record removed.');
    }

    private function validatedAttributes(Request $request): array
    {
        return $request->validate([
            'recorded_on' => ['required', 'date'],
            'review_count' => ['required', 'integer', 'min:0'],
            'rating' => ['required', 'numeric', 'between:0,5', 'decimal:0,1'],
            'period_type' => ['required', Rule::in(['weekly', 'monthly', 'manual'])],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function ensureDecreaseConfirmed(Request $request, array $attributes, ?CustomTrustpilot $current = null): void
    {
        $comparisonRecord = CustomTrustpilot::query()
            ->when($current, fn ($query) => $query->whereKeyNot($current->getKey()))
            ->where(function ($query) use ($attributes, $current) {
                $query->whereDate('recorded_on', '<', $attributes['recorded_on'])
                    ->orWhere(function ($query) use ($attributes, $current) {
                        $query->whereDate('recorded_on', $attributes['recorded_on']);
                        if ($current) {
                            $query->where('id_custom_trustpilot', '<', $current->getKey());
                        }
                    });
            })
            ->orderByDesc('recorded_on')
            ->orderByDesc('id_custom_trustpilot')
            ->first();

        if ($comparisonRecord && (int) $attributes['review_count'] < (int) $comparisonRecord->review_count && !$request->boolean('confirm_lower_review_count')) {
            Validator::make([], [])->after(function ($validator) {
                $validator->errors()->add('review_count', 'The total is lower than the previous record. Confirm the value before saving.');
            })->validate();
        }
    }

    private function canManage(Request $request): bool
    {
        return in_array($request->user()?->role, ['admin', 'manager'], true);
    }

    private function ensureCanManage(Request $request): void
    {
        abort_unless($this->canManage($request), 403);
    }
}
