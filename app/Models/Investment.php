<?php

namespace App\Models;

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
    protected $fillable = [
        'investment_opportunity_id',
        'investor_profile_id',
        'amount_invested',
        'shares_purchased',
        'investment_date',
        'status',
        'investor_id',
        'opportunity_id',
        'shares',
        'amount',
        'user_id',
        'investment_type',
    ];

    protected $casts = [
        'amount_invested' => 'decimal:2',
        'shares_purchased' => 'integer',
        'investment_date' => 'datetime',
        'status' => 'string',
        'amount' => 'decimal:2',
        'shares' => 'integer',
        'investment_type' => 'string',
    ];

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

    // ---------------------------------------------
    // Accessors & Mutators
    // ---------------------------------------------

    /**
     * Get formatted investment amount
     * الحصول على مبلغ الاستثمار المنسق
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ريال'; // Riyal
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
}
