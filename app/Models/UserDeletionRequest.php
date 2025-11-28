<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDeletionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reason',
        'status',
        'requested_at',
        'processed_at',
        'admin_notes'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CANCELLED,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    /**
     * Get the user that owns the deletion request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for cancelled requests
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if request is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
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
     * Cancel the deletion request
     */
    public function cancel(): bool
    {
        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'processed_at' => now()
        ]);
    }

    /**
     * Approve the deletion request
     */
    public function approve(?string $adminNotes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_APPROVED,
            'processed_at' => now(),
            'admin_notes' => $adminNotes
        ]);
    }

    /**
     * Reject the deletion request
     */
    public function reject(?string $adminNotes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_REJECTED,
            'processed_at' => now(),
            'admin_notes' => $adminNotes
        ]);
    }
}
