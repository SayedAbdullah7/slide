<?php

namespace App\Models;

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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerProfile whereTaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OwnerProfile whereUserId($value)
 * @mixin \Eloquent
 */
class OwnerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'tax_number',
        'business_name',
        'business_address',
        'business_phone',
        'business_email',
        'business_website',
        'business_description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
