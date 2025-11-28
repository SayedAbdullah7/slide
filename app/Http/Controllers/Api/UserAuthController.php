<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvestorProfileResource;
use App\Http\Resources\OwnerProfileResource;
use App\Http\Resources\PhoneSessionResource;
use App\Http\Resources\UserResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Rules\SaudiPhoneNumber;
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
            'phone' => ['required', 'string', new SaudiPhoneNumber()]
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
        $request->validate([
            'phone' => ['required', 'string', new SaudiPhoneNumber()],
            'type' => 'sometimes|in:login,confirm,register',
            'operation_name' => 'sometimes|string|max:100'
        ]);

        $type = $request->input('type', 'login');
        $operationName = $request->input('operation_name', '');

        $result = $this->otpService->generate($request->phone, $type, $operationName);

        if (!$result['success']) {
            if (isset($result['next_available_at'])) {
                return $this->respondError($result['message'], 422, null, 1, [
                    'next_available_at' => $result['next_available_at'],
                    'seconds_remaining' => $result['seconds_remaining']
                ]);
            }
            return $this->respondError($result['message'], 422);
        }

        $responseData = [];
        if (isset($result['expires_at'])) {
            $responseData['expires_at'] = $result['expires_at'];
        }

        return $this->respondSuccessWithData($result['message'], $responseData);
    }

    // التحقق من OTP → إصدار Session Token

    /**
     * @throws ValidationException
     */
    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', new SaudiPhoneNumber()],
            'code'  => 'required|string',
            'profile' => 'sometimes|in:owner,investor',
        ]);

        $session = $this->otpService->verify($data['phone'], $data['code']);

        if (!$session) {
            return $this->respondError('OTP invalid or expired', 422);
        }

        $user = User::where('phone', $data['phone'])->first();

        if ($user) {
            // Ensure active_profile_type is set
            $user->ensureActiveProfileType();

            // Switch to requested profile if different from current
            if ($request->profile && $user->active_profile_type !== $request->profile) {
                $user = $this->profileFactoryService->switch($user, $request->profile);
            }

            $token = $this->UserAuthService->login($user);


            // Handle FCM token if provided
            if ($request->has('fcm_token')) {
                try {
                    $user->addFcmToken(
                        $request->fcm_token,
                        $request->device_id,
                        $request->platform,
                        $request->app_version
                    );
                } catch (\Exception $e) {
                    // Log error but don't fail registration
                    \Log::warning('Failed to register FCM token during registration', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $user->loadMissing('investorProfile', 'ownerProfile');

            return $this->respondSuccessWithData('OTP verified successfully', [
                'is_new_user' => false,
                'token'       => $token,
                'user'        => new UserResource($user),
                'profiles' => $this->buildProfilesPayload($user),
                'active_profile_type' => $user->active_profile_type,
                'notifications_enabled' => (bool) $user->notifications_enabled,
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

        // Check if user is authenticated (for adding new profile)
        $user = $request->user();

        // If not authenticated, require session_token
        if (!$user) {
            $tokenPhone = $request->session_token;
            $session = PhoneSession::where('token', $tokenPhone)->first();
            if (!$session || $session->isExpired()) {
                return $this->respondError('Session expired', 422);
            }

            // check if user already exists by phone
            $user = User::where('phone', $session->phone)->first();
        }

        if (!$user) {
            // New user registration - validate all required fields
            $data = $request->validate([
                'session_token' => 'required|string|exists:phone_sessions,token',
                'email' => 'nullable|email|unique:users,email',
                'answers'          => 'required|array',
                'profile' => 'required|in:owner,investor',
                // Investor profile fields
                'full_name'      => 'required_if:profile,investor|string',
                'national_id'     => 'required_if:profile,investor|string',
                'birth_date' => 'required_if:profile,investor|date|date_format:Y-m-d',
                // Owner profile fields
                'business_name' => 'required_if:profile,owner',
                'tax_number' => 'required_if:profile,owner|unique:owner_profiles,tax_number',
            ]);
            //validate answers
            $this->surveyService->validateAnswers($data['answers']);
        } else {
            // Existing user adding new profile - session_token optional if authenticated
            $data = $request->validate([
                'session_token' => 'sometimes|exists:phone_sessions,token',
                'profile' => 'required|in:owner,investor',
                // Investor profile fields
                'full_name'      => 'required_if:profile,investor|string',
                'national_id'     => 'required_if:profile,investor|string',
                'birth_date' => 'required_if:profile,investor|date|date_format:Y-m-d',
                // Owner profile fields
                'business_name' => 'required_if:profile,owner|string',
                'tax_number' => 'required_if:profile,owner|unique:owner_profiles,tax_number',
            ]);

            // If user has a pending delete request, cancel it since they're re-registering
            if ($user->hasPendingDeleteRequest()) {
                $user->cancelDeleteRequest();
            }
        }

       // create user if not exists
        if (!$user) {
            $user = $this->UserAuthService->register([
                'phone'             => $session->phone,
                'email'               => $data['email'] ?? null,
                'active_profile_type' => $data['profile'], // Set the profile type during registration
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

        // Ensure active_profile_type is set after profile creation
        $user->ensureActiveProfileType();

        // Handle FCM token if provided
        if ($request->has('fcm_token')) {
            try {
                $user->addFcmToken(
                    $request->fcm_token,
                    $request->device_id,
                    $request->platform,
                    $request->app_version
                );
            } catch (\Exception $e) {
                // Log error but don't fail registration
                \Log::warning('Failed to register FCM token during registration', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $token = $this->UserAuthService->login($user);
        $user->loadMissing('investorProfile', 'ownerProfile');

        return $this->respondSuccessWithData('Registered successfully', [
            'token'  => $token,
            'user'   => new UserResource($user),
            'profiles' => $this->buildProfilesPayload($user),
            'active_profile_type' => $user->active_profile_type,
            'notifications_enabled' => (bool) $user->notifications_enabled,
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

    /**
     * Switch between user profiles (investor/owner)
     *
     * @throws ValidationException
     */
    public function switchProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'profile_type' => 'required|string|in:investor,owner',
        ]);

        $user = $request->user();

        if (!$user) {
            return $this->respondError('User not authenticated', 401);
        }

        try {
            $user = $this->profileFactoryService->switch($user, $data['profile_type']);
            $user->loadMissing('investorProfile', 'ownerProfile');

            return $this->respondSuccessWithData('Profile switched successfully', [
                'user' => new UserResource($user),
                'profiles' => $this->buildProfilesPayload($user),
                'active_profile_type' => $user->active_profile_type,
            ]);
        } catch (ValidationException $e) {
            return $this->respondError($e->getMessage(), 422);
        }
    }

    /**
     * Logout user and revoke all tokens
     */
    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->respondError('User not authenticated', 401);
        }

        // Revoke all tokens for the user
        $user->tokens()->delete();

        // Deactivate all FCM tokens
        $user->deactivateAllFcmTokens();

        return $this->respondSuccess('Logged out successfully');
    }

    /**
     * Request user account deletion
     */
    public function requestDeletion(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->respondError('User not authenticated', 401);
        }

        $data = $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        // Create a new deletion request (don't update existing ones)
        $deletionRequest = $user->requestDeletion($data['reason'] ?? null);

        return $this->respondSuccessWithData('Deletion request submitted successfully', [
            'request_id' => $deletionRequest->id,
            'status' => $deletionRequest->status,
            'requested_at' => $deletionRequest->requested_at,
            'reason' => $deletionRequest->reason
        ]);
    }

    /**
     * Get user's deletion requests
     */
    public function getDeletionRequests(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->respondError('User not authenticated', 401);
        }

        $deletionRequests = $user->deletionRequests()
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->respondSuccessWithData('Deletion requests retrieved successfully', [
            'requests' => $deletionRequests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'status' => $request->status,
                    'reason' => $request->reason,
                    'requested_at' => $request->requested_at,
                    'processed_at' => $request->processed_at,
                    'admin_notes' => $request->admin_notes
                ];
            })
        ]);
    }

    protected function buildProfilesPayload(User $user): array
    {
        $user->loadMissing('investorProfile', 'ownerProfile');

        $investorProfile = $user->investorProfile;
        $ownerProfile = $user->ownerProfile;

        return [
            'investor' => [
                'exists' => (bool) $investorProfile,
                'data' => $investorProfile ? new InvestorProfileResource($investorProfile) : null,
            ],
            'owner' => [
                'exists' => (bool) $ownerProfile,
                'data' => $ownerProfile ? new OwnerProfileResource($ownerProfile) : null,
            ],
        ];
    }
}
