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
 * @property string $allowed_investment_types
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
 * @property-read array $investment_type_availability_info
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity activeAndVisible()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunity allowsInvestmentType($investmentType)
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

    protected $attributes = [
        'reserved_shares' => 0,
        'allowed_investment_types' => 'both',
    ];

    protected $fillable = [
        'name',
        'location',
        'description',
        'category_id',
        'owner_profile_id',
        'status',
        'risk_level',
        'target_amount',
        'share_price', // سعر السهم الواحد
        'reserved_shares',
        'investment_duration',
        'expected_profit', // الربح المتوقع لكل سهم
        'expected_net_profit', // صافي الربح المتوقع لكل سهم
        'shipping_fee_per_share', // رسوم الشحن لكل سهم
        'actual_profit_per_share', // الربح الفعلي لكل سهم
        'actual_net_profit_per_share', // صافي الربح الفعلي لكل سهم
        'distributed_profit', // الربح الموزع
        'all_merchandise_delivered',
        'all_returns_distributed',
        'expected_delivery_date',
        'expected_distribution_date',
        'min_investment',  // الحد الأدنى للاستثمار (بالأسهم)
        'max_investment',  // الحد الأقصى للاستثمار (بالأسهم)
        'fund_goal',
        'guarantee',
        'show',
        'show_date',
        'offering_start_date',
        'offering_end_date',
        'profit_distribution_date',
        'allowed_investment_types', // 'both', 'myself', 'authorize'
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'share_price' => 'decimal:2',
        'expected_profit' => 'decimal:2',
        'expected_net_profit' => 'decimal:2',
        'shipping_fee_per_share' => 'decimal:2',
        'actual_profit_per_share' => 'decimal:2',
        'actual_net_profit_per_share' => 'decimal:2',
        'distributed_profit' => 'decimal:2',
        'all_merchandise_delivered' => 'boolean',
        'all_returns_distributed' => 'boolean',
        'expected_delivery_date' => 'datetime',
        'expected_distribution_date' => 'datetime',
        'min_investment' => 'integer',
        'max_investment' => 'integer',
        'fund_goal' => 'string',
        'guarantee' => 'string',
        'show' => 'boolean',
        'show_date' => 'datetime',
        'offering_start_date' => 'datetime',
        'offering_end_date' => 'datetime',
        'profit_distribution_date' => 'datetime',
        'allowed_investment_types' => 'string',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Update status when opportunity is updated
        static::updated(function (InvestmentOpportunity $opportunity) {
            // Only update if relevant fields changed
            if ($opportunity->wasChanged(['show', 'show_date', 'offering_start_date', 'offering_end_date', 'reserved_shares'])) {
                $opportunity->updateDynamicStatus();

                // Check and process reminders if opportunity became available
                if ($opportunity->wasChanged(['status', 'offering_start_date']) && $opportunity->status === 'open') {
                    $opportunity->processReminders();
                }
            }
        });

        // Update status when opportunity is created
        static::created(function (InvestmentOpportunity $opportunity) {
            $opportunity->updateDynamicStatus();
        });
    }

    // ---------------------------------------------
    // Mutators
    // ---------------------------------------------

    // temporary methods to keep the same API for authorize

    //expected_profit_by_authorize
    public function getExpectedProfitByAuthorizeAttribute()
    {
        return $this->expected_profit;
    }

    //expected_net_profit_by_authorize
    public function getExpectedNetProfitByAuthorizeAttribute()
    {
        return $this->expected_net_profit;
    }


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
        return $this->hasMany(OpportunityAttachment::class, 'opportunity_id');
    }

    public function guarantees()
    {
        return $this->hasMany(Guarantee::class);
    }

    public function investments()
    {
        return $this->hasMany(Investment::class, 'opportunity_id');
    }

    public function investmentsMyself()
    {
        return $this->hasMany(Investment::class, 'opportunity_id')->myself();
    }

    public function investmentsAuthorize()
    {
        return $this->hasMany(Investment::class, 'opportunity_id')->authorize();
    }

    public function investmentsNotDistributedAuthorize()
    {
        return $this->investments()->notDistributedAuthorize();
    }

    public function investmentsNotArrivedMyself()
    {
        return $this->investments()->notArrivedMyself();
    }

    //counts
    public function countInvestmentsNotDistributedAuthorize()
    {
        return $this->investmentsNotDistributedAuthorize()->count();
    }

    public function countInvestmentsNotArrivedMyself()
    {
        return $this->investmentsNotArrivedMyself()->count();
    }

    public function investment()
    {
        return $this->hasOne(Investment::class, 'opportunity_id');
    }

    public function reminders()
    {
        return $this->hasMany(InvestmentOpportunityReminder::class);
    }

    public function savedOpportunities()
    {
        return $this->hasMany(SavedInvestmentOpportunity::class);
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
//        return $query->where('status', 'open')
//            ->where('show', true)
//            ->where('offering_start_date', '>', now());
        return $query->where('show', true)
            ->where(function ($q) {
                $now = now();
                $q->whereNotNull('offering_start_date')
                    ->where('offering_start_date', '>', $now);
            });
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

    /**
     * Filter opportunities by allowed investment type
     * نطاق الفرص حسب نوع الاستثمار المسموح
     */
    public function scopeAllowsInvestmentType($query, $investmentType)
    {
        return $query->where(function ($q) use ($investmentType) {
            $q->where('allowed_investment_types', 'both')
                ->orWhere('allowed_investment_types', $investmentType);
        });
    }


    // ---------------------------------------------
    // Accessors
    // ---------------------------------------------

    public function getTotalSharesAttribute(): int
    {
        return $this->share_price > 0
            ? (int) floor($this->target_amount / $this->share_price)
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

    public function isComing(): bool
    {
        $now = now();
        return $this->show &&
               $this->offering_start_date &&
               $this->offering_start_date > $now;
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

            $this
            ->addMediaCollection('owner_avatar')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();
    }

    // ---------------------------------------------
    // Percentage Calculation Methods (Per Share)
    // ---------------------------------------------

    /**
     * Calculate expected profit percentage per share
     */
    public function expectedProfitPercentage(): float
    {
        if (
            !$this->expected_profit ||
            !$this->share_price ||
            $this->share_price <= 0
        ) {
            return 0;
        }

        return round(($this->expected_profit / $this->share_price) * 100, 2);
    }

    /**
     * Calculate expected net profit percentage per share
     */
    public function expectedNetProfitPercentage(): float
    {
        if (
            !$this->expected_net_profit ||
            !$this->share_price ||
            $this->share_price <= 0
        ) {
            return 0;
        }

        return round(($this->expected_net_profit / $this->share_price) * 100, 2);
    }

    /**
     * Calculate shipping fee percentage per share
     */
    public function shippingFeePercentage(): float
    {
        if (
            !$this->shipping_fee_per_share ||
            !$this->share_price ||
            $this->share_price <= 0
        ) {
            return 0;
        }

        return round(($this->shipping_fee_per_share / $this->share_price) * 100, 2);
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

    // ---------------------------------------------
    // Dynamic Status Methods
    // ---------------------------------------------

    /**
     * Calculate dynamic status based on dates and conditions
     */
    public function calculateDynamicStatus(): string
    {
        $now = now();

        // If manually set to completed or suspended, keep it
        if (in_array($this->status, ['completed', 'suspended'])) {
            return $this->status;
        }

        // If not shown yet
        if (!$this->show || !$this->show_date || $this->show_date > $now) {
            return 'draft';
        }

        // If offering hasn't started yet
        if ($this->offering_start_date && $this->offering_start_date > $now) {
            return 'coming';
        }

        // If offering has ended
        if ($this->offering_end_date && $this->offering_end_date < $now) {
            // Check if fully funded
            if ($this->completion_rate >= 100) {
                return 'completed';
            } else {
                return 'expired';
            }
        }

        // If within offering period
        if ($this->isWithinOfferingWindow()) {
            // Check if fully funded
            if ($this->completion_rate >= 100) {
                return 'completed';
            } else {
                return 'open';
            }
        }

        // Default to pending if no clear status
        return 'pending';
    }

    /**
     * Update status dynamically
     */
    public function updateDynamicStatus(): bool
    {
        $newStatus = $this->calculateDynamicStatus();

        if ($this->status !== $newStatus) {
            $this->status = $newStatus;
            return $this->save();
        }

        return false;
    }

    /**
     * Check if status should be updated
     */
    public function shouldUpdateStatus(): bool
    {
        $currentStatus = $this->status;
        $calculatedStatus = $this->calculateDynamicStatus();

        return $currentStatus !== $calculatedStatus;
    }

    /**
     * Get status with label and color
     */
    public function getStatusInfoAttribute(): array
    {
        return [
            'value' => $this->status,
            'text' => \App\InvestmentStatusEnum::label($this->status),
            'color' => \App\InvestmentStatusEnum::color($this->status),
        ];
    }

    /**
     * Process reminders for this opportunity when it becomes available
     */
    public function processReminders(): void
    {
        try {
            $reminderService = app(\App\Services\InvestmentOpportunityReminderService::class);

            // Get all active, unsent reminders for this opportunity
            $reminders = $this->reminders()
                ->active()
                ->unsent()
                ->with(['investorProfile.user'])
                ->get();

            foreach ($reminders as $reminder) {
                try {
                    // Send the reminder notification
                    $this->sendReminderNotification($reminder);

                    // Mark as sent
                    $reminder->markAsSent();

                    \Log::info('Reminder sent for opportunity', [
                        'opportunity_id' => $this->id,
                        'opportunity_name' => $this->name,
                        'reminder_id' => $reminder->id,
                        'investor_id' => $reminder->investor_profile_id,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send reminder for opportunity', [
                        'opportunity_id' => $this->id,
                        'reminder_id' => $reminder->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to process reminders for opportunity', [
                'opportunity_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send reminder notification for a specific reminder
     */
    protected function sendReminderNotification(\App\Models\InvestmentOpportunityReminder $reminder): void
    {
        $investor = $reminder->investorProfile;
        $user = $investor->user;

        // Log the reminder notification
        \Log::info('Sending reminder notification', [
            'user_id' => $user->id,
            'user_phone' => $user->phone,
            'user_email' => $user->email,
            'opportunity_id' => $this->id,
            'opportunity_name' => $this->name,
            'opportunity_start_date' => $this->offering_start_date,
        ]);

        // TODO: Implement actual notification sending (SMS, Email, Push, etc.)
        // Examples:
        // - Send SMS notification
        // - Send email notification
        // - Send push notification
        // - Add to notification queue

        // For now, we'll just dispatch an event that can be listened to
        event(new \App\Events\InvestmentOpportunityAvailable($this, $reminder));
    }

    /**
     * Check if actual profits can be edited
     * Actual profits can only be edited if they haven't been set yet (are null)
     */
    public function canEditActualProfits(): bool
    {
        return $this->actual_profit_per_share === null && $this->actual_net_profit_per_share === null;
    }

    // ---------------------------------------------
    // Investment Type Availability Methods
    // ---------------------------------------------

    /**
     * Check if a specific investment type is allowed
     * التحقق من السماح بنوع استثمار محدد
     */
    public function allowsInvestmentType(string $investmentType): bool
    {
        if ($this->allowed_investment_types === 'both') {
            return true;
        }

        return $this->allowed_investment_types === $investmentType;
    }

    /**
     * Check if 'myself' investment type is allowed
     * التحقق من السماح بنوع الاستثمار 'myself'
     */
    public function allowsMyself(): bool
    {
        return $this->allowsInvestmentType('myself');
    }

    /**
     * Check if 'authorize' investment type is allowed
     * التحقق من السماح بنوع الاستثمار 'authorize'
     */
    public function allowsAuthorize(): bool
    {
        return $this->allowsInvestmentType('authorize');
    }

    /**
     * Get allowed investment types as an array
     * الحصول على أنواع الاستثمار المسموحة كمصفوفة
     */
    public function getAllowedInvestmentTypesArray(): array
    {
        if ($this->allowed_investment_types === 'both' || $this->allowed_investment_types === null) {
            return ['myself', 'authorize'];
        }

        return [$this->allowed_investment_types];
    }

    /**
     * Get investment type availability info
     * الحصول على معلومات توفر أنواع الاستثمار
     */
    public function getInvestmentTypeAvailabilityInfoAttribute(): array
    {
        return [
            'value' => $this->allowed_investment_types,
            'allows_myself' => $this->allowsMyself(),
            'allows_authorize' => $this->allowsAuthorize(),
            'allowed_types' => $this->getAllowedInvestmentTypesArray(),
        ];
    }

}
