<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedInvestmentOpportunity extends Model
{
    protected $fillable = [
        'investor_profile_id',
        'investment_opportunity_id',
    ];

    public function investorProfile(): BelongsTo
    {
        return $this->belongsTo(InvestorProfile::class);
    }

    public function investmentOpportunity(): BelongsTo
    {
        return $this->belongsTo(InvestmentOpportunity::class);
    }
}
