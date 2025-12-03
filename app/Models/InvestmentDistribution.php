<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $investment_id
 * @property float $distributed_amount
 * @property bool $is_distributed
 * @property \Illuminate\Support\Carbon|null $distributed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Investment $investment
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentDistribution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentDistribution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentDistribution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentDistribution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentDistribution whereDistributedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentDistribution whereDistributedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentDistribution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentDistribution whereInvestmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentDistribution whereIsDistributed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentDistribution whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class InvestmentDistribution extends Model
{
    protected $fillable = [
        'investment_id',
        'distributed_amount',
        'is_distributed',
        'distributed_at',
    ];

    protected $casts = [
        'distributed_amount' => 'decimal:2',
        'is_distributed' => 'boolean',
        'distributed_at' => 'datetime',
    ];

    // ---------------------------------------------
    // Relationships
    // ---------------------------------------------

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }

    // ---------------------------------------------
    // Scopes
    // ---------------------------------------------

    /**
     * Scope for distributed distributions
     * نطاق التوزيعات الموزعة
     */
    public function scopeDistributed($query)
    {
        return $query->where('is_distributed', true);
    }

    /**
     * Scope for pending distributions
     * نطاق التوزيعات المعلقة
     */
    public function scopePending($query)
    {
        return $query->where('is_distributed', false);
    }

    // ---------------------------------------------
    // Helper Methods
    // ---------------------------------------------

    /**
     * Mark distribution as completed
     * وضع علامة على التوزيع كمكتمل
     */
    public function markAsDistributed(): bool
    {
        $this->is_distributed = true;
        $this->distributed_at = now();
        return $this->save();
    }

    /**
     * Check if distribution is completed
     * التحقق من اكتمال التوزيع
     */
    public function isDistributed(): bool
    {
        return $this->is_distributed;
    }

    /**
     * Check if distribution is pending
     * التحقق من انتظار التوزيع
     */
    public function isPending(): bool
    {
        return !$this->is_distributed;
    }
}
