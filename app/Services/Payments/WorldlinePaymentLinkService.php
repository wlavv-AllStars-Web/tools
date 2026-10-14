<?php

namespace App\Services\Payments;

use App\Models\modules\payment_links\PaymentLinkRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WorldlinePaymentLinkService
{
    /**
     * @return array{payment_link_id: string, payment_url: string, expires_at: CarbonImmutable}
     */
    public function create(PaymentLinkRequest $request): array
    {
        $storeConfig = (array) config('allstars.payment_links.stores.' . strtoupper($request->store_code), []);
        $merchantId = trim((string) ($storeConfig['merchant_id'] ?? ''));
        $apiKey = trim((string) ($storeConfig['api_key'] ?? ''));
        $apiSecret = trim((string) ($storeConfig['api_secret'] ?? ''));
        $baseUrl = rtrim((string) config('allstars.payment_links.worldline_api_base_url'), '/');

        if ($merchantId === '' || $apiKey === '' || $apiSecret === '') {
            throw new RuntimeException('Worldline API credentials are missing for store ' . $request->store_code . '.');
        }

        if (!str_starts_with($baseUrl, 'https://')) {
            throw new RuntimeException('Worldline API URL must use HTTPS.');
        }

        if (empty($request->worldline_idempotency_key)) {
            throw new RuntimeException('Worldline idempotency key is missing.');
        }

        $now = CarbonImmutable::now('UTC');
        $expiresAt = $now->addHours((int) ($request->expiration_hours ?: config('allstars.payment_links.expiration_hours', 168)));
        $date = $now->format('D, d M Y H:i:s \\G\\M\\T');
        $path = '/' . rawurlencode($merchantId) . '/paymentlinks';
        $idempotencyKey = (string) $request->worldline_idempotency_key;

        $signatureSource = implode("\n", [
            'POST',
            'application/json',
            $date,
            'x-gcs-date:' . $date,
            'x-gcs-idempotency-key:' . $idempotencyKey,
            $path,
            '',
        ]);

        $signature = base64_encode(hash_hmac('sha256', $signatureSource, $apiSecret, true));

        $payload = [
            'isReusableLink' => false,
            'displayQRCode' => false,
            'paymentLinkSpecificInput' => [
                'expirationDate' => $expiresAt->toISOString(),
                'description' => (string) $request->description,
            ],
            'order' => [
                'amountOfMoney' => [
                    'amount' => $request->amountInCents(),
                    'currencyCode' => (string) $request->currency,
                ],
                'references' => [
                    'merchantReference' => (string) $request->order_id,
                ],
            ],
        ];

        $response = Http::acceptJson()
            ->contentType('application/json')
            ->withHeaders([
                'Authorization' => 'GCS v1HMAC:' . $apiKey . ':' . $signature,
                'Date' => $date,
                'X-GCS-Date' => $date,
                'X-GCS-Idempotency-Key' => $idempotencyKey,
            ])
            ->connectTimeout(10)
            ->timeout(30)
            ->post($baseUrl . $path, $payload);

        $body = $response->json();
        $paymentUrl = is_array($body) ? ($body['redirectionUrl'] ?? null) : null;

        if (!$response->successful() || !is_string($paymentUrl) || $paymentUrl === '') {
            throw new RuntimeException($this->responseError($response->status(), $body));
        }

        $paymentLinkId = is_array($body) ? (string) ($body['paymentLinkId'] ?? '') : '';
        if ($paymentLinkId === '') {
            throw new RuntimeException('Worldline did not return a paymentLinkId.');
        }

        $responseExpiry = is_array($body) ? ($body['expirationDate'] ?? null) : null;

        return [
            'payment_link_id' => $paymentLinkId,
            'payment_url' => $paymentUrl,
            'expires_at' => is_string($responseExpiry) && $responseExpiry !== ''
                ? CarbonImmutable::parse($responseExpiry)->utc()
                : $expiresAt,
        ];
    }

    private function responseError(int $status, mixed $body): string
    {
        if (is_array($body)) {
            $message = data_get($body, 'errors.0.message')
                ?? data_get($body, 'error.message')
                ?? data_get($body, 'message');

            if (is_string($message) && $message !== '') {
                return 'Worldline API error: ' . $message;
            }
        }

        return 'Worldline API returned HTTP status ' . $status . '.';
    }
}
