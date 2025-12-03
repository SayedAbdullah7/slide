<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string|null $full_name
 * @property string|null $phone
 * @property string|null $phone_verified_at
 * @property int|null $is_active
 * @property int|null $is_registered
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $active_profile_type
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\InvestorProfile|null $investorProfile
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\OwnerProfile|null $ownerProfile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereActiveProfileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, InteractsWithMedia;
    use HasApiTokens;

    public const PROFILE_INVESTOR = 'investor';
    public const PROFILE_OWNER = 'owner';
    public const PROFILE_TYPES = [self::PROFILE_INVESTOR, self::PROFILE_OWNER];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'password',
        'is_active',
        'is_registered',
        'active_profile_type',
        'notifications_enabled'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_registered' => 'boolean',
            'notifications_enabled' => 'boolean',
        ];
    }

    /**
     * Determine if the user has notifications enabled.
     */
    public function hasNotificationsEnabled(): bool
    {
        return (bool) $this->notifications_enabled;
    }


    /**
     * Check if user has a password set
     */
    public function hasPassword(): bool
    {
        return !empty($this->password);
    }

    /**
     * Get user's display name
     */
    public function getDisplayNameAttribute(): string
    {
        $displayName = $this->resolveDisplayName();

        return $displayName ?? $this->email ?? $this->phone ?? 'User #' . $this->id;
    }

    public function getDisplayName(): string
    {
        return $this->getDisplayNameAttribute();
    }

    public function getFullNameAttribute(): ?string
    {
        if ($this->active_profile_type === self::PROFILE_INVESTOR) {
            return $this->investorProfile?->full_name;
        }

        if ($this->active_profile_type === self::PROFILE_OWNER) {
            return $this->ownerProfile?->business_name;
        }

        return $this->attributes['full_name']
            ?? $this->investorProfile?->full_name
            ?? $this->ownerProfile?->business_name;
    }

    protected function resolveDisplayName(): ?string
    {
        $investorName = $this->investorProfile?->full_name;
        if (!empty($investorName)) {
            return $investorName;
        }

        $ownerName = $this->ownerProfile?->business_name;
        if (!empty($ownerName)) {
            return $ownerName;
        }

        $legacyName = $this->attributes['full_name'] ?? null;
        if (!empty($legacyName)) {
            return $legacyName;
        }

        return null;
    }

    // get first name from full name of investor profile
    public function getFirstName(): string
    {
        return $this->investorProfile?->full_name ? explode(' ', $this->investorProfile?->full_name)[0] : '';
    }

    // get last name from full name of investor profile
    public function getLastName(): string
    {
        $fullName = $this->investorProfile?->full_name;
        if (!$fullName) {
            return '';
        }
        $parts = explode(' ', $fullName, 2);
        // If there is no space, return the whole name as last name
        return isset($parts[1]) ? $parts[1] : $parts[0];
    }

    /**
     * Scope to filter active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter registered users
     */
    public function scopeRegistered($query)
    {
        return $query->where('is_registered', true);
    }


    public function investorProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(InvestorProfile::class);
    }

    public function ownerProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(OwnerProfile::class);
    }

    public function hasInvestor(): bool
    {
        return $this->investorProfile()->exists();
    }

    public function hasOwner(): bool
    {
        return $this->ownerProfile()->exists();
    }

    public function hasProfile(string $profileType): bool
    {
        return match ($profileType) {
            self::PROFILE_INVESTOR => $this->hasInvestor(),
            self::PROFILE_OWNER => $this->hasOwner(),
            default => false,
        };
    }

    public function activeProfile(): mixed
    {
        return match ($this->active_profile_type) {
            self::PROFILE_INVESTOR => $this->investorProfile,
            self::PROFILE_OWNER => $this->ownerProfile,
            default => null,
        };
    }

    /**
     * Ensure active_profile_type is set based on available profiles
     */
    public function ensureActiveProfileType(): void
    {
        if (!$this->active_profile_type) {
            if ($this->hasInvestor()) {
                $this->update(['active_profile_type' => self::PROFILE_INVESTOR]);
            } elseif ($this->hasOwner()) {
                $this->update(['active_profile_type' => self::PROFILE_OWNER]);
            }
        }
    }

    /**
     * Get survey answers for this user
     */
    public function surveyAnswers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('avatar')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();
    }

    /**
     * Get saved payment cards for this user
     */
    public function savedCards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserCard::class);
    }

    /**
     * Get FCM tokens for this user
     */
    public function fcmTokens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FcmToken::class);
    }

    /**
     * Get active FCM tokens for this user
     */
    public function activeFcmTokens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->fcmTokens()->active();
    }

    /**
     * Set (add or update) the FCM token for this user.
     * Each user can have only one FCM token, and each token belongs to only one user.
     * When setting a new token, remove any previous tokens for this user.
     */
    public function addFcmToken(string $token, ?string $deviceId = null, ?string $platform = null, ?string $appVersion = null): FcmToken
    {
        // Remove all previous tokens for this user
        $this->fcmTokens()->delete();

        // Remove this token from any other user (enforce one-to-one globally)
        FcmToken::where('token', $token)->where('user_id', '!=', $this->id)->delete();

        // Create the new token for this user
        return $this->fcmTokens()->create([
            'token' => $token,
            'device_id' => $deviceId,
            'platform' => $platform,
            'app_version' => $appVersion,
            'is_active' => true,
            'last_used_at' => now(),
        ]);
    }

    /**
     * Remove FCM token for this user
     */
    public function removeFcmToken(string $token): bool
    {
        return $this->fcmTokens()->where('token', $token)->delete() > 0;
    }

    /**
     * Remove FCM token by device ID
     */
    public function removeFcmTokenByDevice(string $deviceId): bool
    {
        return $this->fcmTokens()->where('device_id', $deviceId)->delete() > 0;
    }

    /**
     * Deactivate all FCM tokens for this user
     */
    public function deactivateAllFcmTokens(): int
    {
        return $this->fcmTokens()->update(['is_active' => false]);
    }

    /**
     * Get bank accounts for this user
     */
    public function bankAccounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * Get withdrawal requests for this user
     */
    public function withdrawalRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    /**
     * Get bank transfer requests for this user
     */
    public function bankTransferRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BankTransferRequest::class);
    }

    /**
     * Get deletion requests for this user
     */
    public function deletionRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserDeletionRequest::class);
    }

    /**
     * Get pending deletion requests for this user
     */
    public function pendingDeletionRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->deletionRequests()->pending();
    }

    /**
     * Check if user has any pending deletion requests
     */
    public function hasPendingDeleteRequest(): bool
    {
        return $this->pendingDeletionRequests()->exists();
    }

    /**
     * Cancel all pending deletion requests for this user
     */
    public function cancelDeleteRequest(): int
    {
        return $this->pendingDeletionRequests()->update([
            'status' => UserDeletionRequest::STATUS_CANCELLED,
            'processed_at' => now()
        ]);
    }

    /**
     * Create a new deletion request for this user
     */
    public function requestDeletion(?string $reason = null): UserDeletionRequest
    {
        return $this->deletionRequests()->create([
            'reason' => $reason,
            'status' => UserDeletionRequest::STATUS_PENDING,
            'requested_at' => now()
        ]);
    }
}
