<?php

namespace App\Models\modules\payment_links;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLinkRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SENT = 'sent';

    protected $fillable = [
        'store_code',
        'order_id',
        'description',
        'amount',
        'currency',
        'customer_email',
        'request_hash',
        'sha_sign',
        'status',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'email_sent_by',
        'email_sent_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'email_sent_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function emailSender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email_sent_by');
    }

    public function isApproved(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_SENT], true);
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function amountInCents(): int
    {
        return (int) round(((float) $this->amount) * 100);
    }

    public function storeName(): string
    {
        return (string) config('allstars.payment_links.stores.' . $this->store_code . '.name', $this->store_code);
    }

    public function storeColor(): string
    {
        return (string) config('allstars.payment_links.stores.' . $this->store_code . '.payment_link_color', '#dd170e');
    }

    public function paymentUrl(): ?string
    {
        if (!$this->isApproved()) {
            return null;
        }

        $storeConfig = self::storeConfig($this->store_code);

        $query = http_build_query([
            'PSPID' => $storeConfig['pspid'],
            'ORDERID' => $this->order_id,
            'COM' => $this->description,
            'AMOUNT' => $this->amountInCents(),
            'EMAIL' => $this->customer_email,
            'SHASIGN' => $this->sha_sign,
            'CURRENCY' => $this->currency,
        ], '', '&', PHP_QUERY_RFC3986);

        return rtrim((string) config('allstars.payment_links.gateway_url'), '?') . '?' . $query;
    }

    public static function generateShaSign(string $storeCode, string $orderId, string $description, int $amountInCents, string $email): string
    {
        $storeConfig = self::storeConfig($storeCode);
        $shaIn = $storeConfig['sha_in'];
        $pspid = $storeConfig['pspid'];

        $plain = 'AMOUNT=' . $amountInCents . $shaIn
            . 'COM=' . $description . $shaIn
            . 'CURRENCY=EUR' . $shaIn
            . 'EMAIL=' . $email . $shaIn
            . 'ORDERID=' . $orderId . $shaIn
            . 'PSPID=' . $pspid . $shaIn;

        return sha1($plain);
    }

    public static function storeOptions(): array
    {
        return array_keys((array) config('allstars.payment_links.stores', []));
    }

    private static function storeConfig(string $storeCode): array
    {
        $config = (array) config('allstars.payment_links.stores.' . strtoupper($storeCode), []);

        return [
            'pspid' => (string) ($config['pspid'] ?? ''),
            'sha_in' => (string) ($config['sha_in'] ?? ''),
        ];
    }
}
