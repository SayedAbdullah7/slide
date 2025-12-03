<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AppVersionController extends Controller
{
    use ApiResponseTrait;

    /**
     * Check for app updates
     * التحقق من وجود تحديثات للتطبيق
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkUpdate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'version' => 'required|string',
                'os' => 'required|string|in:ios,android',
            ]);

            $currentVersion = $validated['version'];
            $os = strtolower($validated['os']);

            // Get latest version for this OS
            $latestVersion = AppVersion::getLatestVersion($os);

            // Base response structure - always include all keys
            $responseData = [
                'update_available' => false,
                'is_mandatory' => false,
                'current_version' => $currentVersion,
                'latest_version' => null,
                'release_notes' => null,
                'release_notes_ar' => null,
                'released_at' => null,
            ];

            if (!$latestVersion) {
                return $this->respondSuccessWithData('لا توجد تحديثات متاحة', $responseData);
            }

            // Update latest version info
            $responseData['latest_version'] = $latestVersion->version;
            $responseData['release_notes'] = $latestVersion->release_notes;
            $responseData['release_notes_ar'] = $latestVersion->release_notes_ar;
            $responseData['released_at'] = $latestVersion->released_at?->toIso8601String();

            // Compare versions
            $comparison = AppVersion::compareVersions($latestVersion->version, $currentVersion);
            $updateAvailable = $comparison > 0;

            if (!$updateAvailable) {
                // User is on latest version - still include all keys
                return $this->respondSuccessWithData('أنت تستخدم أحدث إصدار', $responseData);
            }

            // Update is available
            $responseData['update_available'] = true;
            $responseData['is_mandatory'] = $latestVersion->is_mandatory;

            return $this->respondSuccessWithData('يوجد تحديث متاح', $responseData);

        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e);
        } catch (\Exception $e) {
            return $this->respondError('حدث خطأ أثناء التحقق من التحديثات', 500);
        }
    }
}
