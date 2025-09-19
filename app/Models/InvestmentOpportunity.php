<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string $name
 * @property string|null $location
 * @property string|null $description
 * @property int|null $category_id
 * @property int|null $owner_profile_id
 * @property string $status
 * @property string|null $risk_level
 * @property numeric $target_amount
 * @property numeric $price_per_share
 * @property int $reserved_shares
 * @property int|null $investment_duration
 * @property numeric|null $expected_return_amount
 * @property numeric|null $expected_net_return
 * @property numeric $min_investment
 * @property numeric|null $max_investment
 * @property string|null $fund_goal
 * @property bool $show
 * @property Carbon|null $show_date
 * @property Carbon|null $offering_start_date
 * @property Carbon|null $offering_end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OpportunityAttachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \App\Models\InvestmentCategory|null $category
 * @property-read int $available_shares
 * @property-read float $completion_rate
 * @property-read bool $is_completed
 * @property-read bool $is_fundable
 * @property-read int $total_shares
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guarantee> $guarantees
 * @property-read int|null $guarantees_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Investment> $investments
 * @property-read int|null $investments_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\OwnerProfile|null $ownerProfile
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity activeAndVisible()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity open()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity visible()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereExpectedNetReturn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereExpectedReturnAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereFundGoal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereInvestmentDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereMaxInvestment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereMinInvestment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereOfferingEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereOfferingStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereOwnerProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity wherePricePerShare($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereReservedShares($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereRiskLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereShowDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereTargetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        'expected_return_amount_by_myself',
        'expected_net_return_by_myself',
        'expected_return_amount_by_authorize',
        'expected_net_return_by_authorize',
        'shipping_and_service_fee',
        'min_investment',  // min investment shares for one user
        'max_investment',  // max investment shares for one user
        'fund_goal',
        'show',
        'show_date',
        'offering_start_date',
        'offering_end_date',
        'profit_distribution_date',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'price_per_share' => 'decimal:2',
        'expected_return_amount_by_myself' => 'decimal:2',
        'expected_net_return_by_myself' => 'decimal:2',
        'expected_return_amount_by_authorize' => 'decimal:2',
        'expected_net_return_by_authorize' => 'decimal:2',
        'shipping_and_service_fee' => 'decimal:2',
        'min_investment' => 'integer',
        'max_investment' => 'integer',
        'fund_goal' => 'string',
        'show' => 'boolean',
        'show_date' => 'datetime',
        'offering_start_date' => 'datetime',
        'offering_end_date' => 'datetime',
        'profit_distribution_date' => 'datetime',
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
        return $this->hasMany(Investment::class, 'opportunity_id');
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

    /**
     * Filter opportunities that are coming (future start date)
     */
    public function scopeComing($query)
    {
        return $query->where('status', 'open')
            ->where('show', true)
            ->where('offering_start_date', '>', now());
    }

    /**
     * Filter opportunities owned by a specific user
     */
    public function scopeOwnedBy($query, $userId)
    {
        return $query->whereHas('ownerProfile', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Filter closed opportunities (completed or expired)
     */
    public function scopeClosed($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'completed')
                ->orWhere(function ($subQ) {
                    $subQ->where('offering_end_date', '<', now())
                        ->where('status', 'open');
                });
        });
    }

    /**
     * Filter opportunities by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Filter opportunities by category
     */
    public function scopeCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Filter opportunities by risk level
     */
    public function scopeRiskLevel($query, $riskLevel)
    {
        return $query->where('risk_level', $riskLevel);
    }

    /**
     * Filter opportunities within investment amount range
     */
    public function scopeInvestmentRange($query, $minAmount = null, $maxAmount = null)
    {
        if ($minAmount !== null) {
            $query->where('min_investment', '>=', $minAmount);
        }

        if ($maxAmount !== null) {
            $query->where('max_investment', '<=', $maxAmount);
        }

        return $query;
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
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    // ---------------------------------------------
    // Percentage Calculation Methods (Per Share)
    // ---------------------------------------------

    /**
     * Calculate expected return amount percentage per share for myself (direct investment)
     */
    public function expectedReturnAmountByMyselfPercentage(): float
    {
        if (
            !$this->expected_return_amount_by_myself ||
            !$this->price_per_share ||
            $this->price_per_share <= 0
        ) {
            return 0;
        }

        return round(($this->expected_return_amount_by_myself / $this->price_per_share) * 100, 2);
    }

    /**
     * Calculate expected net return percentage per share for myself (direct investment)
     */
    public function expectedNetReturnByMyselfPercentage(): float
    {
        if (
            !$this->expected_net_return_by_myself ||
            !$this->price_per_share ||
            $this->price_per_share <= 0
        ) {
            return 0;
        }

        return round(($this->expected_net_return_by_myself / $this->price_per_share) * 100, 2);
    }

    /**
     * Calculate expected return amount percentage per share for authorized investments
     */
    public function expectedReturnAmountByAuthorizePercentage(): float
    {
        if (
            !$this->expected_return_amount_by_authorize ||
            !$this->price_per_share ||
            $this->price_per_share <= 0
        ) {
            return 0;
        }

        return round(($this->expected_return_amount_by_authorize / $this->price_per_share) * 100, 2);
    }

    /**
     * Calculate expected net return percentage per share for authorized investments
     */
    public function expectedNetReturnByAuthorizePercentage(): float
    {
        if (
            !$this->expected_net_return_by_authorize ||
            !$this->price_per_share ||
            $this->price_per_share <= 0
        ) {
            return 0;
        }

        return round(($this->expected_net_return_by_authorize / $this->price_per_share) * 100, 2);
    }

    /**
     * Calculate shipping and service fee percentage per share
     */
    public function shippingDeliveryCostPercentage(): float
    {
        if (
            !$this->shipping_and_service_fee ||
            !$this->price_per_share ||
            $this->price_per_share <= 0
        ) {
            return 0;
        }

        return round(($this->shipping_and_service_fee / $this->price_per_share) * 100, 2);
    }

    // ---------------------------------------------
    // Guarantee Methods
    // ---------------------------------------------

    /**
     * Get total guarantee value
     */
    public function getTotalGuaranteeValueAttribute(): float
    {
        return $this->guarantees->sum('value') ?? 0;
    }

    /**
     * Get verified guarantees only
     */
    public function getVerifiedGuaranteesAttribute()
    {
        return $this->guarantees->where('is_verified', true);
    }

    /**
     * Check if opportunity has any guarantees
     */
    public function hasGuarantees(): bool
    {
        return $this->guarantees()->exists();
    }

    /**
     * Check if opportunity has verified guarantees
     */
    public function hasVerifiedGuarantees(): bool
    {
        return $this->guarantees()->verified()->exists();
    }

    /**
     * Get guarantee coverage percentage (total guarantee value vs target amount)
     */
    public function getGuaranteeCoveragePercentageAttribute(): float
    {
        if (!$this->target_amount || $this->target_amount <= 0) {
            return 0;
        }

        return round(($this->total_guarantee_value / $this->target_amount) * 100, 2);
    }


}
