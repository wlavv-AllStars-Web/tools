<?php

namespace App\Services\oms;

/**
 * Defines manufacturers whose catalogue selling price is maintained in EUR,
 * even though their purchase price is maintained in the supplier currency.
 */
final class OmsSalesCurrencyService
{
    private const EURO_SALE_MANUFACTURERS = ['mmx', 'racingline'];

    public static function usesEuroSalePrice(?string $manufacturerName): bool
    {
        return in_array(
            mb_strtolower(trim((string) $manufacturerName)),
            self::EURO_SALE_MANUFACTURERS,
            true
        );
    }
}
