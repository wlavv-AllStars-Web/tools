<?php

namespace App\Services\Trustpilot;

use App\Models\prestashop\CustomTrustpilot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TrustpilotService
{
    public function latest(): ?CustomTrustpilot
    {
        return $this->orderedQuery()->first();
    }

    public function previousFor(CustomTrustpilot $record): ?CustomTrustpilot
    {
        return CustomTrustpilot::query()
            ->where(function ($query) use ($record) {
                $query->where('recorded_on', '<', $record->recorded_on)
                    ->orWhere(function ($query) use ($record) {
                        $query->whereDate('recorded_on', $record->recorded_on)
                            ->where($record->getKeyName(), '<', $record->getKey());
                    });
            })
            ->orderByDesc('recorded_on')
            ->orderByDesc($record->getKeyName())
            ->first();
    }

    public function create(array $attributes, int $userId): CustomTrustpilot
    {
        return DB::connection('mysql2')->transaction(function () use ($attributes, $userId) {
            $record = CustomTrustpilot::create($attributes + ['created_by' => $userId]);
            $this->recalculatePeriodCounts();

            return $record->fresh();
        });
    }

    public function update(CustomTrustpilot $record, array $attributes): CustomTrustpilot
    {
        return DB::connection('mysql2')->transaction(function () use ($record, $attributes) {
            $record->update($attributes);
            $this->recalculatePeriodCounts();

            return $record->fresh();
        });
    }

    public function delete(CustomTrustpilot $record): void
    {
        DB::connection('mysql2')->transaction(function () use ($record) {
            $record->delete();
            $this->recalculatePeriodCounts();
        });
    }

    public function dashboard(): array
    {
        $latest = $this->latest();
        $previous = $latest ? $this->previousFor($latest) : null;

        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();
        $yearStart = now()->startOfYear();
        $previousWeekStart = $weekStart->copy()->subWeek();

        return [
            'latest' => $latest,
            'previous' => $previous,
            'rating_change' => $latest && $previous ? round((float) $latest->rating - (float) $previous->rating, 1) : null,
            'reviews_this_week' => $this->reviewDeltaSince($weekStart),
            'reviews_this_month' => $this->reviewDeltaSince($monthStart),
            'reviews_this_year' => $this->reviewDeltaSince($yearStart),
            'week_comparison' => [
                'current' => $this->reviewDeltaSince($weekStart),
                'previous' => $this->reviewDeltaBetween($previousWeekStart, $weekStart),
            ],
        ];
    }

    public function chartRecords(): Collection
    {
        return CustomTrustpilot::query()
            ->orderBy('recorded_on')
            ->orderBy(CustomTrustpilot::query()->getModel()->getKeyName())
            ->get();
    }

    private function reviewDeltaSince(Carbon $start): ?int
    {
        $latest = $this->latest();
        $baseline = CustomTrustpilot::query()
            ->whereDate('recorded_on', '<', $start->toDateString())
            ->orderByDesc('recorded_on')
            ->orderByDesc('id_custom_trustpilot')
            ->first();

        if (!$latest || !$baseline) {
            return null;
        }

        if ($latest->recorded_on->lt($start)) {
            return null;
        }

        return (int) $latest->review_count - (int) $baseline->review_count;
    }

    private function reviewDeltaBetween(Carbon $start, Carbon $end): ?int
    {
        $endRecord = CustomTrustpilot::query()
            ->whereDate('recorded_on', '>=', $start->toDateString())
            ->whereDate('recorded_on', '<', $end->toDateString())
            ->orderByDesc('recorded_on')
            ->orderByDesc('id_custom_trustpilot')
            ->first();
        $baseline = CustomTrustpilot::query()
            ->whereDate('recorded_on', '<', $start->toDateString())
            ->orderByDesc('recorded_on')
            ->orderByDesc('id_custom_trustpilot')
            ->first();

        if (!$endRecord || !$baseline) {
            return null;
        }

        return (int) $endRecord->review_count - (int) $baseline->review_count;
    }

    private function recalculatePeriodCounts(): void
    {
        $records = CustomTrustpilot::query()
            ->orderBy('recorded_on')
            ->orderBy('id_custom_trustpilot')
            ->get();
        $previousReviewCount = null;

        foreach ($records as $record) {
            $periodCount = $previousReviewCount === null
                ? null
                : max(0, (int) $record->review_count - $previousReviewCount);

            if ($record->review_count_period !== $periodCount) {
                $record->forceFill(['review_count_period' => $periodCount])->saveQuietly();
            }

            $previousReviewCount = (int) $record->review_count;
        }
    }

    private function orderedQuery()
    {
        return CustomTrustpilot::query()
            ->orderByDesc('recorded_on')
            ->orderByDesc('id_custom_trustpilot');
    }
}
