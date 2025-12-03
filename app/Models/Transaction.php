<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Transaction Model (Laravel Wallet Package)
 *
 * @property int $id
 * @property string $payable_type
 * @property int $payable_id
 * @property int $wallet_id
 * @property string $type (deposit|withdraw)
 * @property string $amount (in cents)
 * @property boolean $confirmed
 * @property array|null $meta
 * @property string $uuid
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read mixed $payable
 */
class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'payable_type',
        'payable_id',
        'wallet_id',
        'type',
        'amount',
        'confirmed',
        'meta',
        'uuid',
    ];

    protected $casts = [
        'amount' => 'string', // Keep as string to preserve precision
        'confirmed' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the owning payable model (User, InvestorProfile, OwnerProfile).
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if transaction is a deposit
     */
    public function isDeposit(): bool
    {
        return $this->type === 'deposit';
    }

    /**
     * Check if transaction is a withdrawal
     */
    public function isWithdrawal(): bool
    {
        return $this->type === 'withdraw';
    }

    /**
     * Check if transaction is confirmed
     */
    public function isConfirmed(): bool
    {
        return (bool) $this->confirmed;
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return !$this->confirmed;
    }

    /**
     * Get amount in SAR (from cents)
     */
    public function getAmountInSarAttribute(): float
    {
        return (float) $this->amount / 100;
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_in_sar, 2) . ' SAR';
    }

    /**
     * Get transaction description from meta or default
     */
    public function getDescriptionAttribute(): string
    {
        if ($this->meta && isset($this->meta['description'])) {
            return $this->meta['description'];
        }

        return $this->type === 'deposit' ? 'Wallet deposit' : 'Wallet withdrawal';
    }

    /**
     * Get payable display name
     */
    public function getPayableNameAttribute(): string
    {
        if (!$this->payable) {
            return 'Unknown';
        }

        $type = class_basename($this->payable_type);

        return match($type) {
            'User' => $this->payable->full_name ?? $this->payable->email ?? 'Unknown User',
            'InvestorProfile' => ($this->payable->user->full_name ?? 'Unknown') . ' (Investor)',
            'OwnerProfile' => $this->payable->business_name ?? ($this->payable->user->full_name ?? 'Unknown') . ' (Owner)',
            default => 'Unknown',
        };
    }

    /**
     * Scope: Only deposits
     */
    public function scopeDeposits($query)
    {
        return $query->where('type', 'deposit');
    }

    /**
     * Scope: Only withdrawals
     */
    public function scopeWithdrawals($query)
    {
        return $query->where('type', 'withdraw');
    }

    /**
     * Scope: Only confirmed transactions
     */
    public function scopeConfirmed($query)
    {
        return $query->where('confirmed', true);
    }

    /**
     * Scope: Only pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('confirmed', false);
    }

    /**
     * Scope: For specific payable
     */
    public function scopeForPayable($query, $payable)
    {
        return $query->where('payable_type', get_class($payable))
                    ->where('payable_id', $payable->id);
    }

    /**
     * Scope: Amount range
     */
    public function scopeAmountBetween($query, float $min, float $max)
    {
        return $query->whereBetween('amount', [$min * 100, $max * 100]);
    }

    /**
     * Scope: Recent transactions
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
