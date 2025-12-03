<?php

namespace App\Services;

use App\Models\InvestorProfile;
use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class StatisticsService
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get comprehensive statistics data for investor dashboard
     * الحصول على بيانات الإحصائيات الشاملة لوحة تحكم المستثمر
     */
    public function getStatisticsData(InvestorProfile $investor, string $period = 'month'): array
    {
        $cacheKey = "investor_statistics_{$investor->id}_{$period}";

        return Cache::remember($cacheKey, 1800, function () use ($investor, $period) { // 30 minutes = 1800 seconds
            try {
                $dateRange = $this->getDateRange($period);

                return [
                    'total_balance' => $this->getTotalBalance($investor),
                    'general_vision' => $this->getGeneralVision($investor, $dateRange),
                    'portfolio_performance' => $this->getPortfolioPerformance($investor, $dateRange),
                    'time_period' => $period,
                    'date_range' => $dateRange,
                ];
            } catch (\Exception $e) {
                \Log::error('Error getting statistics data', [
                    'investor_id' => $investor->id,
                    'error' => $e->getMessage()
                ]);

                return $this->getEmptyStatisticsData($period);
            }
        });
    }

    /**
     * Get total balance (wallet balance)
     * الحصول على الرصيد الإجمالي
     */
    protected function getTotalBalance(InvestorProfile $investor): array
    {
        // Use WalletService to get balance consistently across all APIs
        $balance = $this->walletService->getWalletBalance($investor);

        return [
            'amount' => $balance,
            'formatted_amount' => number_format($balance, 0) . ' ريال',
            'currency' => 'SAR',
        ];
    }

    /**
     * Get general vision metrics
     * الحصول على مقاييس الرؤية العامة
     */
    protected function getGeneralVision(InvestorProfile $investor, array $dateRange): array
    {
        $investments = $investor->investments()
            ->with(['opportunity'])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->get();

        $totalInvested = $investments->sum('total_investment');
        $realizedProfits = $this->calculateRealizedProfits($investments);
        $expectedProfits = $this->calculateExpectedProfits($investments);
        $distributedProfits = $this->calculateDistributedProfits($investments);
        $investmentCount = $investments->count();
        $purchaseValue = $this->calculatePurchaseValue($investments);
        $distributedInvestments = $investments->where('status', 'completed')->where('investment_type', 'authorize')->count();

        return [
            // الإجمالي المستثمر
            'investment' => [
                'value' => number_format($totalInvested, 0),
                'formatted' => number_format($totalInvested, 0) . ' ريال'
            ],
            // الأرباح المحققة او الفعلية
            'realized_profits' => [
                'value' => number_format($realizedProfits, 0),
                'formatted' => number_format($realizedProfits, 0) . ' ريال'
            ],
            // الأرباح المتوقعة او المنتظرة ولم توزع بعد
            'expected_profits' => [
                'value' => number_format($expectedProfits, 0),
                'formatted' => number_format($expectedProfits, 0) . ' ريال'
            ],
            // عدد الاستثمارات
            'investment_count' => [
                'value' => $investmentCount,
                'formatted' => (string) $investmentCount
            ],
            // قيمة المشتريات (مجموع المبالغ المدفوعة من كل الاستثمارات)
            'purchase_value' => [
                'value' => number_format($purchaseValue, 0),
                'formatted' => number_format($purchaseValue, 0) . ' ريال'
            ],
            // عدد الاستثمارات الموزعة
            'distributed_investments' => [
                'value' => $distributedInvestments,
                'formatted' => (string) $distributedInvestments
            ],
            // الأرباح الموزعة
            'distributed_profits' => [
                'value' => number_format($distributedProfits, 0),
                'formatted' => number_format($distributedProfits, 0) . ' ريال'
            ],
            'profit_percentage' => $this->calculateProfitPercentage($totalInvested, $realizedProfits),
        ];
    }

    /**
     * Get portfolio performance metrics
     * الحصول على مقاييس أداء المحفظة
     */
    protected function getPortfolioPerformance(InvestorProfile $investor, array $dateRange): array
    {
        $investments = $investor->investments()
            ->with(['opportunity'])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->get();

        $totalInvested = $investments->sum('total_investment');
        $realizedProfits = $this->calculateRealizedProfits($investments);
        $distributedProfits = $this->calculateDistributedProfits($investments);
        $profitPercentage = $this->calculateProfitPercentage($totalInvested, $realizedProfits);

        return [
            'realized_profit_percentage' => [
                'value' => $profitPercentage,
                'formatted' => $profitPercentage . '%',
                'progress' => min($profitPercentage, 100) // Cap at 100% for progress bar
            ],
            'net_profits_so_far' => [
                'value' => $realizedProfits,
                'formatted' => number_format($realizedProfits, 0) . ' ريال'
            ],
            'distributed_profits' => [
                'value' => $distributedProfits,
                'formatted' => number_format($distributedProfits, 0) . ' ريال'
            ],
            'total_invested' => [
                'value' => $totalInvested,
                'formatted' => number_format($totalInvested, 0) . ' ريال'
            ],
            'performance_summary' => $this->getPerformanceSummary($investments),
        ];
    }

    /**
     * Calculate realized profits from completed investments
     * حساب الأرباح المحققة من الاستثمارات المكتملة
     * الأرباح المحققة هي الاستمارات المكتملة من النوع authorize فقط
     */
    protected function calculateRealizedProfits($investments): float
    {
        $realizedProfits = 0;

        foreach ($investments as $investment) {
            // Only completed investments of type 'authorize' (تفويض)
            if ($investment->status === 'completed' && $investment->investment_type === 'authorize') {
                // Use the actual net profit from the investment model
                $realizedProfits += $investment->getActualNetProfit();
            }
        }

        return $realizedProfits;
    }

    /**
     * Calculate expected profits from active investments
     * حساب الأرباح المتوقعة من الاستثمارات النشطة
     * الأرباح المنتظرة هي الأرباح المتوقعة للاستثمارات غير المكتملة بعد من النوع تفويض
     */
    protected function calculateExpectedProfits($investments): float
    {
        $expectedProfits = 0;

        foreach ($investments as $investment) {
            // Only incomplete investments of type 'authorize' (تفويض)
            if (in_array($investment->status, ['active', 'pending']) && $investment->investment_type === 'authorize') {
                // Use the expected net profit from the investment model
                $expectedProfits += $investment->getExpectedNetProfit();
            }
        }

        return $expectedProfits;
    }

    /**
     * Calculate purchase value (sum of all paid amounts from all investments)
     * حساب قيمة المشتريات (مجموع المبالغ المدفوعة من كل الاستثمارات)
     */
    protected function calculatePurchaseValue($investments): float
    {
        $purchaseValue = 0;

        foreach ($investments as $investment) {
            // Sum all paid amounts from all investments
            $purchaseValue += $investment->getTotalAmountRequired();
        }

        return $purchaseValue;
    }

    /**
     * Calculate distributed profits from completed authorize investments
     * حساب الأرباح الموزعة من الاستثمارات المكتملة من النوع تفويض
     * الأرباح الموزعة هي الأرباح الفعلية للاستثمارات المكتملة من النوع تفويض
     */
    protected function calculateDistributedProfits($investments): float
    {
        $distributedProfits = 0;

        foreach ($investments as $investment) {
            // Only completed investments of type 'authorize' (تفويض) that are distributed
            if ($investment->status === 'completed' &&
                $investment->investment_type === 'authorize' &&
                $investment->isDistributed()) {
                $distributedProfits += $investment->distributed_profit ?? 0;
            }
        }

        return $distributedProfits;
    }

    /**
     * Calculate profit percentage
     * حساب نسبة الربح
     */
    protected function calculateProfitPercentage(float $totalInvested, float $realizedProfits): float
    {
        if ($totalInvested <= 0) {
            return 0;
        }

        return round(($realizedProfits / $totalInvested) * 100, 2);
    }

    /**
     * Get performance summary
     * الحصول على ملخص الأداء
     */
    protected function getPerformanceSummary($investments): array
    {
        $summary = [
            'total_investments' => $investments->count(),
            'active_investments' => $investments->where('status', 'active')->count(),
            'completed_investments' => $investments->where('status', 'completed')->count(),
            'pending_investments' => $investments->where('status', 'pending')->count(),
            'cancelled_investments' => $investments->where('status', 'cancelled')->count(),
        ];

        return $summary;
    }

    /**
     * Get date range based on period
     * الحصول على نطاق التاريخ بناءً على الفترة
     */
    protected function getDateRange(string $period): array
    {
        $now = Carbon::now();

        return match($period) {
            'week' => [
                'start' => $now->copy()->subWeek(),
                'end' => $now,
                'label' => 'أسبوع'
            ],
            'month' => [
                'start' => $now->copy()->subMonth(),
                'end' => $now,
                'label' => 'شهر'
            ],
            'quarter' => [
                'start' => $now->copy()->subQuarter(),
                'end' => $now,
                'label' => 'ربع سنة'
            ],
            'year' => [
                'start' => $now->copy()->subYear(),
                'end' => $now,
                'label' => 'سنة'
            ],
            'all' => [
                'start' => Carbon::createFromDate(1900, 1, 1),
                'end' => $now,
                'label' => 'الكل'
            ],
            default => [
                'start' => $now->copy()->subMonth(),
                'end' => $now,
                'label' => 'شهر'
            ]
        };
    }

    /**
     * Get empty statistics data structure
     * الحصول على هيكل بيانات الإحصائيات الفارغ
     */
    protected function getEmptyStatisticsData(string $period): array
    {
        $dateRange = $this->getDateRange($period);

        return [
            'total_balance' => [
                'amount' => 0,
                'formatted_amount' => '0 ريال',
                'currency' => 'SAR',
            ],
            'general_vision' => [
                'investment' => ['value' => '0', 'formatted' => '0 ريال'],
                'realized_profits' => ['value' => '0', 'formatted' => '0 ريال'],
                'expected_profits' => ['value' => '0', 'formatted' => '0 ريال'],
                'investment_count' => ['value' => '0', 'formatted' => '0'],
                'purchase_value' => ['value' => '0', 'formatted' => '0 ريال'],
                'distributed_investments' => ['value' => '0', 'formatted' => '0'],
                'distributed_profits' => ['value' => '0', 'formatted' => '0 ريال'],
                'profit_percentage' => 0,
            ],
            'portfolio_performance' => [
                'realized_profit_percentage' => [
                    'value' => 0,
                    'formatted' => '0%',
                    'progress' => 0
                ],
                'net_profits_so_far' => [
                    'value' => 0,
                    'formatted' => '0 ريال'
                ],
                'distributed_profits' => [
                    'value' => 0,
                    'formatted' => '0 ريال'
                ],
                'total_invested' => [
                    'value' => 0,
                    'formatted' => '0 ريال'
                ],
                'performance_summary' => [
                    'total_investments' => 0,
                    'active_investments' => 0,
                    'completed_investments' => 0,
                    'pending_investments' => 0,
                    'cancelled_investments' => 0,
                ],
            ],
            'time_period' => $period,
            'date_range' => $dateRange,
        ];
    }

    /**
     * Get statistics data for specific time period
     * الحصول على بيانات الإحصائيات لفترة زمنية محددة
     */
    public function getStatisticsByPeriod(InvestorProfile $investor, string $period): array
    {
        return $this->getStatisticsData($investor, $period);
    }

    /**
     * Get investment trends over time
     * الحصول على اتجاهات الاستثمار عبر الوقت
     */
    public function getInvestmentTrends(InvestorProfile $investor, int $months = 12): array
    {
        $cacheKey = "investor_trends_{$investor->id}_{$months}";

        return Cache::remember($cacheKey, 1800, function () use ($investor, $months) { // 30 minutes
            $trends = [];
            $now = Carbon::now();

            for ($i = $months - 1; $i >= 0; $i--) {
                $monthStart = $now->copy()->subMonths($i)->startOfMonth();
                $monthEnd = $now->copy()->subMonths($i)->endOfMonth();

                $monthInvestments = $investor->investments()
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->get();

                $trends[] = [
                    'month' => $monthStart->format('Y-m'),
                    'month_name' => $monthStart->locale('ar')->monthName,
                    'total_invested' => $monthInvestments->sum('total_investment'),
                    'investment_count' => $monthInvestments->count(),
                    'realized_profits' => $this->calculateRealizedProfits($monthInvestments),
                    'distributed_profits' => $this->calculateDistributedProfits($monthInvestments),
                ];
            }

            return $trends;
        });
    }

    /**
     * Clear all statistics cache for an investor
     * مسح جميع ذاكرة التخزين المؤقت للإحصائيات لمستثمر
     */
    public function clearInvestorStatisticsCache(InvestorProfile $investor): bool
    {
        try {
            $periods = ['week', 'month', 'quarter', 'year', 'all'];
            $months = [6, 12, 18, 24];

            $cleared = 0;

            // Clear main statistics cache for all periods
            foreach ($periods as $period) {
                $cacheKey = "investor_statistics_{$investor->id}_{$period}";
                if (Cache::forget($cacheKey)) {
                    $cleared++;
                }
            }

            // Clear trends cache for different month ranges
            foreach ($months as $month) {
                $cacheKey = "investor_trends_{$investor->id}_{$month}";
                if (Cache::forget($cacheKey)) {
                    $cleared++;
                }
            }

            // Clear comparison cache (if exists)
            $comparisonCacheKey = "investor_comparison_{$investor->id}";
            if (Cache::forget($comparisonCacheKey)) {
                $cleared++;
            }

            \Log::info('Statistics cache cleared for investor', [
                'investor_id' => $investor->id,
                'cache_keys_cleared' => $cleared
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error clearing statistics cache for investor', [
                'investor_id' => $investor->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Clear all statistics cache for all investors
     * مسح جميع ذاكرة التخزين المؤقت للإحصائيات لجميع المستثمرين
     */
    public function clearAllStatisticsCache(): bool
    {
        try {
            // Clear all cache keys that start with investor_statistics_ or investor_trends_
            $pattern = 'investor_statistics_*';
            $trendsPattern = 'investor_trends_*';
            $comparisonPattern = 'investor_comparison_*';

            $cleared = 0;

            // Note: This is a simplified approach. In production, you might want to use
            // Redis SCAN or similar for more efficient pattern-based cache clearing
            $cacheKeys = Cache::getRedis()->keys($pattern);
            $trendsKeys = Cache::getRedis()->keys($trendsPattern);
            $comparisonKeys = Cache::getRedis()->keys($comparisonPattern);

            $allKeys = array_merge($cacheKeys, $trendsKeys, $comparisonKeys);

            foreach ($allKeys as $key) {
                if (Cache::forget($key)) {
                    $cleared++;
                }
            }

            \Log::info('All statistics cache cleared', [
                'cache_keys_cleared' => $cleared
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error clearing all statistics cache', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
