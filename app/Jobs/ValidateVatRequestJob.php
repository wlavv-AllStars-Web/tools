<?php

namespace App\Jobs;

use App\Models\modules\vat_validation_requests\vat_validation_requests;
use App\Services\Vat\PrestashopVatCustomerService;
use App\Services\Vat\ViesVatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ValidateVatRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 90;

    public function __construct(public int $vatValidationRequestId)
    {
    }

    public function handle(ViesVatService $viesService, PrestashopVatCustomerService $prestashopService): void
    {
        $request = vat_validation_requests::query()->find($this->vatValidationRequestId);

        if (!$request || $request->isFinal()) {
            return;
        }

        if (!in_array($request->status, [
            vat_validation_requests::STATUS_PENDING,
            vat_validation_requests::STATUS_RETRY_SCHEDULED,
            vat_validation_requests::STATUS_PROCESSING,
        ], true)) {
            return;
        }

        $maxAttempts = (int) env('VAT_VALIDATION_MAX_ATTEMPTS', 3);
        $retryDelayMinutes = (int) env('VAT_VALIDATION_RETRY_DELAY_MINUTES', 60);

        try {
            $request->markProcessing();

            $vatOnly = vat_validation_requests::normalizeVatNumber($request->vat_number, $request->country_iso);
            $result = $viesService->check($request->country_iso, $vatOnly);

            if (($result['status'] ?? null) === 'valid') {
                $prestashopService->applyProfessionalGroup((int) $request->id_customer);
                $request->markValid($result['raw'] ?? $result);
                return;
            }

            if (($result['status'] ?? null) === 'invalid') {
                $request->markInvalid($result['raw'] ?? $result);
                return;
            }

            $request->scheduleRetry(
                (string) ($result['message'] ?? 'VIES validation inconclusive.'),
                $retryDelayMinutes,
                $maxAttempts
            );
        } catch (Throwable $e) {
            $request->refresh();
            $request->scheduleRetry($e->getMessage(), $retryDelayMinutes, $maxAttempts);
        }
    }
}
