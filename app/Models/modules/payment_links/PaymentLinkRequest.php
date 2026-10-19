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
        'expiration_hours',
        'worldline_payment_link_id',
        'worldline_payment_url',
        'worldline_idempotency_key',
        'worldline_request_payload',
        'worldline_expires_at',
        'worldline_created_at',
        'worldline_error',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'worldline_expires_at' => 'datetime',
        'worldline_created_at' => 'datetime',
        'worldline_request_payload' => 'array',
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
        return $this->isApproved() && !empty($this->worldline_payment_url)
            ? (string) $this->worldline_payment_url
            : null;
    }

    public static function storeOptions(): array
    {
        return array_keys((array) config('allstars.payment_links.stores', []));
    }

}
