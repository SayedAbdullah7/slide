<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\FcmToken;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FcmTokenController extends Controller
{
    use ApiResponseTrait;

    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Register FCM token for the authenticated user
     * تسجيل رمز FCM للمستخدم المصادق عليه
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|max:255',
            'device_id' => 'nullable|string|max:255',
            'platform' => 'nullable|string|in:ios,android,web',
            'app_version' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        try {
            $user = Auth::user();

            $fcmToken = $user->addFcmToken(
                $request->token,
                $request->device_id,
                $request->platform,
                $request->app_version
            );

            return $this->respondSuccessWithData('تم تسجيل رمز الإشعارات بنجاح', [
                'token_id' => $fcmToken->id,
                'token' => $fcmToken->token,
                'platform' => $fcmToken->platform,
                'device_id' => $fcmToken->device_id,
                'is_active' => $fcmToken->is_active,
            ]);

        } catch (\Exception $e) {
            return $this->respondBadRequest('حدث خطأ أثناء تسجيل رمز الإشعارات: ' . $e->getMessage());
        }
    }

    /**
     * Update FCM token for the authenticated user
     * تحديث رمز FCM للمستخدم المصادق عليه
     */
    public function update(Request $request, $tokenId)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|max:255',
            'device_id' => 'nullable|string|max:255',
            'platform' => 'nullable|string|in:ios,android,web',
            'app_version' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        try {
            $user = Auth::user();
            $fcmToken = $user->fcmTokens()->findOrFail($tokenId);

            $fcmToken->update([
                'token' => $request->token,
                'device_id' => $request->device_id,
                'platform' => $request->platform,
                'app_version' => $request->app_version,
                'is_active' => true,
                'last_used_at' => now(),
            ]);

            return $this->respondSuccessWithData('تم تحديث رمز الإشعارات بنجاح', $fcmToken);

        } catch (\Exception $e) {
            return $this->respondBadRequest('حدث خطأ أثناء تحديث رمز الإشعارات: ' . $e->getMessage());
        }
    }

    /**
     * Remove FCM token for the authenticated user
     * إزالة رمز FCM للمستخدم المصادق عليه
     */
    public function remove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required_without:device_id|string|max:255',
            'device_id' => 'required_without:token|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        try {
            $user = Auth::user();
            $removed = false;

            if ($request->has('token')) {
                $removed = $user->removeFcmToken($request->token);
            } elseif ($request->has('device_id')) {
                $removed = $user->removeFcmTokenByDevice($request->device_id);
            }

            if ($removed) {
                return $this->respondSuccess('تم إزالة رمز الإشعارات بنجاح');
            } else {
                return $this->respondNotFound('لم يتم العثور على رمز الإشعارات المطلوب');
            }

        } catch (\Exception $e) {
            return $this->respondBadRequest('حدث خطأ أثناء إزالة رمز الإشعارات: ' . $e->getMessage());
        }
    }

    /**
     * Get all FCM tokens for the authenticated user
     * الحصول على جميع رموز FCM للمستخدم المصادق عليه
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $tokens = $user->fcmTokens()
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->respondSuccessWithData('تم جلب رموز الإشعارات بنجاح', $tokens);

        } catch (\Exception $e) {
            return $this->respondBadRequest('حدث خطأ أثناء جلب رموز الإشعارات: ' . $e->getMessage());
        }
    }

    /**
     * Test notification for the authenticated user
     * اختبار الإشعارات للمستخدم المصادق عليه
     */
    public function testNotification(Request $request)
    {
        try {
            $user = Auth::user();

            $result = $this->firebaseService->sendToUser(
                $user,
                'اختبار الإشعارات',
                'هذا اختبار لإشعارات Firebase. إذا وصلتك هذه الرسالة، فالإشعارات تعمل بشكل صحيح!',
                [
                    'type' => 'test',
                    'timestamp' => now()->toISOString(),
                ]
            );

            if ($result['success']) {
                return $this->respondSuccessWithData('تم إرسال إشعار الاختبار بنجاح', $result);
            } else {
                return $this->respondBadRequest('فشل في إرسال إشعار الاختبار: ' . $result['message']);
            }

        } catch (\Exception $e) {
            return $this->respondBadRequest('حدث خطأ أثناء إرسال إشعار الاختبار: ' . $e->getMessage());
        }
    }

    /**
     * Get notification statistics
     * الحصول على إحصائيات الإشعارات
     */
    public function stats()
    {
        try {
            $stats = $this->firebaseService->getNotificationStats();
            return $this->respondSuccessWithData('تم جلب إحصائيات الإشعارات بنجاح', $stats);

        } catch (\Exception $e) {
            return $this->respondBadRequest('حدث خطأ أثناء جلب إحصائيات الإشعارات: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate all FCM tokens for the authenticated user
     * إلغاء تفعيل جميع رموز FCM للمستخدم المصادق عليه
     */
    public function deactivateAll()
    {
        try {
            $user = Auth::user();
            $deactivatedCount = $user->deactivateAllFcmTokens();

            return $this->respondSuccessWithData(
                "تم إلغاء تفعيل {$deactivatedCount} رمز إشعارات",
                ['deactivated_count' => $deactivatedCount]
            );

        } catch (\Exception $e) {
            return $this->respondBadRequest('حدث خطأ أثناء إلغاء تفعيل رموز الإشعارات: ' . $e->getMessage());
        }
    }
}
