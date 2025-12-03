<?php
use App\Models\User;

if (!function_exists('currentUser')) {
    /**
     * Get the currently authenticated user or null.
     *
     * @return User|null
     */
    function currentUser(): ?User
    {
        return auth()->user();
    }
}

if (!function_exists('isInvestor')) {
    /**
     * Check if the authenticated user is an investor.
     *
     * @return bool
     */
    function isInvestor(): bool
    {
        $user = currentUser();
        return $user ? $user->active_profile_type === User::PROFILE_INVESTOR && $user->hasInvestorProfile() : false;
    }
}

if (!function_exists('isOwner')) {
    /**
     * Check if the authenticated user is an owner.
     *
     * @return bool
     */
    function isOwner(): bool
    {
        $user = currentUser();
        return $user ? $user->active_profile_type === User::PROFILE_OWNER && $user->hasOwnerProfile() : false;
    }
}
