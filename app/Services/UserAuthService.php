<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UserAuthService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function register(array $data): User
    {
        return User::create([
            'full_name'    => $data['full_name'],
            'phone'        => $data['phone'],
            'email'        => $data['email'] ?? null,
            'national_id'  => $data['national_id'] ?? null, // ✅ corrected here
            'birth_date'   => $data['birth_date'] ?? null,
            'password' => !empty($data['password'])
                ? Hash::make((string) $data['password'])
                : null,
            ]);
    }

    public function login(User $user): string
    {
        return $user->createToken('auth_token')->plainTextToken;
    }
}
