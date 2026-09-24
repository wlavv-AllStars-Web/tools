<?php

namespace App\Services\Vat;

use SoapClient;
use SoapFault;
use Throwable;

class ViesVatService
{
    private const WSDL = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
    private const SUPPORTED_COUNTRY_ISOS = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR',
        'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK',
        'SI', 'ES', 'SE', 'XI',
    ];

    public static function supportsCountry(?string $countryIso): bool
    {
        return in_array(strtoupper(trim((string) $countryIso)), self::SUPPORTED_COUNTRY_ISOS, true);
    }

    public function check(string $countryIso, string $vatNumber): array
    {
        $countryIso = strtoupper(trim($countryIso));
        $vatNumber = strtoupper(preg_replace('/[^A-Z0-9]/', '', $vatNumber) ?? '');

        // VIES identifies Greece as EL, while billing addresses commonly use
        // the ISO country code GR. Some imported VATs contain both prefixes.
        if (str_starts_with($vatNumber, 'GREL') || str_starts_with($vatNumber, 'ELGR')) {
            $vatNumber = 'EL' . substr($vatNumber, 4);
            $countryIso = 'EL';
        } elseif ($countryIso === 'GR') {
            $countryIso = 'EL';

            if (str_starts_with($vatNumber, 'GR')) {
                $vatNumber = 'EL' . substr($vatNumber, 2);
            }
        }
        if (str_starts_with($vatNumber, $countryIso)) {
            $vatNumber = substr($vatNumber, 2);
        }

        if ($countryIso === '' || strlen($countryIso) !== 2 || $vatNumber === '') {
            return [
                'status' => 'invalid',
                'valid' => false,
                'message' => 'Invalid country ISO or VAT number format.',
                'raw' => null,
            ];
        }

        if (!class_exists(SoapClient::class)) {
            return [
                'status' => 'inconclusive',
                'valid' => null,
                'message' => 'PHP SOAP extension is not enabled.',
                'raw' => null,
            ];
        }

        try {
            $client = new SoapClient(self::WSDL, [
                'connection_timeout' => (int) env('VAT_VALIDATION_VIES_TIMEOUT', 15),
                'exceptions' => true,
                'trace' => false,
                'cache_wsdl' => WSDL_CACHE_MEMORY,
            ]);

            $response = $client->checkVat([
                'countryCode' => $countryIso,
                'vatNumber' => $vatNumber,
            ]);

            $raw = json_decode(json_encode($response), true) ?: [];
            $valid = (bool) ($raw['valid'] ?? false);

            return [
                'status' => $valid ? 'valid' : 'invalid',
                'valid' => $valid,
                'message' => $valid ? 'VAT validated by VIES.' : 'VAT returned as invalid by VIES.',
                'raw' => $raw,
            ];
        } catch (SoapFault $e) {
            return [
                'status' => $this->isTechnicalViesFault($e->getMessage()) ? 'inconclusive' : 'invalid',
                'valid' => null,
                'message' => $e->getMessage(),
                'raw' => [
                    'faultcode' => $e->faultcode ?? null,
                    'faultstring' => $e->faultstring ?? null,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'inconclusive',
                'valid' => null,
                'message' => $e->getMessage(),
                'raw' => [
                    'exception' => get_class($e),
                ],
            ];
        }
    }

    private function isTechnicalViesFault(string $message): bool
    {
        $message = strtoupper($message);

        $technicalMarkers = [
            'MS_UNAVAILABLE',
            'SERVICE_UNAVAILABLE',
            'TIMEOUT',
            'SERVER_BUSY',
            'MS_MAX_CONCURRENT_REQ',
            'GLOBAL_MAX_CONCURRENT_REQ',
            'IP_BLOCKED',
            'SERVICE TEMPORARILY UNAVAILABLE',
            'COULD NOT CONNECT',
            'CONNECTION',
        ];

        foreach ($technicalMarkers as $marker) {
            if (str_contains($message, $marker)) {
                return true;
            }
        }

        return true;
    }
}
