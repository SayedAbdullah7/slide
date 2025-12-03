<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'data',
        'related_id',
        'related_type',
        'is_sent',
        'sent_at',
        'is_read',
        'read_at',
        'fcm_success_count',
        'fcm_failure_count',
        'fcm_results',
    ];

    protected $casts = [
        'data' => 'array',
        'fcm_results' => 'array',
        'is_sent' => 'boolean',
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Notification types
    public const TYPE_WALLET_CHARGE = 'wallet_charge';
    public const TYPE_WALLET_WITHDRAW = 'wallet_withdraw';
    public const TYPE_INVESTMENT_PURCHASE = 'investment_purchase';
    public const TYPE_PROFIT_DISTRIBUTION = 'profit_distribution';
    public const TYPE_CUSTOM = 'custom';
    public const TYPE_INVESTMENT_OPPORTUNITY_AVAILABLE = 'investment_opportunity_available';
    public const TYPE_REMINDER = 'reminder';

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related entity (morphTo)
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to get unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope to get sent notifications
     */
    public function scopeSent($query)
    {
        return $query->where('is_sent', true);
    }

    /**
     * Scope to get notifications by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get recent notifications
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): bool
    {
        if (!$this->is_read) {
            return $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
        return false;
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent(array $fcmResults = []): bool
    {
        $updateData = [
            'is_sent' => true,
            'sent_at' => now(),
        ];

        if (!empty($fcmResults)) {
            $successCount = collect($fcmResults)->where('success', true)->count();
            $failureCount = collect($fcmResults)->where('success', false)->count();
            
            $updateData['fcm_success_count'] = $successCount;
            $updateData['fcm_failure_count'] = $failureCount;
            $updateData['fcm_results'] = $fcmResults;
        }

        return $this->update($updateData);
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return !$this->is_read;
    }

    /**
     * Get notification type label in Arabic
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_WALLET_CHARGE => 'شحن المحفظة',
            self::TYPE_WALLET_WITHDRAW => 'سحب من المحفظة',
            self::TYPE_INVESTMENT_PURCHASE => 'شراء فرصة استثمارية',
            self::TYPE_PROFIT_DISTRIBUTION => 'توزيع الأرباح',
            self::TYPE_CUSTOM => 'إشعار مخصص',
            self::TYPE_INVESTMENT_OPPORTUNITY_AVAILABLE => 'فرصة استثمارية متاحة',
            self::TYPE_REMINDER => 'تذكير',
            default => 'إشعار',
        };
    }
}
