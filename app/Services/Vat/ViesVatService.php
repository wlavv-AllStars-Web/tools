<?php

namespace App\Services\Vat;

use SoapClient;
use SoapFault;
use Throwable;

class ViesVatService
{
    private const WSDL = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    public function check(string $countryIso, string $vatNumber): array
    {
        $countryIso = strtoupper(trim($countryIso));
        $vatNumber = strtoupper(preg_replace('/[^A-Z0-9]/', '', $vatNumber) ?? '');

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
