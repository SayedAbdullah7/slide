<?php

namespace App\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWalletFloat;
use Illuminate\Database\Eloquent\Model;
use App\Models\Investment;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $full_name
 * @property string|null $birth_date
 * @property string|null $national_id
 * @property string|null $extra_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile whereNationalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile whereExtraData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorProfile whereUserId($value)
 * @mixin \Eloquent
 */
class InvestorProfile extends Model implements Wallet, WalletFloat
{
    use HasWallet, HasWalletFloat;

    protected $fillable = [
        'user_id',
        'full_name',
        'birth_date',
        'national_id',
        'extra_data',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function investments()
    {
        return $this->hasMany(Investment::class, 'investor_id');
    }

    public function reminders()
    {
        return $this->hasMany(InvestmentOpportunityReminder::class);
    }

    public function savedOpportunities()
    {
        return $this->hasMany(SavedInvestmentOpportunity::class);
    }

    /**
     * Check if investor has wallet functionality
     * التحقق من وجود وظائف المحفظة للمستثمر
     */
    public function hasWallet(): bool
    {
        return $this->wallet()->exists();
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
