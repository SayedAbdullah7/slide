<?php

namespace App\Http\Middleware;

use App\Http\Helpers\ApiResponseHelper;
use App\Models\AppVersion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAppVersion
{
    /**
     * Handle an incoming request.
     * Checks if the app version is valid and if there's a mandatory update
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip version check for the check-update endpoint itself
        if ($request->routeIs('api.app-version.check-update')) {
            return $next($request);
        }

        $version = $request->header('x-version');
        $os = $request->header('x-os');

        // If headers are missing, allow the request (for backward compatibility)
        if (!$version || !$os) {
            return $next($request);
        }

        // Validate OS
        if (!in_array(strtolower($os), ['ios', 'android'])) {
            return ApiResponseHelper::badRequest(
                'Invalid OS. Must be "ios" or "android"',
                ['os' => ['The selected os is invalid. Must be "ios" or "android"']]
            );
        }

        $os = strtolower($os);

        // Check if there's a mandatory update available
        if (AppVersion::hasMandatoryUpdate($version, $os)) {
            $latestVersion = AppVersion::getLatestVersion($os);

            // Always include all keys in response
            $result = [
                'update_available' => true,
                'is_mandatory' => true,
                'current_version' => $version,
                'latest_version' => $latestVersion?->version ?? null,
                'release_notes' => $latestVersion?->release_notes ?? null,
                'release_notes_ar' => $latestVersion?->release_notes_ar ?? null,
                'released_at' => $latestVersion?->released_at?->toIso8601String() ?? null,
            ];

            return ApiResponseHelper::error(
                'يجب تحديث التطبيق إلى الإصدار الأحدث',
                426, // 426 Upgrade Required
                null,
                'MANDATORY_UPDATE_REQUIRED',
                $result
            );
        }

        return $next($request);
    }
}
