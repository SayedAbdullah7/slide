<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class ProfileSwitchService
{
    public function switch(User $user, string $toType): User
    {
        $toType = strtolower($toType);

        if (!in_array($toType, User::PROFILE_TYPES, true)) {
            throw ValidationException::withMessages(['profile_type' => 'Invalid profile type.']);
        }

       // Check if user has the requested profile type
        $hasProfile = match ($toType) {
            User::PROFILE_INVESTOR => $user->hasInvestor(),
            User::PROFILE_OWNER => $user->hasOwner(),
        };

        if (!$hasProfile) {
            throw ValidationException::withMessages([
                'profile_type' => "User doesn't have a {$toType} profile.",
            ]);
        }

        $user->forceFill(['active_profile_type' => $toType])->save();

        return $user->fresh(['investorProfile','ownerProfile']);
    }
}
