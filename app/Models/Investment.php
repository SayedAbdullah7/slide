<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
