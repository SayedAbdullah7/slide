<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\PhoneSession;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Random\RandomException;

class OtpService
{
    protected $smsService;

    /**
     * OTP expiration time in minutes
     */
    protected const OTP_EXPIRY_MINUTES = 1;

    /**
     * Create a new class instance.
     */
    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Generate and send OTP code via SMS
     *
     * @param string $phone
     * @param string $type Type of OTP: 'login', 'confirm', 'register'
     * @param string $operationName Operation name for confirm type
     * @return array ['success' => bool, 'otp' => OtpCode|null, 'message' => string]
     * @throws RandomException
     */
    public function generate(string $phone, string $type = 'login', string $operationName = ''): array|string
    {
        // Check if there's a recent OTP (within 2 minutes) for this phone number
        $recentOtp = OtpCode::where('phone', $phone)
            ->where('is_used', false)
            ->where('created_at', '>=', Carbon::now()->subMinutes(self::OTP_EXPIRY_MINUTES))
            ->latest()
            ->first();

        if ($recentOtp) {
            $waitUntil = $recentOtp->created_at->copy()->addMinutes(self::OTP_EXPIRY_MINUTES);
            $secondsRemaining = max(0, Carbon::now()->diffInSeconds($waitUntil, false));

            $secondsRemaining = (int) ceil($secondsRemaining); // round up to the nearest second
            return [
                'success' => false,
                'otp' => null,
                'message' => 'يرجى الانتظار ' . $secondsRemaining . ' ثانية قبل طلب رمز تحقق جديد.',
                'next_available_at' => $waitUntil->toIso8601String(),
                'seconds_remaining' => $secondsRemaining
            ];
        }

        // $testMode = false;
        $testMode = true;



        if ($testMode) {
            $code = '1234';
        } else {

            // like 1234
            $code = rand(1000, 9999);
            // $code = random_int(100000, 999999);

        }

        // Uncomment the line below for testing (bypasses SMS sending)
        // $code = '1234'; // for testing purposes only

        try {
            return DB::transaction(function () use ($phone, $code, $testMode, $type, $operationName) {
                // Create OTP record
                $otpRecord = OtpCode::create([
                    'phone' => $phone,
                    'code'  => $code,
                    'expires_at' => Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES),
                ]);

                if ($testMode) {
                    return [
                        'success' => true,
                        'otp' => $otpRecord,
                        'message' => 'تم إرسال رمز التحقق بنجاح',
                        'expires_at' => $otpRecord->expires_at->toIso8601String()
                    ];
                }
                $type = 'loginAndRegister';

                // return $type;
                // Send OTP via SMS with proper template
                $result = $this->smsService->sendOtp($phone, $code, null, $type, $operationName);
                $result;
                if ($result['success']) {
                    Log::info('OTP sent successfully', [
                        'phone' => $phone,
                        'otp_id' => $otpRecord->id,
                        'type' => $type,
                        'operation' => $operationName
                    ]);

                    return [
                        'success' => true,
                        'otp' => $otpRecord,
                        'message' => 'تم إرسال رمز التحقق بنجاح',
                        'expires_at' => $otpRecord->expires_at->toIso8601String()
                    ];
                } else {
                    Log::warning('Failed to send OTP via SMS', [
                        'phone' => $phone,
                        'error' => $result['message']
                    ]);

                    // Rollback transaction - OTP won't be saved
                    throw new \Exception($result['message']);
                }
            });
        } catch (\Exception $e) {
            Log::error('Exception while sending OTP', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'otp' => null,
                'message' => $e->getMessage()
                // 'message' => 'Failed to send OTP'
            ];
        }
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
