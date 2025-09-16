<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\PhoneSession;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Random\RandomException;

class OtpService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * @throws RandomException
     */
    public function generate(string $phone): OtpCode
    {
        $code = random_int(100000, 999999);
        $code = '1234'; // for testing purposes only

        return OtpCode::create([
            'phone' => $phone,
            'code'  => $code,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);
    }

    public function verify(string $phone, string $code): PhoneSession|null
    {
        $otp = OtpCode::where('phone', $phone)
            ->where('code', $code)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$otp || $otp->isExpired()) {
            return null;
        }

        $otp->update(['is_used' => true]);

        // إصدار session token صالح للتسجيل
        return PhoneSession::create([
            'phone'      => $phone,
            'token'      => Str::random(40),
//            'expires_at' => Carbon::now()->addMinutes(15),
            'expires_at' => Carbon::now()->addMinutes(60*24),
        ]);
    }
}
