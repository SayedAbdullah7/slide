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
            'phone'        => $data['phone'],
            'email'        => $data['email'] ?? null,
            'password' => !empty($data['password'])
                ? Hash::make((string) $data['password'])
                : null,
            'active_profile_type' => $data['active_profile_type'] ?? null,
            ]);
    }

    public function login(User $user): string
    {
        // حذف جميع الـ tokens القديمة للمستخدم قبل إنشاء token جديد
        // Delete all old tokens for the user before creating a new token
        $user->tokens()->delete();

        // إنشاء token جديد
        // Create a new token
        return $user->createToken('auth_token')->plainTextToken;
    }
}
