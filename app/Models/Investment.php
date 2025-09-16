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
    ];

    protected $casts = [
        'amount_invested' => 'decimal:2',
        'shares_purchased' => 'integer',
        'investment_date' => 'datetime',
        'status' => 'string',
    ];

    // ---------------------------------------------
    // Relationships
    // ---------------------------------------------

    public function investmentOpportunity()
    {
        return $this->belongsTo(InvestmentOpportunity::class)->withDefault();
    }

    public function investorProfile()
    {
        return $this->belongsTo(InvestorProfile::class)->withDefault();
    }
}
