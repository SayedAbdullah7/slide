<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class InvestmentOpportunity extends Model implements HasMedia
{
//    use SoftDeletes;
    use InteractsWithMedia;
    protected $fillable = [
        'name',
        'location',
        'description',
        'category_id',
        'owner_profile_id',
        'status',
        'risk_level',
        'target_amount',
        'price_per_share',
        'reserved_shares',
        'investment_duration',
        'expected_return_amount',
        'expected_net_return',
        'min_investment',
        'max_investment',
        'fund_goal',
        'show',
        'show_date',
        'offering_start_date',
        'offering_end_date',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'price_per_share' => 'decimal:2',
        'expected_return_amount' => 'decimal:2',
        'expected_net_return' => 'decimal:2',
        'min_investment' => 'decimal:2',
        'max_investment' => 'decimal:2',
        'fund_goal' => 'string',
        'show' => 'boolean',
        'show_date' => 'datetime',
        'offering_start_date' => 'datetime',
        'offering_end_date' => 'datetime',
    ];



    // ---------------------------------------------
    // Relationships
    // ---------------------------------------------

    public function category()
    {
        return $this->belongsTo(InvestmentCategory::class)->withDefault();
    }

    public function ownerProfile(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(OwnerProfile::class)->withDefault();
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OpportunityAttachment::class);
    }

    public function guarantees()
    {
        return $this->hasMany(Guarantee::class);
    }

    public function investments()
    {
        return $this->hasMany(Investment::class);
    }


    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    /**
     * Filter visible opportunities (shown to users).
     */
    public function scopeVisible($query)
    {
        return $query->where('show', true)
            ->whereNotNull('show_date')
            ->where('show_date', '<=', now());
    }

    /**
     * Filter only open (available for investment) opportunities.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open')
            ->where(function ($q) {
                $now = now();
                $q->whereNull('offering_start_date')
                    ->orWhere('offering_start_date', '<=', $now);
            })
            ->where(function ($q) {
                $now = now();
                $q->whereNull('offering_end_date')
                    ->orWhere('offering_end_date', '>=', $now);
            });
    }

    public function scopeActiveAndVisible($query)
    {
        return $query->visible()->open();
    }


    // ---------------------------------------------
    // Accessors
    // ---------------------------------------------

    public function getTotalSharesAttribute(): int
    {
        return $this->price_per_share > 0
            ? (int) floor($this->target_amount / $this->price_per_share)
            : 0;
    }

    public function getAvailableSharesAttribute(): int
    {
        return max(0, $this->total_shares - $this->reserved_shares);
    }

    public function getCompletionRateAttribute(): float
    {
        $totalShares = $this->total_shares;
        return $totalShares > 0
            ? round(($this->reserved_shares / $totalShares) * 100, 2)
            : 0;
    }

    // ---------------------------------------------
    // Helpers
    // ---------------------------------------------

    public function isWithinOfferingWindow(): bool
    {
        $now = now();

        return (! $this->offering_start_date || $this->offering_start_date <= $now) &&
            (! $this->offering_end_date || $this->offering_end_date >= $now);
    }

    public function getIsFundableAttribute(): bool
    {
        return $this->status === 'open' &&
            $this->available_shares > 0 &&
            $this->isWithinOfferingWindow();
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function reserveShares(int $shares): void
    {
        $this->reserved_shares += $shares;
        $this->save();
    }


    public function isInvestable(): bool
    {
            return $this->status === 'open'
                && $this->available_shares > 0
                && $this->isWithinOfferingWindow();
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('terms')
            ->acceptsMimeTypes(['application/pdf'])
            ->singleFile();

        $this
            ->addMediaCollection('summary')
            ->acceptsMimeTypes(['application/pdf'])
            ->singleFile();

        $this
            ->addMediaCollection('cover')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();
    }

}
