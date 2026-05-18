<?php

namespace App\Models\modules\vat_validation_requests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use App\Models\prestashop\asm_dashboard;

use App\Models\Concerns\BuildsDashboardPanels;
class vat_validation_requests extends Model
{
    
    use BuildsDashboardPanels;
protected $table = 'vat_validation_requests';

    protected $fillable = [
        'id_customer',
        'country_iso',
        'vat_number',
        'normalized_vat_number',
        'status',
        'attempts',
        'last_attempt_at',
        'next_attempt_at',
        'validated_at',
        'last_error',
        'vies_response',
        'manual_notes',
    ];

    protected $casts = [
        'id_customer' => 'integer',
        'attempts' => 'integer',
        'last_attempt_at' => 'datetime',
        'next_attempt_at' => 'datetime',
        'validated_at' => 'datetime',
        'vies_response' => 'array',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_RETRY_SCHEDULED = 'retry_scheduled';
    public const STATUS_VALID = 'valid';
    public const STATUS_INVALID = 'invalid';
    public const STATUS_MANUAL_REVIEW = 'manual_review';
    public const STATUS_FAILED = 'failed';

    public static function normalizeCountryIso(?string $countryIso): string
    {
        return strtoupper(substr(trim((string) $countryIso), 0, 2));
    }

    public static function normalizeVatNumber(?string $vatNumber, ?string $countryIso = null): string
    {
        $vat = strtoupper((string) $vatNumber);
        $vat = preg_replace('/[^A-Z0-9]/', '', $vat) ?? '';

        $country = self::normalizeCountryIso($countryIso);

        if ($country !== '' && str_starts_with($vat, $country)) {
            $vat = substr($vat, 2);
        }

        return $vat;
    }

    public static function buildNormalizedVat(?string $countryIso, ?string $vatNumber): string
    {
        return self::normalizeCountryIso($countryIso) . self::normalizeVatNumber($vatNumber, $countryIso);
    }

    public static function createPending(int $idCustomer, string $countryIso, string $vatNumber): self
    {
        $countryIso = self::normalizeCountryIso($countryIso);
        $vatOnly = self::normalizeVatNumber($vatNumber, $countryIso);
        $normalizedVat = $countryIso . $vatOnly;

        return self::create([
            'id_customer' => $idCustomer,
            'country_iso' => $countryIso,
            'vat_number' => $vatNumber,
            'normalized_vat_number' => $normalizedVat,
            'status' => self::STATUS_PENDING,
            'attempts' => 0,
            'next_attempt_at' => now(),
        ]);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRetryScheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RETRY_SCHEDULED);
    }

    public function scopeManualReview(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_MANUAL_REVIEW);
    }

    public function scopeReadyToProcess(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_RETRY_SCHEDULED])
            ->where(function (Builder $q) {
                $q->whereNull('next_attempt_at')
                  ->orWhere('next_attempt_at', '<=', now());
            });
    }

    public function markProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'last_attempt_at' => now(),
            'attempts' => $this->attempts + 1,
        ]);
    }

    public function markValid(?array $viesResponse = null): void
    {
        $this->update([
            'status' => self::STATUS_VALID,
            'validated_at' => now(),
            'next_attempt_at' => null,
            'last_error' => null,
            'vies_response' => $viesResponse,
        ]);
    }

    public function markInvalid(?array $viesResponse = null): void
    {
        $this->update([
            'status' => self::STATUS_INVALID,
            'validated_at' => now(),
            'next_attempt_at' => null,
            'last_error' => null,
            'vies_response' => $viesResponse,
        ]);
    }

    public function scheduleRetry(string $errorMessage, int $delayMinutes = 60, int $maxAttempts = 3): void
    {
        if ($this->attempts >= $maxAttempts) {
            $this->markManualReview($errorMessage);
            return;
        }

        $this->update([
            'status' => self::STATUS_RETRY_SCHEDULED,
            'next_attempt_at' => Carbon::now()->addMinutes($delayMinutes),
            'last_error' => $errorMessage,
        ]);
    }

    public function markManualReview(?string $errorMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_MANUAL_REVIEW,
            'next_attempt_at' => null,
            'last_error' => $errorMessage,
        ]);
    }

    public function markFailed(?string $errorMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'next_attempt_at' => null,
            'last_error' => $errorMessage,
        ]);
    }

    public function isFinal(): bool
    {
        return in_array($this->status, [
            self::STATUS_VALID,
            self::STATUS_INVALID,
            self::STATUS_MANUAL_REVIEW,
            self::STATUS_FAILED,
        ], true);
    }
    
    public static function dashboard_vat_failed($type)
    {
        $data = [];
        $excludedIds = asm_dashboard::getExceptions('vat_failed')->pluck('id_product')->toArray();
        
        $query = \App\Models\modules\vat_validation_requests\vat_validation_requests::query()
            ->select([
                'id',
                'id_customer',
                'country_iso',
                'vat_number',
            ])
            ->where('status', 'failed')
            ->orderByDesc('id');
    
        if (!empty($excludedIds)) {
            $query->whereNotIn('id_customer', $excludedIds);
        }
    
        foreach ($query->get() as $item) {
            $data[] = [
                'clean' => $item->id,
                'id_customer' => $item->id_customer,
                'country_iso' => $item->country_iso,
                'vat_number' => $item->vat_number,
            ];
        }
    
        return self::dashboardPanel(
            trans('dashboard.VAT FAILED'),
            $type,
            'vat_failed',
            [
                'clean',
                'id_customer',
                'country_iso',
                'vat_number',
            ],
            $data,
            [
                'exception_fields' => [
                    'vat_failed',
                    'id_customer',
                    'country_iso',
                    'vat_number',
                ],
            ]
        );
    }

}
