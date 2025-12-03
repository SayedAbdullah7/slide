<?php

namespace App\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWalletFloat;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $business_name
 * @property string|null $goal
 * @property string|null $tax_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerProfile whereBusinessName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerProfile whereGoal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerProfile whereId($value)
 * @method static \Illuminate\Database\El   oquent\Builder<static>|OwnerProfile whereTaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerProfile whereUserId($value)
 * @mixin \Eloquent
 */
class OwnerProfile extends Model implements Wallet, WalletFloat
{
    use HasWallet, HasWalletFloat;

    protected $fillable = [
        'user_id',
        'tax_number',
        'business_name',
        'business_address',
        'business_phone',
        'business_email',
        'business_website',
        'business_description',
        'goal',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get business display name
     */
    public function getBusinessDisplayNameAttribute(): string
    {
        return $this->business_name ?: 'Unnamed Business';
    }

    /**
     * Check if owner has wallet functionality
     */
    public function hasWallet(): bool
    {
        return $this->wallet()->exists();
    }

    /**
     * Get wallet balance if available
     */
    public function getWalletBalance(): float
    {
        return $this->hasWallet() ? $this->balance : 0.0;
    }

    /**
     * Get investment opportunities created by this owner
     */
    public function investmentOpportunities()
    {
        return $this->hasMany(InvestmentOpportunity::class, 'owner_profile_id');
    }

    /**
     * Get investment opportunity requests submitted by this owner
     */
    public function investmentOpportunityRequests()
    {
        return $this->hasMany(InvestmentOpportunityRequest::class);
    }

}
