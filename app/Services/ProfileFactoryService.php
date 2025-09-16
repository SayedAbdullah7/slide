<?php

namespace App\Services;

use App\Models\User;
use App\Models\InvestorProfile;
use App\Models\OwnerProfile;
use Illuminate\Validation\ValidationException;

class ProfileFactoryService
{
    /**
     * @throws ValidationException
     */
    public function createInvestor(User $user, array $data = []): \Illuminate\Database\Eloquent\Model
    {
        if ($user->hasInvestor()) {
            throw ValidationException::withMessages(['investor' => 'Investor profile already exists.']);
        }
        return $user->investorProfile()->create([
            'extra_data' => $data['extra_data'] ?? null,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function createOwner(User $user, array $data): \Illuminate\Database\Eloquent\Model
    {
        if ($user->hasOwner()) {
            throw ValidationException::withMessages(['owner' => 'Owner profile already exists.']);
        }
        return $user->ownerProfile()->create([
//            'company_name' => $data['company_name'] ?? throw ValidationException::withMessages(['company_name' => 'Required']),
            'tax_number'   => $data['tax_number'] ?? null,
        ]);
    }

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
