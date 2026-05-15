<?php

namespace App\Services\Vat;

use App\Models\modules\vat_validation_requests\vat_validation_requests;

class VatValidationRequestService
{
    public function addRequest(int $idCustomer, string $countryIso, string $vatNumber): vat_validation_requests
    {
        $countryIso = vat_validation_requests::normalizeCountryIso($countryIso);
        $normalizedVat = vat_validation_requests::buildNormalizedVat($countryIso, $vatNumber);

        $existing = vat_validation_requests::query()
            ->where('id_customer', $idCustomer)
            ->where('normalized_vat_number', $normalizedVat)
            ->whereIn('status', [
                vat_validation_requests::STATUS_PENDING,
                vat_validation_requests::STATUS_PROCESSING,
                vat_validation_requests::STATUS_RETRY_SCHEDULED,
                vat_validation_requests::STATUS_VALID,
            ])
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        return vat_validation_requests::createPending($idCustomer, $countryIso, $vatNumber);
    }
}
