<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use App\Models\InvestorProfile;
use Illuminate\Support\Facades\DB;
use Exception;

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
        $activeOpportunities = InvestmentOpportunity::where('status', 'open')->count();
        $completedOpportunities = InvestmentOpportunity::where('status', 'completed')->count();

        $totalInvestments = Investment::count();
        $activeInvestments = Investment::where('status', 'active')->count();
        $completedInvestments = Investment::where('status', 'completed')->count();

        $totalInvestmentAmount = Investment::sum('amount');
        $totalDistributedAmount = Investment::where('distribution_status', 'distributed')->sum('distributed_amount');

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
                'myself' => $opportunity->investments()->where('investment_type', 'myself')->get(),
                'authorize' => $opportunity->investments()->where('investment_type', 'authorize')->get(),
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
     * Process actual returns recording for an opportunity
     * معالجة تسجيل العوائد الفعلية لفرصة معينة
     */
    public function processActualReturnsRecording(InvestmentOpportunity $opportunity, array $returnsData): array
    {
        try {
            $recordedCount = $this->returnsService->recordOpportunityActualReturns($opportunity, $returnsData);

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
                'message' => "تم توزيع العوائد لـ {$results['myself_investments']} استثمار بيع بنفسي و {$results['authorize_investments']} استثمار مفوض",
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
        $myselfInvestments = $opportunity->investments()->where('investment_type', 'myself')->get();
        $authorizeInvestments = $opportunity->investments()->where('investment_type', 'authorize')->get();

        $myselfStatus = [
            'total' => $myselfInvestments->count(),
            'pending_delivery' => $myselfInvestments->where('merchandise_status', 'pending')->count(),
            'arrived' => $myselfInvestments->where('merchandise_status', 'arrived')->count(),
            'distributed' => $myselfInvestments->where('distribution_status', 'distributed')->count(),
        ];

        $authorizeStatus = [
            'total' => $authorizeInvestments->count(),
            'pending_returns' => $authorizeInvestments->whereNull('actual_return_amount')->count(),
            'returns_recorded' => $authorizeInvestments->whereNotNull('actual_return_amount')->count(),
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
            ->withSum('investments', 'amount')
            ->withSum('investments', 'distributed_amount')
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
            ->withSum('investments', 'amount')
            ->withSum('investments', 'distributed_amount')
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
                'total_amount' => $investments->sum('amount'),
                'distributed_amount' => $investments->sum('distributed_amount'),
                'pending_distribution' => $investments->sum('amount') - $investments->sum('distributed_amount'),
            ],
            'opportunities' => [
                'count' => $opportunities->count(),
                'total_target_amount' => $opportunities->sum('target_amount'),
                'total_reserved_shares' => $opportunities->sum('reserved_shares'),
            ],
        ];
    }
}
