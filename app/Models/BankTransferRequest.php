<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BankTransferRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'investor_id',
        'profile_type',
        'profile_id',
        'receipt_file',
        'receipt_file_name',
        'bank_id',
        'transfer_reference',
        'amount',
        'admin_notes',
        'status',
        'rejection_reason',
        'action_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the user that made the bank transfer request
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
     * Get the bank
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Get the admin who performed the action
     */
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'action_by');
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();
        // Note: transfer_reference is set by admin, not auto-generated
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
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
     * Scope to get approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope to get rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Get receipt file URL
     */
    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_file) {
            return null;
        }

        return asset('storage/' . $this->receipt_file);
    }

    /**
     * Get receipt file type (image or pdf)
     */
    public function getReceiptTypeAttribute(): ?string
    {
        if (!$this->receipt_file) {
            return null;
        }

        $extension = strtolower(pathinfo($this->receipt_file, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            return 'image';
        } elseif ($extension === 'pdf') {
            return 'pdf';
        }

        return 'other';
    }
}
