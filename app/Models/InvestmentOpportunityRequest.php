<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\InvestmentOpportunityRequestStatusEnum;

/**
 * @property int $id
 * @property int $owner_profile_id
 * @property string|null $company_age
 * @property string|null $commercial_experience
 * @property string|null $net_profit_margins
 * @property float|null $required_amount
 * @property string|null $description
 * @property string|null $guarantee_type
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OwnerProfile $ownerProfile
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunityRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunityRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunityRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunityRequest whereCompanyAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunityRequest whereCommercialExperience($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunityRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunityRequest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunityRequest whereGuaranteeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunityRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunityRequest whereNetProfitMargins($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunityRequest whereOwnerProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunityRequest whereRequiredAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunityRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentOpportunityRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class InvestmentOpportunityRequest extends Model
{
    protected $fillable = [
        'owner_profile_id',
        'company_age',
        'commercial_experience',
        'net_profit_margins',
        'required_amount',
        'description',
        'guarantee_type',
        'status',
    ];

    protected $casts = [
        'required_amount' => 'decimal:2',
    ];

    // ---------------------------------------------
    // Relationships
    // ---------------------------------------------

    public function ownerProfile(): BelongsTo
    {
        return $this->belongsTo(OwnerProfile::class);
    }

    // ---------------------------------------------
    // Accessors
    // ---------------------------------------------

    /**
     * Get the guarantee type label in Arabic
     */
    public function getGuaranteeTypeLabelAttribute(): ?string
    {
        return $this->guarantee_type ? \App\GuaranteeTypeEnum::label($this->guarantee_type) : null;
    }

    /**
     * Get the guarantee type color
     */
    public function getGuaranteeTypeColorAttribute(): ?string
    {
        return $this->guarantee_type ? \App\GuaranteeTypeEnum::color($this->guarantee_type) : null;
    }

    /**
     * Get the status label in Arabic
     */
    public function getStatusLabelAttribute(): string
    {
        return InvestmentOpportunityRequestStatusEnum::label($this->status);
    }

    /**
     * Get the status color
     */
    public function getStatusColorAttribute(): string
    {
        return InvestmentOpportunityRequestStatusEnum::color($this->status);
    }

    /**
     * Get formatted required amount with currency
     */
    public function getFormattedRequiredAmountAttribute(): ?string
    {
        return $this->required_amount ? number_format($this->required_amount, 2) . ' ريال' : null;
    }

    // ---------------------------------------------
    // Scopes
    // ---------------------------------------------

    /**
     * Filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Filter by owner profile
     */
    public function scopeOwnerProfile($query, int $ownerProfileId)
    {
        return $query->where('owner_profile_id', $ownerProfileId);
    }

    /**
     * Filter pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', InvestmentOpportunityRequestStatusEnum::PENDING->value);
    }

    /**
     * Filter approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', InvestmentOpportunityRequestStatusEnum::APPROVED->value);
    }

    /**
     * Filter rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', InvestmentOpportunityRequestStatusEnum::REJECTED->value);
    }

    /**
     * Filter under review requests
     */
    public function scopeUnderReview($query)
    {
        return $query->where('status', InvestmentOpportunityRequestStatusEnum::UNDER_REVIEW->value);
    }

    /**
     * Filter cancelled requests
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', InvestmentOpportunityRequestStatusEnum::CANCELLED->value);
    }

    /**
     * Filter editable requests (pending and under review)
     */
    public function scopeEditable($query)
    {
        return $query->whereIn('status', InvestmentOpportunityRequestStatusEnum::editableStatuses());
    }

    /**
     * Filter deletable requests (only pending)
     */
    public function scopeDeletable($query)
    {
        return $query->whereIn('status', InvestmentOpportunityRequestStatusEnum::deletableStatuses());
    }

    /**
     * Filter final status requests
     */
    public function scopeFinal($query)
    {
        return $query->whereIn('status', InvestmentOpportunityRequestStatusEnum::finalStatuses());
    }
}
