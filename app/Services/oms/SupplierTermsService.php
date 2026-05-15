<?php

namespace App\Services\oms;

use App\Models\modules\oms\SupplierTermLevel;
use Illuminate\Support\Collection;

class SupplierTermsService
{
    public function getLevelsForSupplier(int $supplierId, bool $onlyActive = true): Collection
    {
        $query = SupplierTermLevel::query()
            ->where('supplier_id', $supplierId)
            ->orderBy('sort_order')
            ->orderBy('min_amount')
            ->orderBy('id');

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    public function resolveLevelForAmount(int $supplierId, float $amount): ?SupplierTermLevel
    {
        return $this->getLevelsForSupplier($supplierId, true)
            ->first(function (SupplierTermLevel $level) use ($amount) {
                $min = (float) $level->min_amount;
                $max = $level->max_amount !== null ? (float) $level->max_amount : null;

                if ($amount < $min) {
                    return false;
                }

                if ($max !== null && $amount >= $max) {
                    return false;
                }

                return true;
            });
    }

    public function getNextLevel(int $supplierId, float $amount): ?SupplierTermLevel
    {
        return $this->getLevelsForSupplier($supplierId, true)
            ->filter(fn (SupplierTermLevel $level) => (float) $level->min_amount > $amount)
            ->sortBy('min_amount')
            ->first();
    }

    public function buildProgressSummary(int $supplierId, float $amount): array
    {
        $current = $this->resolveLevelForAmount($supplierId, $amount);
        $next = $this->getNextLevel($supplierId, $amount);

        return [
            'amount' => round($amount, 2),
            'current_level' => $current,
            'next_level' => $next,
            'missing_to_next' => $next ? max(0, round(((float) $next->min_amount - $amount), 2)) : 0,
        ];
    }

    public function buildLabel($level): string
    {
        if (!$level) {
            return 'No commercial benefit';
        }

        $discountPercent = (float) data_get($level, 'discount_percent', 0);
        $freeShipping = (bool) data_get($level, 'free_shipping', false);
        $label = (string) data_get($level, 'label', '');

        $parts = [];

        if ($discountPercent > 0) {
            $parts[] = rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.') . '% discount';
        }

        if ($freeShipping) {
            $parts[] = 'free shipping';
        }

        return !empty($parts) ? implode(' + ', $parts) : ($label ?: 'No commercial benefit');
    }

    public function normalizePayload(array $payload, int $supplierId): array
    {
        $maxAmount = $payload['max_amount'] ?? null;
        $maxAmount = ($maxAmount === '' || $maxAmount === null) ? null : round((float) $maxAmount, 2);

        return [
            'supplier_id' => $supplierId,
            'label' => trim((string) ($payload['label'] ?? '')) ?: null,
            'min_amount' => round((float) ($payload['min_amount'] ?? 0), 2),
            'max_amount' => $maxAmount,
            'discount_percent' => round((float) ($payload['discount_percent'] ?? 0), 2),
            'free_shipping' => !empty($payload['free_shipping']),
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'is_active' => array_key_exists('is_active', $payload) ? !empty($payload['is_active']) : true,
            'notes' => trim((string) ($payload['notes'] ?? '')) ?: null,
        ];
    }
}
