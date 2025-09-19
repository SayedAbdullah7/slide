<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $extra_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile whereExtraData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile whereUserId($value)
 * @mixin \Eloquent
 */
class InvestorProfile extends Model
{
    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function investments()
    {
        return $this->hasMany(Investment::class, 'investor_id');
    }

    /**
     * Check if investor has wallet functionality
     * التحقق من وجود وظائف المحفظة للمستثمر
     */
    public function hasWallet(): bool
    {
        return method_exists($this, 'balance');
    }

    /**
     * Get wallet balance if available
     * الحصول على رصيد المحفظة إذا كان متاحاً
     */
    public function getWalletBalance(): float
    {
        return $this->hasWallet() ? $this->balance : 0.0;
    }
}
