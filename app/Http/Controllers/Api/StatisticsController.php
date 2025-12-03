<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatisticsResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StatisticsController extends Controller
{
    use ApiResponseTrait;

    protected $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    /**
     * Get statistics dashboard data
     * الحصول على بيانات لوحة تحكم الإحصائيات
     */
    public function getStatistics(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->respondUnAuthorized('يجب تسجيل الدخول أولاً'); // Must be logged in first
        }

        $investor = $user->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر'); // Investor profile not found
        }

        try {
            // Validate period parameter
            $validator = Validator::make($request->all(), [
                'period' => 'sometimes|string|in:week,month,quarter,year,all'
            ]);

            if ($validator->fails()) {
                return $this->respondBadRequest('فترة غير صحيحة', $validator->errors()->toArray());
            }

            $period = $request->get('period', 'month');
            $statisticsData = $this->statisticsService->getStatisticsData($investor, $period);

            return $this->respondSuccessWithData(
                'تم جلب بيانات الإحصائيات بنجاح', // Statistics data retrieved successfully
                new StatisticsResource($statisticsData)
            );
        } catch (\Exception $e) {
            \Log::error('Error getting statistics data', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondBadRequest('حدث خطأ أثناء جلب بيانات الإحصائيات: ' . $e->getMessage()); // Error occurred while retrieving statistics data
        }
    }

    /**
     * Get statistics data for specific period
     * الحصول على بيانات الإحصائيات لفترة محددة
     */
    public function getStatisticsByPeriod(Request $request, string $period)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->respondUnAuthorized('يجب تسجيل الدخول أولاً'); // Must be logged in first
        }

        $investor = $user->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر'); // Investor profile not found
        }

        // Validate period
        $validPeriods = ['week', 'month', 'quarter', 'year', 'all'];
        if (!in_array($period, $validPeriods)) {
            return $this->respondBadRequest('فترة غير صحيحة. الفترات المتاحة: ' . implode(', ', $validPeriods)); // Invalid period
        }

        try {
            $statisticsData = $this->statisticsService->getStatisticsByPeriod($investor, $period);

            return $this->respondSuccessWithData(
                'تم جلب بيانات الإحصائيات للفترة المحددة بنجاح', // Statistics data for specified period retrieved successfully
                new StatisticsResource($statisticsData)
            );
        } catch (\Exception $e) {
            \Log::error('Error getting statistics data by period', [
                'user_id' => $user->id,
                'period' => $period,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondBadRequest('حدث خطأ أثناء جلب بيانات الإحصائيات: ' . $e->getMessage()); // Error occurred while retrieving statistics data
        }
    }

    /**
     * Get investment trends over time
     * الحصول على اتجاهات الاستثمار عبر الوقت
     */
    public function getInvestmentTrends(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->respondUnAuthorized('يجب تسجيل الدخول أولاً'); // Must be logged in first
        }

        $investor = $user->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر'); // Investor profile not found
        }

        try {
            // Validate months parameter
            $validator = Validator::make($request->all(), [
                'months' => 'sometimes|integer|min:1|max:24'
            ]);

            if ($validator->fails()) {
                return $this->respondBadRequest('معاملات غير صحيحة', $validator->errors()->toArray());
            }

            $months = $request->get('months', 12);
            $trends = $this->statisticsService->getInvestmentTrends($investor, $months);

            return $this->respondSuccessWithData(
                'تم جلب اتجاهات الاستثمار بنجاح', // Investment trends retrieved successfully
                $trends
            );
        } catch (\Exception $e) {
            \Log::error('Error getting investment trends', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondBadRequest('حدث خطأ أثناء جلب اتجاهات الاستثمار: ' . $e->getMessage()); // Error occurred while retrieving investment trends
        }
    }

    /**
     * Get quick statistics summary
     * الحصول على ملخص سريع للإحصائيات
     */
    public function getQuickSummary(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->respondUnAuthorized('يجب تسجيل الدخول أولاً'); // Must be logged in first
        }

        $investor = $user->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر'); // Investor profile not found
        }

        try {
            $statisticsData = $this->statisticsService->getStatisticsData($investor, 'month');

            // Extract key metrics for quick summary
            $summary = [
                'total_balance' => $statisticsData['total_balance']['formatted_amount'],
                'total_invested' => $statisticsData['general_vision']['investment']['formatted'],
                'realized_profits' => $statisticsData['general_vision']['realized_profits']['formatted'],
                'profit_percentage' => $statisticsData['general_vision']['profit_percentage'],
                'investment_count' => $statisticsData['general_vision']['investment_count']['value'],
            ];

            return $this->respondSuccessWithData(
                'تم جلب الملخص السريع بنجاح', // Quick summary retrieved successfully
                $summary
            );
        } catch (\Exception $e) {
            \Log::error('Error getting quick summary', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondBadRequest('حدث خطأ أثناء جلب الملخص السريع: ' . $e->getMessage()); // Error occurred while retrieving quick summary
        }
    }

    /**
     * Get statistics comparison between periods
     * الحصول على مقارنة الإحصائيات بين الفترات
     */
    public function getStatisticsComparison(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->respondUnAuthorized('يجب تسجيل الدخول أولاً'); // Must be logged in first
        }

        $investor = $user->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر'); // Investor profile not found
        }

        try {
            $validator = Validator::make($request->all(), [
                'current_period' => 'required|string|in:week,month,quarter,year',
                'previous_period' => 'required|string|in:week,month,quarter,year'
            ]);

            if ($validator->fails()) {
                return $this->respondBadRequest('معاملات غير صحيحة', $validator->errors()->toArray());
            }

            $currentPeriod = $request->get('current_period');
            $previousPeriod = $request->get('previous_period');

            $currentData = $this->statisticsService->getStatisticsData($investor, $currentPeriod);
            $previousData = $this->statisticsService->getStatisticsData($investor, $previousPeriod);

            $comparison = [
                'current_period' => $currentPeriod,
                'previous_period' => $previousPeriod,
                'total_balance' => [
                    'current' => $currentData['total_balance']['amount'],
                    'previous' => $previousData['total_balance']['amount'],
                    'change' => $currentData['total_balance']['amount'] - $previousData['total_balance']['amount'],
                    'change_percentage' => $this->calculatePercentageChange(
                        $previousData['total_balance']['amount'],
                        $currentData['total_balance']['amount']
                    )
                ],
                'total_invested' => [
                    'current' => (float) str_replace(',', '', $currentData['general_vision']['investment']['value']),
                    'previous' => (float) str_replace(',', '', $previousData['general_vision']['investment']['value']),
                    'change' => (float) str_replace(',', '', $currentData['general_vision']['investment']['value']) -
                               (float) str_replace(',', '', $previousData['general_vision']['investment']['value']),
                    'change_percentage' => $this->calculatePercentageChange(
                        (float) str_replace(',', '', $previousData['general_vision']['investment']['value']),
                        (float) str_replace(',', '', $currentData['general_vision']['investment']['value'])
                    )
                ],
                'realized_profits' => [
                    'current' => (float) str_replace(',', '', $currentData['general_vision']['realized_profits']['value']),
                    'previous' => (float) str_replace(',', '', $previousData['general_vision']['realized_profits']['value']),
                    'change' => (float) str_replace(',', '', $currentData['general_vision']['realized_profits']['value']) -
                               (float) str_replace(',', '', $previousData['general_vision']['realized_profits']['value']),
                    'change_percentage' => $this->calculatePercentageChange(
                        (float) str_replace(',', '', $previousData['general_vision']['realized_profits']['value']),
                        (float) str_replace(',', '', $currentData['general_vision']['realized_profits']['value'])
                    )
                ],
            ];

            return $this->respondSuccessWithData(
                'تم جلب مقارنة الإحصائيات بنجاح', // Statistics comparison retrieved successfully
                $comparison
            );
        } catch (\Exception $e) {
            \Log::error('Error getting statistics comparison', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondBadRequest('حدث خطأ أثناء جلب مقارنة الإحصائيات: ' . $e->getMessage()); // Error occurred while retrieving statistics comparison
        }
    }

    /**
     * Clear all statistics cache for the authenticated investor
     * مسح جميع ذاكرة التخزين المؤقت للإحصائيات للمستثمر المصادق عليه
     */
    public function clearCache(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->respondUnAuthorized('يجب تسجيل الدخول أولاً'); // Must be logged in first
        }

        $investor = $user->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر'); // Investor profile not found
        }

        try {
            $cleared = $this->statisticsService->clearInvestorStatisticsCache($investor);

            if ($cleared) {
                return $this->respondSuccess('تم مسح ذاكرة التخزين المؤقت للإحصائيات بنجاح'); // Statistics cache cleared successfully
            } else {
                return $this->respondBadRequest('فشل في مسح ذاكرة التخزين المؤقت'); // Failed to clear cache
            }
        } catch (\Exception $e) {
            \Log::error('Error clearing statistics cache', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondBadRequest('حدث خطأ أثناء مسح ذاكرة التخزين المؤقت: ' . $e->getMessage()); // Error occurred while clearing cache
        }
    }

    /**
     * Clear all statistics cache for all investors (Admin only)
     * مسح جميع ذاكرة التخزين المؤقت للإحصائيات لجميع المستثمرين (للمدير فقط)
     */
    public function clearAllCache(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->respondUnAuthorized('يجب تسجيل الدخول أولاً'); // Must be logged in first
        }

        // Add admin check if needed
        // if (!$user->isAdmin()) {
        //     return $this->respondForbidden('غير مسموح لك بتنفيذ هذا الإجراء'); // Not allowed to perform this action
        // }

        try {
            $cleared = $this->statisticsService->clearAllStatisticsCache();

            if ($cleared) {
                return $this->respondSuccess('تم مسح جميع ذاكرة التخزين المؤقت للإحصائيات بنجاح'); // All statistics cache cleared successfully
            } else {
                return $this->respondBadRequest('فشل في مسح ذاكرة التخزين المؤقت'); // Failed to clear cache
            }
        } catch (\Exception $e) {
            \Log::error('Error clearing all statistics cache', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondBadRequest('حدث خطأ أثناء مسح ذاكرة التخزين المؤقت: ' . $e->getMessage()); // Error occurred while clearing cache
        }
    }

    /**
     * Calculate percentage change between two values
     * حساب النسبة المئوية للتغيير بين قيمتين
     */
    private function calculatePercentageChange(float $oldValue, float $newValue): float
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }

        return round((($newValue - $oldValue) / $oldValue) * 100, 2);
    }
}
