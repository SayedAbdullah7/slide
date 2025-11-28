<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentOpportunityReminder extends Model
{
    protected $fillable = [
        'investor_profile_id',
        'investment_opportunity_id',
        'is_active',
        'reminder_sent_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'reminder_sent_at' => 'datetime',
    ];

    /**
     * Get the investor profile that owns the reminder
     */
    public function investorProfile(): BelongsTo
    {
        return $this->belongsTo(InvestorProfile::class);
    }

    /**
     * Get the investment opportunity for this reminder
     */
    public function investmentOpportunity(): BelongsTo
    {
        return $this->belongsTo(InvestmentOpportunity::class);
    }

    /**
     * Scope to get active reminders
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get unsent reminders
     */
    public function scopeUnsent($query)
    {
        return $query->whereNull('reminder_sent_at');
    }

    /**
     * Mark reminder as sent
     */
    public function markAsSent(): bool
    {
        return $this->update(['reminder_sent_at' => now()]);
    }

    /**
     * Check if reminder has been sent
     */
    public function isSent(): bool
    {
        return !is_null($this->reminder_sent_at);
    }
}
