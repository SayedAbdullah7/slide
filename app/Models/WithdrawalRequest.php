<?php

namespace App\Models;




use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'investor_id',
        'profile_type',
        'profile_id',
        'bank_account_id',
        'amount',
        'available_balance',
        'money_withdrawn',
        'status',
        'rejection_reason',
        'admin_notes',
        'completed_at',
        'processed_at',
        'reference_number',
        'bank_details',
        'action_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'money_withdrawn' => 'boolean',
        'bank_details' => 'array',
        'completed_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the user that made the withdrawal request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the investor profile
     */
    public function investor(): BelongsTo
    {
        return $this->belongsTo(InvestorProfile::class, 'investor_id');
    }

    /**
     * Get the bank account (if saved)
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Get the admin who performed the action
     */
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'action_by');
    }

    /**
     * Boot method to generate reference number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($withdrawalRequest) {
            if (empty($withdrawalRequest->reference_number)) {
                $withdrawalRequest->reference_number = 'WR-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if request is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if request is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Scope to get pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get completed requests
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
