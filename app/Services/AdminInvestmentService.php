<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use App\Models\InvestorProfile;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * AdminInvestmentService - Service for administrative investment management
 *
 * This service provides comprehensive administrative functionality for managing investments,
 * opportunities, and related processes. It orchestrates other services (MerchandiseService,
 * ReturnsService, DistributionService) to provide high-level administrative operations.
 *
 * ## Service Role:
 * - Provides dashboard statistics and analytics
 * - Orchestrates investment lifecycle management
 * - Handles bulk operations and administrative tasks
 * - Manages opportunity lifecycle and investment processing
 * - Provides performance analytics and reporting
 *
 * ## Administrative Operations:
 * - Dashboard statistics and KPIs
 * - Investment lifecycle management (merchandise → returns → distribution)
 * - Bulk status updates and administrative actions
 * - Performance analytics for investors and opportunities
 * - Financial summaries and reporting
 *
 * ## Key Administrative Processes:
 * 1. **Dashboard Management**: Comprehensive statistics and KPIs
 * 2. **Merchandise Processing**: Mark merchandise as arrived for myself investments
 * 3. **Returns Recording**: Record actual returns for authorize investments
 * 4. **Distribution Processing**: Distribute profits to investor wallets
 * 5. **Performance Analytics**: Track investor and opportunity performance
 * 6. **Bulk Operations**: Mass updates and administrative actions
 *
 * ## Service Dependencies:
 * - MerchandiseService: For merchandise delivery management
 * - ReturnsService: For actual returns recording
 * - DistributionService: For profit distribution
 *
 * ## Key Methods:
 * - getDashboardStatistics(): Get comprehensive dashboard data
 * - getOpportunityManagementData(): Get complete opportunity management data
 * - processMerchandiseDelivery(): Process merchandise delivery for opportunity
 * - processActualProfitPerShareRecording(): Record actual returns for opportunity
 * - processReturnsDistribution(): Distribute returns for opportunity
 * - getInvestmentsRequiringAttention(): Get investments needing admin action
 * - getInvestmentLifecycleStatus(): Get lifecycle status for opportunity
 * - getInvestorPerformanceData(): Get investor performance analytics
 * - getOpportunityPerformanceData(): Get opportunity performance analytics
 * - bulkUpdateInvestmentStatuses(): Bulk update investment statuses
 * - getFinancialSummary(): Get financial summary for date range
 *
 * @package App\Services
 * @author AI Assistant
 * @version 1.0
 */
class AdminInvestmentService
{
    protected $merchandiseService;
    protected $returnsService;
    protected $distributionService;

    public function __construct(
        MerchandiseService $merchandiseService,
        ReturnsService $returnsService,
        DistributionService $distributionService
    ) {
        $this->merchandiseService = $merchandiseService;
        $this->returnsService = $returnsService;
        $this->distributionService = $distributionService;
    }

    /**
     * Get comprehensive dashboard statistics
     * الحصول على إحصائيات شاملة للوحة التحكم
     */
    public function getDashboardStatistics(): array
    {
        $totalOpportunities = InvestmentOpportunity::count();
        $activeOpportunities = InvestmentOpportunity::status('open')->count();
        $completedOpportunities = InvestmentOpportunity::status('completed')->count();

        $totalInvestments = Investment::count();
        $activeInvestments = Investment::active()->count();
        $completedInvestments = Investment::completed()->count();

        $totalInvestmentAmount = Investment::sum('total_investment');
        $totalDistributedAmount = Investment::statusDistributed()->sum('distributed_profit');

        return [
            'opportunities' => [
                'total' => $totalOpportunities,
                'active' => $activeOpportunities,
                'completed' => $completedOpportunities,
            ],
            'investments' => [
                'total' => $totalInvestments,
                'active' => $activeInvestments,
                'completed' => $completedInvestments,
            ],
            'financial' => [
                'total_investment_amount' => $totalInvestmentAmount,
                'total_distributed_amount' => $totalDistributedAmount,
                'pending_distribution' => $totalInvestmentAmount - $totalDistributedAmount,
            ],
        ];
    }

    /**
     * Get opportunity management data
     * الحصول على بيانات إدارة الفرص
     */
    public function getOpportunityManagementData(InvestmentOpportunity $opportunity): array
    {
        $merchandiseStats = $this->merchandiseService->getMerchandiseStatistics($opportunity);
        $returnsStats = $this->returnsService->getOpportunityReturnsStatistics($opportunity);
        $distributionStats = $this->distributionService->getDistributionStatistics($opportunity);

        return [
            'opportunity' => $opportunity,
            'merchandise' => $merchandiseStats,
            'returns' => $returnsStats,
            'distribution' => $distributionStats,
            'investments' => [
                'myself' => $opportunity->investmentsMyself()->get(),
                'authorize' => $opportunity->investmentsAuthorize()->get(),
            ],
        ];
    }

    /**
     * Process merchandise delivery for an opportunity
     * معالجة تسليم البضائع لفرصة معينة
     */
    public function processMerchandiseDelivery(InvestmentOpportunity $opportunity): array
    {
        try {
            $markedCount = $this->merchandiseService->markOpportunityMerchandiseAsArrived($opportunity);

            return [
                'success' => true,
                'message' => "تم وضع علامة وصول البضائع لـ {$markedCount} استثمار",
                'marked_count' => $markedCount,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'فشل في معالجة تسليم البضائع: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process actual profit per share recording for an opportunity
     * معالجة تسجيل الربح الفعلي لكل سهم لفرصة معينة
     */
    public function processActualProfitPerShareRecording(InvestmentOpportunity $opportunity, array $profitData): array
    {
        try {
            $recordedCount = $this->returnsService->recordOpportunityActualProfitPerShare($opportunity, $profitData);

            return [
                'success' => true,
                'message' => "تم تسجيل العوائد الفعلية لـ {$recordedCount} استثمار",
                'recorded_count' => $recordedCount,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'فشل في تسجيل العوائد الفعلية: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process returns distribution for an opportunity
     * معالجة توزيع العوائد لفرصة معينة
     */
    public function processReturnsDistribution(InvestmentOpportunity $opportunity): array
    {
        try {
            $results = $this->distributionService->distributeOpportunityReturns($opportunity);

            return [
                'success' => true,
                'message' => "تم توزيع العوائد لـ {$results['authorize_investments']} استثمار مفوض",
                'results' => $results,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'فشل في توزيع العوائد: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get investments requiring admin attention
     * الحصول على الاستثمارات التي تحتاج انتباه الإدارة
     */
    public function getInvestmentsRequiringAttention(): array
    {
        return [
            'pending_merchandise' => $this->merchandiseService->getPendingMerchandiseDeliveries(),
            'pending_actual_returns' => $this->returnsService->getPendingActualReturns(),
            'ready_for_distribution' => $this->distributionService->getInvestmentsReadyForDistribution(),
        ];
    }

    /**
     * Get investment lifecycle status for an opportunity
     * الحصول على حالة دورة حياة الاستثمار لفرصة معينة
     */
    public function getInvestmentLifecycleStatus(InvestmentOpportunity $opportunity): array
    {
        $myselfInvestments = $opportunity->investmentsMyself()->get();
        $authorizeInvestments = $opportunity->investmentsAuthorize()->get();

        $myselfStatus = [
            'total' => $myselfInvestments->count(),
            'pending_delivery' => $myselfInvestments->where('merchandise_status', 'pending')->count(),
            'arrived' => $myselfInvestments->where('merchandise_status', 'arrived')->count(),
            'distributed' => $myselfInvestments->where('distribution_status', 'distributed')->count(),
        ];

        $authorizeStatus = [
            'total' => $authorizeInvestments->count(),
            'pending_returns' => $authorizeInvestments->whereNull('actual_profit_per_share')->count(),
            'returns_recorded' => $authorizeInvestments->whereNotNull('actual_profit_per_share')->count(),
            'distributed' => $authorizeInvestments->where('distribution_status', 'distributed')->count(),
        ];

        return [
            'myself_investments' => $myselfStatus,
            'authorize_investments' => $authorizeStatus,
            'overall_completion' => $this->calculateOverallCompletion($myselfStatus, $authorizeStatus),
        ];
    }

    /**
     * Calculate overall completion percentage
     * حساب نسبة الإكمال الإجمالية
     */
    protected function calculateOverallCompletion(array $myselfStatus, array $authorizeStatus): float
    {
        $totalInvestments = $myselfStatus['total'] + $authorizeStatus['total'];
        $distributedInvestments = $myselfStatus['distributed'] + $authorizeStatus['distributed'];

        return $totalInvestments > 0 ? round(($distributedInvestments / $totalInvestments) * 100, 2) : 0;
    }

    /**
     * Get investor performance data
     * الحصول على بيانات أداء المستثمرين
     */
    public function getInvestorPerformanceData(int $perPage = 15)
    {
        return InvestorProfile::with(['user', 'investments.opportunity'])
            ->withCount(['investments'])
            ->withSum('investments', 'total_investment')
            ->withSum('investments', 'distributed_profit')
            ->orderBy('investments_sum_amount', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get opportunity performance data
     * الحصول على بيانات أداء الفرص
     */
    public function getOpportunityPerformanceData(int $perPage = 15)
    {
        return InvestmentOpportunity::with(['category', 'ownerProfile.user'])
            ->withCount(['investments'])
            ->withSum('investments', 'total_investment')
            ->withSum('investments', 'distributed_profit')
            ->orderBy('investments_sum_amount', 'desc')
            ->paginate($perPage);
    }

    /**
     * Bulk update investment statuses
     * تحديث حالات الاستثمارات بالجملة
     */
    public function bulkUpdateInvestmentStatuses(array $investmentIds, string $status): array
    {
        try {
            $updatedCount = Investment::whereIn('id', $investmentIds)
                ->update(['status' => $status]);

            return [
                'success' => true,
                'message' => "تم تحديث حالة {$updatedCount} استثمار",
                'updated_count' => $updatedCount,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'فشل في تحديث حالات الاستثمارات: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process actual profit per share for all authorize investments in an opportunity
     * معالجة الربح الفعلي لكل سهم لجميع الاستثمارات المفوضة في فرصة معينة
     */
    public function processActualProfitForAllAuthorizeInvestments(
        InvestmentOpportunity $opportunity,
        float $actualProfitPerShare,
        float $actualNetProfitPerShare
    ): array {
        try {
            $result = $this->returnsService->recordActualProfitForAllAuthorizeInvestments(
                $opportunity,
                $actualProfitPerShare,
                $actualNetProfitPerShare
            );

            return [
                'success' => $result['success'],
                'message' => $result['success']
                    ? "تم تسجيل الربح الفعلي لـ {$result['recorded_count']} استثمار مفوض من أصل {$result['total_authorize_investments']}"
                    : 'لم يتم تسجيل أي استثمار',
                'data' => $result,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'فشل في تسجيل الربح الفعلي للاستثمارات المفوضة: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get financial summary for date range
     * الحصول على ملخص مالي لفترة زمنية
     */
    public function getFinancialSummary($startDate, $endDate): array
    {
        $investments = Investment::whereBetween('created_at', [$startDate, $endDate])->get();
        $opportunities = InvestmentOpportunity::whereBetween('created_at', [$startDate, $endDate])->get();

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'investments' => [
                'count' => $investments->count(),
                'total_amount' => $investments->sum('total_investment'),
                'distributed_amount' => $investments->sum('distributed_profit'),
                'pending_distribution' => $investments->sum('total_investment') - $investments->sum('distributed_profit'),
            ],
            'opportunities' => [
                'count' => $opportunities->count(),
                'total_target_amount' => $opportunities->sum('target_amount'),
                'total_reserved_shares' => $opportunities->sum('reserved_shares'),
            ],
        ];
    }
}
