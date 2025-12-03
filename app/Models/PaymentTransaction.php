<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_intention_id',
        'user_id',
        'transaction_id',
        'amount_cents',
        'currency',
        'status',
        'payment_method',
        'card_token',
        'payment_token',
        'merchant_order_id',
        'paymob_response',
        'processed_at',
        'refunded_at',
        'refund_amount_cents',
    ];

    protected $casts = [
        'paymob_response' => 'array',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Get the payment intention that owns the transaction.
     */
    public function paymentIntention(): BelongsTo
    {
        return $this->belongsTo(PaymentIntention::class);
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the transaction is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }

    /**
     * Check if the transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the transaction is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if the transaction is refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Get the amount in SAR.
     */
    public function getAmountInSarAttribute(): float
    {
        return $this->amount_cents / 100;
    }

    /**
     * Get the refund amount in SAR.
     */
    public function getRefundAmountInSarAttribute(): float
    {
        return $this->refund_amount_cents ? $this->refund_amount_cents / 100 : 0;
    }
}


