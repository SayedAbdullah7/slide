<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * InvestmentTransaction Model
 *
 * تتبع كل عملية شراء منفصلة للأسهم في فرصة استثمارية
 * يسمح بتمييز المشتريات المختلفة حتى لو تم دمجها في Investment واحد
 *
 * @property int $id
 * @property int $investment_id
 * @property int $investor_id
 * @property int $opportunity_id
 * @property int $shares
 * @property float $share_price
 * @property float $total_investment
 * @property float $total_payment_required
 * @property string $investment_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class InvestmentTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'investment_id',
        'investor_id',
        'opportunity_id',
        'shares',
        'share_price',
        'total_investment',
        'total_payment_required',
        'investment_type',
    ];

    protected $casts = [
        'share_price' => 'decimal:2',
        'total_investment' => 'decimal:2',
        'total_payment_required' => 'decimal:2',
        'shares' => 'integer',
        'investment_type' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ---------------------------------------------
    // Relationships
    // ---------------------------------------------

    /**
     * Get the investment that this transaction belongs to
     */
    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }

    /**
     * Get the investor profile
     */
    public function investor()
    {
        return $this->belongsTo(InvestorProfile::class, 'investor_id');
    }

    /**
     * Get the investment opportunity
     */
    public function opportunity()
    {
        return $this->belongsTo(InvestmentOpportunity::class, 'opportunity_id');
    }

    // ---------------------------------------------
    // Scopes
    // ---------------------------------------------

    /**
     * Scope for transactions of type 'myself'
     */
    public function scopeMyself($query)
    {
        return $query->where('investment_type', 'myself');
    }

    /**
     * Scope for transactions of type 'authorize'
     */
    public function scopeAuthorize($query)
    {
        return $query->where('investment_type', 'authorize');
    }

    /**
     * Scope for transactions in a specific opportunity
     */
    public function scopeForOpportunity($query, $opportunityId)
    {
        return $query->where('opportunity_id', $opportunityId);
    }

    /**
     * Scope for transactions by a specific investor
     */
    public function scopeForInvestor($query, $investorId)
    {
        return $query->where('investor_id', $investorId);
    }

    // ---------------------------------------------
    // Helper Methods
    // ---------------------------------------------

    /**
     * Check if transaction is of type 'myself'
     */
    public function isMyselfType(): bool
    {
        return $this->investment_type === 'myself';
    }

    /**
     * Check if transaction is of type 'authorize'
     */
    public function isAuthorizeType(): bool
    {
        return $this->investment_type === 'authorize';
    }

    /**
     * Get formatted total investment
     */
    public function getFormattedTotalInvestmentAttribute(): string
    {
        return number_format($this->total_investment, 2) . ' ريال';
    }
}
