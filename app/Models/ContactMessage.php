<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_type',
        'subject',
        'message',
        'status',
        'admin_notes',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_RESOLVED,
        self::STATUS_CLOSED,
    ];

    /**
     * Profile type constants
     */
    public const PROFILE_TYPE_INVESTOR = 'investor';
    public const PROFILE_TYPE_OWNER = 'owner';
    public const PROFILE_TYPE_GUEST = 'guest';

    public const PROFILE_TYPES = [
        self::PROFILE_TYPE_INVESTOR,
        self::PROFILE_TYPE_OWNER,
        self::PROFILE_TYPE_GUEST,
    ];

    /**
     * Get the user that sent the contact message
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for pending messages
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for in progress messages
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope for resolved messages
     */
    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    /**
     * Scope for closed messages
     */
    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    /**
     * Check if message is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if message is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    /**
     * Mark message as responded
     */
    public function markAsResponded(): bool
    {
        return $this->update([
            'status' => self::STATUS_RESOLVED,
            'responded_at' => now()
        ]);
    }

    /**
     * Get status label in Arabic
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'في الانتظار',
            self::STATUS_IN_PROGRESS => 'قيد المعالجة',
            self::STATUS_RESOLVED => 'تم الحل',
            self::STATUS_CLOSED => 'مغلق',
            default => 'غير محدد'
        };
    }

    /**
     * Get profile type label in Arabic
     */
    public function getProfileTypeLabelAttribute(): string
    {
        return match($this->profile_type) {
            self::PROFILE_TYPE_INVESTOR => 'مستثمر',
            self::PROFILE_TYPE_OWNER => 'مالك مشروع',
            self::PROFILE_TYPE_GUEST => 'زائر',
            default => 'غير محدد'
        };
    }
}
