<?php
namespace App\Services\MoloniVat;

use App\Models\modules\moloni_vat_validation\MoloniVatValidation;
use App\Models\modules\moloni_vat_validation\MoloniVatValidationOrder;
use App\Services\Vat\ViesVatService;
use Illuminate\Support\Facades\DB;
use Throwable;

class MoloniVatValidationService
{
    private const ELIGIBLE_GROUP_IDS = [4, 6];

    public function registerOrderFromPrestashop(int $orderId, ?int $invoiceId = null, string $source = 'module'): ?MoloniVatValidationOrder
    {
        $prefix = env('DB2_DB_prefix', 'ps_');

        $order = DB::connection('mysql2')->table($prefix . 'orders as o')
            ->leftJoin($prefix . 'address as a', 'a.id_address', '=', 'o.id_address_invoice')
            ->leftJoin($prefix . 'country as c', 'c.id_country', '=', 'a.id_country')
            ->where('o.id_order', $orderId)
            ->select(['o.id_order', 'o.id_customer', 'a.vat_number', 'c.iso_code as country_iso'])
            ->first();

        if (!$order) {
            return null;
        }

        $groupId = DB::connection('mysql2')->table($prefix . 'customer_group')
            ->where('id_customer', $order->id_customer)
            ->whereIn('id_group', self::ELIGIBLE_GROUP_IDS)
            ->orderBy('id_group')
            ->value('id_group');

        return $this->registerInvoice([
            'id_order' => (int) $order->id_order,
            'id_customer' => (int) $order->id_customer,
            'customer_group_id' => (int) $groupId,
            'country_iso' => $order->country_iso,
            'vat_number' => $order->vat_number,
            'moloni_invoice_id' => $invoiceId,
        ], $source);
    }

    public function registerInvoice(array $payload, string $source = 'module'): ?MoloniVatValidationOrder
    {
        $orderId = (int) ($payload['id_order'] ?? 0);
        $customerId = (int) ($payload['id_customer'] ?? 0);
        $groupId = (int) ($payload['customer_group_id'] ?? 0);

        if ($orderId <= 0 || $customerId <= 0 || !in_array($groupId, self::ELIGIBLE_GROUP_IDS, true)) {
            return null;
        }

        $country = MoloniVatValidation::normalizeCountryIso($payload['country_iso'] ?? '');
        $vat = trim((string) ($payload['vat_number'] ?? ''));
        $invoiceId = isset($payload['moloni_invoice_id']) ? (int) $payload['moloni_invoice_id'] : null;

        return DB::transaction(function () use ($orderId, $customerId, $groupId, $country, $vat, $invoiceId, $source) {
            if ($country === '' || $vat === '') {
                $validation = MoloniVatValidation::query()->firstOrNew([
                    'normalized_vat_number' => 'MISSING:' . $orderId,
                ]);
                $validation->fill([
                    'country_iso' => $country,
                    'vat_number' => $vat,
                    'status' => MoloniVatValidation::STATUS_MISSING_VAT,
                    'attempts' => 0,
                    'next_attempt_at' => null,
                    'validated_at' => null,
                    'valid_until' => null,
                    'last_error' => 'Missing billing-address country or VAT number.',
                    'vies_response' => null,
                ]);
                $validation->save();
            } else {
                $validation = MoloniVatValidation::query()->firstOrNew([
                    'normalized_vat_number' => MoloniVatValidation::normalizedVat($country, $vat),
                ]);

                $mustQueue = !$validation->exists
                    || ($validation->status === MoloniVatValidation::STATUS_VALID && !$validation->isFreshlyValid());

                $validation->country_iso = $country;
                $validation->vat_number = $vat;

                if ($mustQueue) {
                    $validation->fill([
                        'status' => MoloniVatValidation::STATUS_PENDING,
                        'attempts' => 0,
                        'next_attempt_at' => now(),
                        'validated_at' => null,
                        'valid_until' => null,
                        'last_error' => null,
                        'vies_response' => null,
                    ]);
                }

                $validation->save();
            }

            return MoloniVatValidationOrder::query()->updateOrCreate(
                ['id_order' => $orderId],
                [
                    'id_customer' => $customerId,
                    'customer_group_id' => $groupId,
                    'moloni_invoice_id' => $invoiceId,
                    'moloni_vat_validation_id' => $validation->id,
                    'source' => $source,
                ]
            );
        });
    }

    public function process(MoloniVatValidation $validation): void
    {
        if (!in_array($validation->status, [
            MoloniVatValidation::STATUS_PENDING,
            MoloniVatValidation::STATUS_RETRY_SCHEDULED,
        ], true)) {
            return;
        }

        $validation->update([
            'status' => MoloniVatValidation::STATUS_PROCESSING,
            'attempts' => (int) $validation->attempts + 1,
            'last_attempt_at' => now(),
        ]);

        try {
            $result = app(ViesVatService::class)->check(
                $validation->country_iso,
                MoloniVatValidation::normalizeVatNumber($validation->vat_number, $validation->country_iso)
            );

            if (($result['status'] ?? null) === 'valid') {
                $validation->update([
                    'status' => MoloniVatValidation::STATUS_VALID,
                    'validated_at' => now(),
                    'valid_until' => now()->addDays((int) config('moloni_vat.valid_days', 7)),
                    'next_attempt_at' => null,
                    'last_error' => null,
                    'vies_response' => $result['raw'] ?? $result,
                ]);
                return;
            }

            if (($result['status'] ?? null) === 'invalid') {
                $validation->update([
                    'status' => MoloniVatValidation::STATUS_INVALID,
                    'validated_at' => now(),
                    'valid_until' => null,
                    'next_attempt_at' => null,
                    'last_error' => (string) ($result['message'] ?? 'VAT returned as invalid by VIES.'),
                    'vies_response' => $result['raw'] ?? $result,
                ]);
                return;
            }

            $this->retryOrReview($validation, (string) ($result['message'] ?? 'VIES validation unavailable.'));
        } catch (Throwable $e) {
            $this->retryOrReview($validation, $e->getMessage());
        }
    }

    public function retry(MoloniVatValidation $validation): void
    {
        $validation->update([
            'status' => MoloniVatValidation::STATUS_PENDING,
            'attempts' => 0,
            'next_attempt_at' => now(),
            'last_error' => null,
        ]);
    }

    public function expireValidatedVats(): int
    {
        return MoloniVatValidation::query()
            ->where('status', MoloniVatValidation::STATUS_VALID)
            ->whereNotNull('valid_until')
            ->where('valid_until', '<=', now())
            ->update([
                'status' => MoloniVatValidation::STATUS_PENDING,
                'attempts' => 0,
                'next_attempt_at' => now(),
                'last_error' => 'The seven-day VIES validation period expired.',
            ]);
    }

    private function retryOrReview(MoloniVatValidation $validation, string $error): void
    {
        if ((int) $validation->attempts >= (int) config('moloni_vat.max_attempts', 6)) {
            $validation->update([
                'status' => MoloniVatValidation::STATUS_MANUAL_REVIEW,
                'next_attempt_at' => null,
                'last_error' => $error,
            ]);
            return;
        }

        $delay = min(360, 15 * (2 ** max(0, (int) $validation->attempts - 1)));

        $validation->update([
            'status' => MoloniVatValidation::STATUS_RETRY_SCHEDULED,
            'next_attempt_at' => now()->addMinutes($delay),
            'last_error' => $error,
        ]);
    }
}
