<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentIntention extends Model
{
    use HasFactory;

    protected $fillable = [
        // Core payment data
        'user_id',
        'type',                    // 'investment' or 'wallet_charge'
        'amount_cents',
        'currency',
        'status',                  // 'created', 'active', 'completed', 'failed', 'expired'
        'is_executed',

        // Paymob integration (minimal)
        'client_secret',           // For checkout - from intention response
        'paymob_intention_id',     // Paymob's intention ID - from response: obj.id
        'paymob_order_id',         // Paymob's order ID - from response: intention_order_id
        'special_reference',       // Our custom reference - sent to Paymob

        // Business data
        'billing_data',            // Customer info - required by Paymob
        'items',                   // Line items - required by Paymob
        'extras',                  // Business context (opportunity_id, shares, etc.)

        // Transaction data (from webhook)
        'transaction_id',          // Paymob transaction ID - from webhook: obj.id
        'merchant_order_id',       // From webhook: obj.order.merchant_order_id
        'payment_method',          // Actual method used - from webhook: obj.source_data.sub_type
        'paymob_response',         // Full webhook data - for debugging

        // Timestamps
        'expires_at',
        'processed_at',
        'refunded_at',
        'refund_amount_cents',
    ];

    protected $casts = [
        'is_executed' => 'boolean',
        'billing_data' => 'array',
        'items' => 'array',
        'extras' => 'array',
        'paymob_response' => 'array',
        'expires_at' => 'datetime',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Get the user that owns the payment intention.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the intention is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the intention is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    /**
     * Get the amount in SAR.
     */
    public function getAmountInSarAttribute(): float
    {
        return $this->amount_cents / 100;
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'completed' && $this->is_executed;
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['created', 'active']);
    }

    /**
     * Check if payment has transaction data
     */
    public function hasTransaction(): bool
    {
        return !empty($this->transaction_id);
    }

    /**
     * Get refund amount in SAR
     */
    public function getRefundAmountInSarAttribute(): float
    {
        return $this->refund_amount_cents ? $this->refund_amount_cents / 100 : 0;
    }
}


