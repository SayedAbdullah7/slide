<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhoneSessionResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Services\{OtpService, ProfileFactoryService, UserAuthService, SurveyService};
use App\Models\{User, PhoneSession};
class UserAuthController extends Controller
{
    use ApiResponseTrait;
    protected $otpService, $UserAuthService, $surveyService, $profileFactoryService;

    public function __construct(OtpService $otpService, UserAuthService $UserAuthService, SurveyService $surveyService, ProfileFactoryService $profileFactoryService)
    {
        $this->otpService = $otpService;
        $this->UserAuthService = $UserAuthService;
        $this->surveyService = $surveyService;
        $this->profileFactoryService = $profileFactoryService;
    }


    /**
     * Check if phone exists and get profile information
     */
    public function checkPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return $this->respondSuccessWithData('Phone not registered', [
                'exists' => false,
                'profiles' => [
                    'investor' => false,
                    'owner' => false,
                ]
            ]);
        }

        return $this->respondSuccessWithData('Phone exists', [
            'exists' => true,
            'profiles' => [
                'investor' => $user->hasInvestor(),
                'owner' => $user->hasOwner(),
            ],
            'has_password' => $user->hasPassword(),
        ]);
    }


    // إرسال OTP
    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => 'required|string']);
        $otp = $this->otpService->generate($request->phone);

        // TODO: إرسال كـ SMS
//        return response()->json(['message'=>'OTP sent','debug_code'=>$otp->code]);
        return $this->respondSuccess('OTP sent');
    }

    // التحقق من OTP → إصدار Session Token

    /**
     * @throws ValidationException
     */
    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'code'  => 'required|string',
            'profile' => 'sometimes|in:owner,investor',
        ]);

        $session = $this->otpService->verify($data['phone'], $data['code']);

        if (!$session) {
            return $this->respondError('OTP invalid or expired', 422);
        }

        $user = User::where('phone', $data['phone'])->first();

        if ($user) {
            if ($request->profile && $user->active_profile_type !== $request->profile) {
                $user = $this->profileFactoryService->switch($user, $request->profile);
            }
            $token = $this->UserAuthService->login($user);

            return $this->respondSuccessWithData('OTP verified successfully', [
                'is_new_user' => false,
                'token'       => $token,
                'user'        => $user,
                'profiles' => [
                    'investor' => $user->hasInvestor(),
                    'owner' => $user->hasOwner(),
                ],
                'active_profile_type' => $user->active_profile_type,
                'phone_session' => new PhoneSessionResource($session),
                'has_password' => $user->hasPassword(),
            ]);
        }

        return $this->respondSuccessWithData('OTP verified successfully', [
            'is_new_user'   => true,
            'phone_session' => new PhoneSessionResource($session),
        ]);
    }


    // التسجيل + إجابة الأسئلة

    /**
     * @throws ValidationException
     */
    public function register(Request $request)
    {
        $tokenPhone = $request->session_token;
        $session = PhoneSession::where('token', $tokenPhone)->first();
        if (!$session || $session->isExpired()) {

            return $this->respondError('Session expired', 422);
        }

        // check if user already exists
        $user = User::where('phone', $session->phone)->first();

        if (!$user) {
            // if user not exists, validate for create new user
            $data = $request->validate([
                'session_token' => 'required|string|exists:phone_sessions,token',
                'full_name'      => 'required|string',
                'email' => 'nullable|email|unique:users,email',
                'national_id'     => 'required|string',
                'birth_date' => 'required|date|date_format:Y-m-d',
                'answers'          => 'required|array',
                'profile' => 'required|in:owner,investor',
                'tax_number'     => 'required_if:profile,owner',
//                'tax_number' => 'required_if:profile,owner|unique:owner_profiles,tax_number',
            ]);
            //validate answers
            $this->surveyService->validateAnswers($data['answers']);
        }else{
            // if user exists, validate for create new profile
            $data = $request->validate([
                'session_token' => 'required|string|exists:phone_sessions,token',
                'profile' => 'required|in:owner,investor',
//                'tax_number'     => 'required_if:profile,owner',
                'tax_number' => 'required_if:profile,owner|unique:owner_profiles,tax_number',
            ]);
        }

       // create user if not exists
        if (!$user) {
            $user = $this->UserAuthService->register([
                'phone'             => $session->phone,
                'full_name'        => $data['full_name'],
                'email'               => $data['email'] ?? null,
                'national_id'      => $data['national_id'] ?? null,
                'birth_date'       => $data['birth_date'] ?? null,
            ]);
            // save answers
            $this->surveyService->saveAnswers($user, $data['answers']);
        }

        if ($data['profile'] === 'owner') {
//            if ($user->hasOwner()) {
//                throw ValidationException::withMessages(['owner' => 'Owner profile already exists.']);
//            }
            $this->profileFactoryService->createOwner($user, $data);
        } elseif ($data['profile'] === 'investor') {
//            if ($user->hasInvestor()) {
//                throw ValidationException::withMessages(['investor' => 'Investor profile already exists.']);
//            }
            $this->profileFactoryService->createInvestor($user, $data);

        }

        $token = $this->UserAuthService->login($user);

//        return response()->json([
//            'message'=>'Registered successfully',
//            'token' F => $token,
//            'user'   => $user,
//        ]);

        return $this->respondSuccessWithData('Registered successfully', [
            'token'  => $token,
            'user'   => $user,
            'profiles' => [
                'investor' => $user->hasInvestor(),
                'owner' => $user->hasOwner(),
            ],
            'active_profile_type' => $user->active_profile_type,
        ]);
    }


    public function setPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'session_token' => 'required|string|exists:phone_sessions,token',
            'password'      => 'required|string|min:4|max:10|confirmed', // password_confirmation required
        ]);

        $session = PhoneSession::where('token', $data['session_token'])->first();

        if (!$session || $session->isExpired()) {
            return $this->respondError('Session expired', 422);
        }

        $user = User::where('phone', $session->phone)->first();

        if (!$user) {
            return $this->respondError('User not found', 404);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        return $this->respondSuccess('Password has been set successfully');
    }
}
