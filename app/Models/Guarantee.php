<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\GuaranteeTypeEnum;

/**
 * @property int $id
 * @property int $investment_opportunity_id
 * @property string $type
 * @property string $name
 * @property string|null $description
 * @property numeric|null $value
 * @property string $currency
 * @property bool $is_verified
 * @property \Illuminate\Support\Carbon|null $expiry_date
 * @property string|null $document_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\InvestmentOpportunity $investmentOpportunity
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee verified()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereDocumentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereInvestmentOpportunityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereValue($value)
 * @mixin \Eloquent
 */
class Guarantee extends Model
{
    protected $fillable = [
        'investment_opportunity_id',
        'type',
        'name',
        'description',
        'value',
        'currency',
        'is_verified',
        'expiry_date',
        'document_number',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_verified' => 'boolean',
        'expiry_date' => 'date',
    ];

    // ---------------------------------------------
    // Relationships
    // ---------------------------------------------

    public function investmentOpportunity(): BelongsTo
    {
        return $this->belongsTo(InvestmentOpportunity::class);
    }

    // ---------------------------------------------
    // Scopes
    // ---------------------------------------------

    /**
     * Filter verified guarantees
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    // ---------------------------------------------
    // Accessors
    // ---------------------------------------------

    /**
     * Get the type label in Arabic
     */
    public function getTypeLabelAttribute(): string
    {
        return GuaranteeTypeEnum::label($this->type);
    }

    /**
     * Get the type color
     */
    public function getTypeColorAttribute(): string
    {
        return GuaranteeTypeEnum::color($this->type);
    }

    /**
     * Check if guarantee is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    /**
     * Get formatted value with currency
     */
    public function getFormattedValueAttribute(): string
    {
        if (!$this->value) {
            return 'غير محدد';
        }

        return number_format($this->value, 2) . ' ' . $this->currency;
    }
}
