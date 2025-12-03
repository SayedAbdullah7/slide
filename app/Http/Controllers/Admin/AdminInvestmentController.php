<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestmentOpportunity;
use App\Services\AdminInvestmentService;
use App\Services\MerchandiseService;
use App\Services\ReturnsService;
use App\Services\DistributionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminInvestmentController extends Controller
{
    protected $adminService;
    protected $merchandiseService;
    protected $returnsService;
    protected $distributionService;

    public function __construct(
        AdminInvestmentService $adminService,
        MerchandiseService $merchandiseService,
        ReturnsService $returnsService,
        DistributionService $distributionService
    ) {
        $this->adminService = $adminService;
        $this->merchandiseService = $merchandiseService;
        $this->returnsService = $returnsService;
        $this->distributionService = $distributionService;
    }

    /**
     * Get dashboard statistics
     * الحصول على إحصائيات لوحة التحكم
     */
    public function dashboard(): JsonResponse
    {
        try {
            $statistics = $this->adminService->getDashboardStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في الحصول على الإحصائيات: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get opportunity management data
     * الحصول على بيانات إدارة الفرصة
     */
    public function opportunityManagement(InvestmentOpportunity $opportunity): JsonResponse
    {
        try {
            $data = $this->adminService->getOpportunityManagementData($opportunity);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في الحصول على بيانات الفرصة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process merchandise delivery
     * معالجة تسليم البضائع
     */
    public function processMerchandiseDelivery(InvestmentOpportunity $opportunity): JsonResponse
    {
        try {
            $result = $this->adminService->processMerchandiseDelivery($opportunity);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في معالجة تسليم البضائع: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process actual profit per share recording
     * معالجة تسجيل الربح الفعلي لكل سهم
     */
    public function processActualProfitPerShare(Request $request, InvestmentOpportunity $opportunity): JsonResponse
    {
        $request->validate([
            'returns_data' => 'required|array',
            'returns_data.*.actual_profit_per_share' => 'required|numeric|min:0',
            'returns_data.*.actual_net_profit_per_share' => 'required|numeric|min:0',
        ]);

        try {
            $result = $this->adminService->processActualProfitPerShareRecording($opportunity, $request->returns_data);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في تسجيل العوائد الفعلية: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process actual profit per share for all authorize investments
     * معالجة الربح الفعلي لكل سهم لجميع الاستثمارات المفوضة
     */
    public function processActualProfitForAllAuthorize(Request $request, InvestmentOpportunity $opportunity): JsonResponse
    {
        $request->validate([
            'actual_profit_per_share' => 'required|numeric|min:0',
            'actual_net_profit_per_share' => 'required|numeric|min:0',
        ]);

        try {
            $result = $this->adminService->processActualProfitForAllAuthorizeInvestments(
                $opportunity,
                $request->actual_profit_per_share,
                $request->actual_net_profit_per_share
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في تسجيل الربح الفعلي للاستثمارات المفوضة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process returns distribution
     * معالجة توزيع العوائد
     */
    public function processReturnsDistribution(InvestmentOpportunity $opportunity): JsonResponse
    {
        try {
            $result = $this->adminService->processReturnsDistribution($opportunity);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في توزيع العوائد: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get investments requiring attention
     * الحصول على الاستثمارات التي تحتاج انتباه
     */
    public function getInvestmentsRequiringAttention(): JsonResponse
    {
        try {
            $data = $this->adminService->getInvestmentsRequiringAttention();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في الحصول على الاستثمارات: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get investment lifecycle status
     * الحصول على حالة دورة حياة الاستثمار
     */
    public function getInvestmentLifecycleStatus(InvestmentOpportunity $opportunity): JsonResponse
    {
        try {
            $data = $this->adminService->getInvestmentLifecycleStatus($opportunity);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في الحصول على حالة دورة الحياة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get investor performance data
     * الحصول على بيانات أداء المستثمرين
     */
    public function getInvestorPerformance(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $data = $this->adminService->getInvestorPerformanceData($perPage);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في الحصول على بيانات الأداء: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get opportunity performance data
     * الحصول على بيانات أداء الفرص
     */
    public function getOpportunityPerformance(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $data = $this->adminService->getOpportunityPerformanceData($perPage);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في الحصول على بيانات أداء الفرص: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get financial summary
     * الحصول على الملخص المالي
     */
    public function getFinancialSummary(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $data = $this->adminService->getFinancialSummary(
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في الحصول على الملخص المالي: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk update investment statuses
     * تحديث حالات الاستثمارات بالجملة
     */
    public function bulkUpdateStatuses(Request $request): JsonResponse
    {
        $request->validate([
            'investment_ids' => 'required|array|min:1',
            'investment_ids.*' => 'exists:investments,id',
            'status' => 'required|string|in:active,completed,cancelled,pending',
        ]);

        try {
            $result = $this->adminService->bulkUpdateInvestmentStatuses(
                $request->investment_ids,
                $request->status
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في تحديث حالات الاستثمارات: ' . $e->getMessage(),
            ], 500);
        }
    }
}
