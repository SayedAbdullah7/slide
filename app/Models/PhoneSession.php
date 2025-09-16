<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $phone
 * @property string $token
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhoneSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhoneSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhoneSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhoneSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhoneSession whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhoneSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhoneSession wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhoneSession whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhoneSession whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PhoneSession extends Model
{
    protected $fillable = ['phone', 'token', 'expires_at'];
    protected $casts = ['expires_at' => 'datetime'];

    public function isExpired(): bool {
        return $this->expires_at->isPast();
    }
}
