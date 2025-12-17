<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $investor_id
 * @property int $opportunity_id
 * @property int $shares
 * @property string $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\InvestmentOpportunity|null $investmentOpportunity
 * @property-read \App\Models\InvestorProfile|null $investorProfile
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investment whereInvestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investment whereOpportunityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investment whereShares($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investment whereUserId($value)
 * @mixin \Eloquent
 */
class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'investor_profile_id',
        'investment_date',
        'status',
        'investor_id',
        'opportunity_id',
        'shares', // عدد الأسهم
        'share_price', // سعر السهم الواحد
        'total_investment', // إجمالي الاستثمار (shares × share_price)
        'total_payment_required', // إجمالي المبلغ المطلوب (بما في ذلك رسوم الشحن لنوع myself)
        'user_id',
        'investment_type', // myself, authorize

        // Merchandise tracking
        'merchandise_status', // pending, arrived
        'expected_delivery_date',
        'expected_distribution_date',
        'merchandise_arrived_at',

        // Financial fields (per share)
        'shipping_fee_per_share', // رسوم الشحن لكل سهم
        'expected_profit_per_share', // الربح المتوقع لكل سهم (بعد خصم رأس المال فقط)
        'expected_net_profit_per_share', // صافي الربح المتوقع لكل سهم (بعد خصم رأس المال والتكاليف)
        'actual_profit_per_share', // الربح الفعلي لكل سهم (بعد خصم رأس المال فقط)
        'actual_net_profit_per_share', // صافي الربح الفعلي لكل سهم (بعد خصم رأس المال والتكاليف)
        'actual_returns_recorded_at',

        // Distribution
        'distribution_status', // pending, distributed
        'distributed_profit', // الربح الموزع
        'distributed_at',
    ];

    protected $casts = [
        'share_price' => 'decimal:2',
        'total_investment' => 'decimal:2',
        'total_payment_required' => 'decimal:2',
        'investment_date' => 'datetime',
        'status' => 'string',
        'shares' => 'integer',
        'investment_type' => 'string',
        'merchandise_status' => 'string',
        'expected_delivery_date' => 'datetime',
        'expected_distribution_date' => 'datetime',
        'merchandise_arrived_at' => 'datetime',
        'shipping_fee_per_share' => 'decimal:2',
        'expected_profit_per_share' => 'decimal:2',
        'expected_net_profit_per_share' => 'decimal:2',
        'actual_profit_per_share' => 'decimal:2',
        'actual_net_profit_per_share' => 'decimal:2',
        'actual_returns_recorded_at' => 'datetime',
        'distribution_status' => 'string',
        'distributed_profit' => 'decimal:2',
        'distributed_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Track changes when investment is updated
        static::updated(function (Investment $investment) {
            // Dispatch shares updated event
            if ($investment->wasChanged('shares')) {
                $oldShares = $investment->getOriginal('shares');
                // event(new \App\Events\InvestmentSharesUpdated(
                //     $investment,
                //     $oldShares ?? 0,
                //     $investment->shares
                // ));
            }

            // Dispatch merchandise status changed event
            if ($investment->wasChanged('merchandise_status')) {
                $oldStatus = $investment->getOriginal('merchandise_status');
                // event(new \App\Events\MerchandiseStatusChanged(
                //     $investment,
                //     $oldStatus ?? 'pending',
                //     $investment->merchandise_status
                // ));
            }

            // Dispatch distribution status changed event
            if ($investment->wasChanged('distribution_status')) {
                $oldStatus = $investment->getOriginal('distribution_status');
                // event(new \App\Events\DistributionStatusChanged(
                //     $investment,
                //     $oldStatus ?? 'pending',
                //     $investment->distribution_status,
                //     $investment->distributed_profit
                // ));
            }
        });
    }

    // ---------------------------------------------
    // Relationships
    // ---------------------------------------------

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function investor()
    {
        return $this->belongsTo(InvestorProfile::class, 'investor_id');
    }

    public function opportunity()
    {
        return $this->belongsTo(InvestmentOpportunity::class, 'opportunity_id');
    }

    // Legacy relationships for backward compatibility
    public function investmentOpportunity()
    {
        return $this->belongsTo(InvestmentOpportunity::class, 'opportunity_id');
    }

    public function investorProfile()
    {
        return $this->belongsTo(InvestorProfile::class, 'investor_id');
    }

    public function distributions()
    {
        return $this->hasMany(InvestmentDistribution::class);
    }

    /**
     * Get all transactions for this investment
     * الحصول على جميع المعاملات لهذا الاستثمار
     */
    public function transactions()
    {
        return $this->hasMany(InvestmentTransaction::class);
    }

    // ---------------------------------------------
    // Scopes
    // ---------------------------------------------

    /**
     * Scope for active investments
     * نطاق الاستثمارات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed investments
     * نطاق الاستثمارات المكتملة
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for cancelled investments
     * نطاق الاستثمارات الملغية
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for pending investments
     * نطاق الاستثمارات المعلقة
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    /**
     * Scope for investments with distribution_status 'distributed'
     * نطاق الاستثمارات الموزعة
     */
    public function scopeStatusDistributed($query)
    {
        return $query->where('distribution_status', 'distributed');
    }

    /**
     * Scope for investments with merchandise_status 'arrived'
     * نطاق الاستثمارات الموصلة
     */
    public function scopeStatusArrived($query)
    {
        return $query->where('merchandise_status', 'arrived');
    }

    /**
     * Scope for investments of type 'myself'
     * نطاق الاستثمارات من النوع myself
     */
    public function scopeMyself($query)
    {
        return $query->where('investment_type', 'myself');
    }

    /**
     * Scope for investments of type 'authorize'
     * نطاق الاستثمارات من النوع authorize
     */
    public function scopeAuthorize($query)
    {
        return $query->where('investment_type', 'authorize');
    }

    /**
     * Scope for not distributed investments of type 'authorize'
     * نطاق الاستثمارات من النوع authorize التي لم توزع بعد
     */
    public function scopeNotDistributedAuthorize($query)
    {
        // Investments of type 'authorize' where distributed_profit is null or 0 (i.e., not distributed yet)
        return $query->authorize()->where(function($q) {
            $q->whereNull('distributed_profit')->orWhere('distributed_profit', '<=', 0);
        });
    }

    /**
     * Scope for distributed investments of type 'authorize'
     * نطاق الاستثمارات الموزعة من النوع authorize
     */
    public function scopeDistributedAuthorize($query)
    {
        return $query->authorize()
            ->where('distribution_status', 'distributed')
            ->where(function($q) {
                $q->whereNotNull('distributed_profit')
                  ->where('distributed_profit', '>', 0);
            });
    }

    /**
     * Scope for 'myself' investments where merchandise has not arrived
     * نطاق الاستثمارات من النوع myself التي لم تصل بضائعها بعد
     */
    public function scopeNotArrivedMyself($query)
    {
        return $query->myself()->where('merchandise_status', 'pending');
    }

    /**
     * Scope for investments with actual returns recorded
     * نطاق الاستثمارات التي تم تسجيل عوائدها الفعلية
     */
    public function scopeWithActualReturns($query)
    {
        return $query->whereNotNull('actual_net_profit_per_share');
    }

    /**
     * Scope for investments ready for distribution (authorize type with actual returns)
     * نطاق الاستثمارات الجاهزة للتوزيع (نوع مفوض مع عوائد فعلية)
     */
    public function scopeReadyForDistribution($query)
    {
        return $query->authorize()
            // ->withActualReturns()
            ->where('distribution_status', '!=', 'distributed');
    }

    /**
     * Scope for investments pending distribution
     * نطاق الاستثمارات المعلقة للتوزيع
     */
    public function scopePendingDistribution($query)
    {
        return $query->where('distribution_status', '!=', 'distributed');
    }

    /**
     * Scope for investments by opportunity
     * نطاق الاستثمارات حسب الفرصة
     */
    public function scopeForOpportunity($query, $opportunityId)
    {
        return $query->where('opportunity_id', $opportunityId);
    }

    /**
     * Scope for investments with merchandise status 'pending'
     * نطاق الاستثمارات ذات حالة البضائع 'معلقة'
     */
    public function scopeStatusPending($query)
    {
        return $query->where('merchandise_status', 'pending');
    }

    /**
     * Scope for investments pending actual returns recording
     * نطاق الاستثمارات المنتظرة لتسجيل العوائد الفعلية
     */
    public function scopePendingActualReturns($query)
    {
        return $query->whereNull('actual_profit_per_share');
    }

    // ---------------------------------------------
    // Accessors & Mutators
    // ---------------------------------------------

    /**
     * Get formatted investment amount
     * الحصول على مبلغ الاستثمار المنسق
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->total_investment, 2) . ' ريال'; // Riyal
    }

    /**
     * Get investment type in Arabic
     * الحصول على نوع الاستثمار بالعربية
     */
    public function getInvestmentTypeArabicAttribute(): string
    {
        return match($this->investment_type) {
            'myself' => 'بيع بنفسي',
            'authorize' => 'تفويض بالبيع',
            default => 'غير محدد'
        };
    }

    /**
     * Get status in Arabic
     * الحصول على الحالة بالعربية
     */
    public function getStatusArabicAttribute(): string
    {
        return match($this->status) {
            'active' => 'نشط',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            'pending' => 'معلق',
            default => 'غير محدد'
        };
    }

    // ---------------------------------------------
    // Helper Methods
    // ---------------------------------------------

    /**
     * Check if investment is active
     * التحقق من كون الاستثمار نشط
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if investment is completed
     * التحقق من كون الاستثمار مكتمل
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if investment is cancelled
     * التحقق من كون الاستثمار ملغي
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if investment is pending
     * التحقق من كون الاستثمار معلق
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if investment type is myself
     * التحقق من كون نوع الاستثمار myself
     */
    public function isMyselfType(): bool
    {
        return $this->investment_type === 'myself';
    }

    /**
     * Check if investment type is authorize
     * التحقق من كون نوع الاستثمار authorize
     */
    public function isAuthorizeType(): bool
    {
        return $this->investment_type === 'authorize';
    }

    /**
     * todo: remove this method
     * Mark investment as completed
     * وضع علامة على الاستثمار كمكتمل
     */
    public function markAsCompleted(): bool
    {
        $this->status = 'completed';
        return $this->save();
    }

    /**
     * Mark investment as cancelled
     * وضع علامة على الاستثمار كملغي
     */
    public function markAsCancelled(): bool
    {
        $this->status = 'cancelled';
        return $this->save();
    }

    // ---------------------------------------------
    // Merchandise Methods
    // ---------------------------------------------

    /**
     * Check if merchandise has arrived
     * التحقق من وصول البضائع
     */
    public function isMerchandiseArrived(): bool
    {
        return $this->merchandise_status === 'arrived';
    }

    /**
     * Check if merchandise is pending
     * التحقق من انتظار البضائع
     */
    public function isMerchandisePending(): bool
    {
        return $this->merchandise_status === 'pending';
    }


    // ---------------------------------------------
    // Returns Methods
    // ---------------------------------------------

    /**
     * Check if actual returns are recorded
     * التحقق من تسجيل العوائد الفعلية
     */
    public function hasActualReturns(): bool
    {
        return $this->actual_profit_per_share !== null && $this->actual_net_profit_per_share !== null;
    }


    // ---------------------------------------------
    // Distribution Methods
    // ---------------------------------------------

    /**
     * Check if returns are distributed
     * التحقق من توزيع العوائد
     */
    public function isDistributed(): bool
    {
        return $this->distribution_status === 'distributed';
    }

    /**
     * Check if investment is ready for distribution
     * التحقق من جاهزية الاستثمار للتوزيع
     */
    public function isReadyForDistribution(): bool
    {
        // if ($this->isMyselfType()) {
        //     return $this->isMerchandiseArrived() && !$this->isDistributed();
        // } else {
        return $this->hasActualReturns() && !$this->isDistributed();
        // }
    }

    /**
     * Check if investment is ready for merchandise arrival
     * التحقق من جاهزية الاستثمار لوصول البضائع
     */
    public function isReadyForMerchandiseArrival(): bool
    {
        return $this->isMyselfType() && $this->isMerchandisePending();
    }

    /**
     * Check if investment is ready for completion
     * التحقق من جاهزية الاستثمار للانتهاء
     */
    public function isReadyForCompletion(): bool
    {
        if ($this->isMyselfType()) {
            return $this->isReadyForMerchandiseArrival();
        } else {
            return $this->isReadyForDistribution();
        }
    }


    // ---------------------------------------------
    // Total Calculation Methods (Per Share)
    // ---------------------------------------------

    /**
     * Calculate total for any per-share value
     * حساب الإجمالي لأي قيمة لكل سهم
     */
    private function calculateTotal(?float $perShareValue): float
    {
        return $this->shares * ($perShareValue ?? 0);
    }

    /**
     * Calculate total shipping and service fee
     * حساب إجمالي رسوم الشحن والخدمة
     */
    public function getTotalShippingAndServiceFee(): float
    {
        return $this->calculateTotal($this->shipping_fee_per_share);
    }

    /**
     * Calculate total expected profit amount
     * حساب إجمالي مبلغ الربح المتوقع
     */
    public function getTotalExpectedProfitAmount(): float
    {
        return $this->calculateTotal($this->expected_profit_per_share);
    }

    /**
     * Calculate total expected net profit
     * حساب إجمالي صافي الربح المتوقع
     */
    public function getTotalExpectedNetProfit(): float
    {
        return $this->calculateTotal($this->expected_net_profit_per_share);
    }

    /**
     * Calculate total actual profit amount
     * حساب إجمالي مبلغ الربح الفعلي
     */
    public function getTotalActualProfitAmount(): float
    {
        return $this->calculateTotal($this->actual_profit_per_share);
    }

    /**
     * Calculate total actual net profit
     * حساب إجمالي صافي الربح الفعلي
     */
    public function getTotalActualNetProfit(): float
    {
        return $this->calculateTotal($this->actual_net_profit_per_share);
    }

    /**
     * Calculate total actual returns (principal + profit)
     * حساب إجمالي العوائد الفعلية (الأصل المدفوع + الربح)
     */
    public function getTotalActualReturns(): float
    {
        return $this->total_investment + $this->getTotalActualNetProfit();
    }

    /**
     * Calculate total amount required (total_investment + shipping fee for myself type)
     * حساب إجمالي المبلغ المطلوب (إجمالي الاستثمار + رسوم الشحن لنوع myself)
     */
    public function getTotalAmountRequired(): float
    {
        if ($this->investment_type === 'myself') {
            // For myself: total_investment + shipping fee
            return $this->total_investment + $this->getTotalShippingAndServiceFee();
        } else {
            // For authorize: only total_investment (no shipping fee)
            return $this->total_investment;
        }
    }

    /**
     * Calculate total investment cost based on investment type
     * حساب إجمالي تكلفة الاستثمار حسب نوع الاستثمار
     */
    public function getTotalInvestmentCost(): float
    {
        return $this->getTotalAmountRequired();
    }

    /**
     * Calculate expected net profit (total expected net profit)
     * حساب صافي الربح المتوقع (إجمالي صافي الربح المتوقع)
     * Note: expected_net_profit_per_share is already net profit per share (after deducting capital)
     */
    public function getExpectedNetProfit(): float
    {
        return $this->getTotalExpectedNetProfit();
    }

    /**
     * Calculate actual net profit (total actual net profit)
     * حساب صافي الربح الفعلي (إجمالي صافي الربح الفعلي)
     * Note: actual_net_profit_per_share is already net profit per share (after deducting capital)
     */
    public function getActualNetProfit(): float
    {
        return $this->getTotalActualNetProfit();
    }

    // ---------------------------------------------
    // Profit Calculation Methods (Unified Logic)
    // ---------------------------------------------

    /**
     * Get realized profit for authorize type investments (distributed profits only)
     * الحصول على الربح المحقق للاستثمارات من نوع authorize (الأرباح الموزعة فقط)
     *
     * This method returns the distributed profit if the investment is of type 'authorize'
     * and has been distributed. Returns 0 otherwise.
     *
     * @return float The distributed profit amount
     */
    public function getRealizedProfit(): float
    {
        // Only authorize type investments have distributed profits
        if (!$this->isAuthorizeType()) {
            return 0;
        }

        // Return distributed profit if it exists and is greater than 0
        if ($this->isDistributed() && $this->distributed_profit > 0) {
            return (float) $this->distributed_profit;
        }

        return 0;
    }

    /**
     * Get pending profit for authorize type investments (expected profits from not distributed investments)
     * الحصول على الربح المعلّق للاستثمارات من نوع authorize (الأرباح المتوقعة من الاستثمارات غير الموزعة)
     *
     * This method returns the expected net profit if the investment is of type 'authorize'
     * and has not been distributed yet. Returns 0 otherwise.
     *
     * @return float The expected net profit amount
     */
    public function getPendingProfit(): float
    {
        // Only authorize type investments have pending profits
        if (!$this->isAuthorizeType()) {
            return 0;
        }

        // Return expected net profit if investment is not distributed yet
        if (!$this->isDistributed()) {
            return $this->getExpectedNetProfit();
        }

        return 0;
    }




    /**
     * Calculate percentage for any per-share value
     * حساب النسبة المئوية لأي قيمة لكل سهم
     */
    private function calculatePercentagePerShare(?float $value): float
    {
        $pricePerShare = $this->getPricePerShare();
        if (!$value || !$pricePerShare || $pricePerShare <= 0) {
            return 0;
        }
        return round(($value / $pricePerShare) * 100, 2);
    }

    /**
     * Calculate expected profit percentage per share
     * حساب نسبة الربح المتوقع لكل سهم
     */
    public function getExpectedProfitPercentage(): float
    {
        return $this->calculatePercentagePerShare($this->expected_profit_per_share);
    }

    /**
     * Calculate expected net profit percentage per share
     * حساب نسبة صافي الربح المتوقع لكل سهم
     */
    public function getExpectedNetProfitPercentage(): float
    {
        return $this->calculatePercentagePerShare($this->expected_net_profit_per_share);
    }

    /**
     * Calculate actual profit percentage per share
     * حساب نسبة الربح الفعلي لكل سهم
     */
    public function getActualProfitPercentage(): float
    {
        return $this->calculatePercentagePerShare($this->actual_profit_per_share);
    }

    /**
     * Calculate actual net profit percentage per share
     * حساب نسبة صافي الربح الفعلي لكل سهم
     */
    public function getActualNetProfitPercentage(): float
    {
        return $this->calculatePercentagePerShare($this->actual_net_profit_per_share);
    }

    /**
     * Calculate profit performance percentage (actual vs expected)
     * حساب نسبة أداء الربح (الفعلي مقابل المتوقع)
     */
    public function getProfitPerformancePercentage(): float
    {
        $actualPercentage = $this->getActualNetProfitPercentage();
        $expectedPercentage = $this->getExpectedNetProfitPercentage();

        if ($actualPercentage > 0 && $expectedPercentage > 0) {
            // return round((($actualPercentage - $expectedPercentage) / $expectedPercentage) * 100, 2);
            return $actualPercentage - $expectedPercentage;
        }

        return 0;
    }

    /**
     * Get profit performance status (exceeded, met, or below)
     * الحصول على حالة أداء الربح (تجاوز، حقق، أو أقل)
     */
    public function getProfitPerformanceStatus(): string
    {
        $actualPercentage = $this->getActualNetProfitPercentage();
        $expectedPercentage = $this->getExpectedNetProfitPercentage();

        if ($actualPercentage > $expectedPercentage) {
            return 'exceeded';
        } elseif ($actualPercentage < $expectedPercentage) {
            return 'below';
        }

        return 'met';
    }

    /**
     * Get price per share
     * الحصول على سعر السهم الواحد
     */
    public function getPricePerShare(): float
    {
        return $this->share_price ?? 0;
    }

    /**
     * Get total payment required (total_investment + shipping fee for myself type)
     * الحصول على إجمالي المبلغ المطلوب (إجمالي الاستثمار + رسوم الشحن لنوع myself)
     */
    public function getTotal(): float
    {
        return $this->total_payment_required ?? $this->getTotalAmountRequired();
    }

    /**
     * Get all calculated totals in one array
     * الحصول على جميع الإجماليات المحسوبة في مصفوفة واحدة
     */
    public function getAllTotals(): array
    {
        return [
            'basic_info' => [
                'shares' => $this->shares,
                'share_price' => $this->share_price,
                'total_investment' => $this->total_investment,
                'total_payment_required' => $this->getTotal(),
                'price_per_share' => $this->getPricePerShare(),
                'investment_type' => $this->investment_type,
            ],
            'costs' => [
                'shipping_and_service_fee' => $this->getTotalShippingAndServiceFee(),
                'total_amount_required' => $this->getTotalAmountRequired(),
                'total_investment_cost' => $this->getTotalInvestmentCost(),
            ],
            'expected_returns' => [
                'profit_amount' => $this->getTotalExpectedProfitAmount(),
                'net_profit' => $this->getTotalExpectedNetProfit(),
                'total_net_profit' => $this->getExpectedNetProfit(),
            ],
            'actual_returns' => [
                'profit_amount' => $this->getTotalActualProfitAmount(),
                'net_profit' => $this->getTotalActualNetProfit(),
                'total_net_profit' => $this->getActualNetProfit(),
            ],
            'per_share_percentages' => [
                'expected_profit_percentage' => $this->getExpectedProfitPercentage(),
                'expected_net_profit_percentage' => $this->getExpectedNetProfitPercentage(),
                'actual_profit_percentage' => $this->getActualProfitPercentage(),
                'actual_net_profit_percentage' => $this->getActualNetProfitPercentage(),
            ],
            'variance' => [
                'profit_amount' => $this->getTotalActualProfitAmount() - $this->getTotalExpectedProfitAmount(),
                'net_profit' => $this->getTotalActualNetProfit() - $this->getTotalExpectedNetProfit(),
                'total_net_profit' => $this->getActualNetProfit() - $this->getExpectedNetProfit(),
            ],
        ];
    }
}
