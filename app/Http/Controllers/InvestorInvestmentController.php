<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use App\Services\MerchandiseService;
use App\Services\ReturnsService;
use App\Services\DistributionService;
use App\Support\CurrentProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvestorInvestmentController extends Controller
{
    protected $merchandiseService;
    protected $returnsService;
    protected $distributionService;
    protected $currentProfile;

    public function __construct(
        MerchandiseService $merchandiseService,
        ReturnsService $returnsService,
        DistributionService $distributionService,
        CurrentProfile $currentProfile
    ) {
        $this->merchandiseService = $merchandiseService;
        $this->returnsService = $returnsService;
        $this->distributionService = $distributionService;
        $this->currentProfile = $currentProfile;
    }

    /**
     * Get investor's investments
     * الحصول على استثمارات المستثمر
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $investor = $this->currentProfile->model;

            if (!$investor) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد بروفايل مستثمر نشط',
                ], 400);
            }

            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            $investmentType = $request->get('investment_type');

            $query = $investor->investments()
                ->with(['opportunity.category', 'opportunity.ownerProfile.user']);

            if ($status) {
                $query->where('status', $status);
            }

            if ($investmentType) {
                $query->where('investment_type', $investmentType);
            }

            $investments = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $investments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في الحصول على الاستثمارات: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get investment details
     * الحصول على تفاصيل الاستثمار
     */
    public function show(Investment $investment): JsonResponse
    {
        try {
            $investor = $this->currentProfile->model;

            if (!$investor || $investment->investor_id !== $investor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح بالوصول لهذا الاستثمار',
                ], 403);
            }

            $investment->load(['opportunity.category', 'opportunity.ownerProfile.user']);

            // Get expected returns
            $expectedReturns = $this->returnsService->getExpectedReturns($investment);

            // Get actual returns (if available)
            $actualReturns = $this->returnsService->getActualReturns($investment);

            // Get returns comparison
            $returnsComparison = $this->returnsService->getReturnsComparison($investment);

            // Get merchandise status
            $merchandiseStatus = null;
            if ($investment->investment_type === 'myself') {
                $merchandiseStatus = [
                    'status' => $investment->merchandise_status,
                    'expected_delivery_date' => $investment->expected_delivery_date,
                    'arrived_at' => $investment->merchandise_arrived_at,
                ];
            }

            // Get distribution status
            $distributionStatus = [
                'status' => $investment->distribution_status,
                'distributed_amount' => $investment->distributed_amount,
                'distributed_at' => $investment->distributed_at,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'investment' => $investment,
                    'expected_returns' => $expectedReturns,
                    'actual_returns' => $actualReturns,
                    'returns_comparison' => $returnsComparison,
                    'merchandise_status' => $merchandiseStatus,
                    'distribution_status' => $distributionStatus,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في الحصول على تفاصيل الاستثمار: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get investment statistics for investor
     * الحصول على إحصائيات الاستثمار للمستثمر
     */
    public function statistics(): JsonResponse
    {
        try {
            $investor = $this->currentProfile->model;

            if (!$investor) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد بروفايل مستثمر نشط',
                ], 400);
            }

            $totalInvestments = $investor->investments()->count();
            $activeInvestments = $investor->investments()->where('status', 'active')->count();
            $completedInvestments = $investor->investments()->where('status', 'completed')->count();

            $totalAmount = $investor->investments()->sum('amount');
            $totalDistributed = $investor->investments()->sum('distributed_amount');

            $myselfInvestments = $investor->investments()->where('investment_type', 'myself')->count();
            $authorizeInvestments = $investor->investments()->where('investment_type', 'authorize')->count();

            $arrivedMerchandise = $investor->investments()
                ->where('investment_type', 'myself')
                ->where('merchandise_status', 'arrived')
                ->count();

            $distributedInvestments = $investor->investments()
                ->where('distribution_status', 'distributed')
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_investments' => $totalInvestments,
                    'active_investments' => $activeInvestments,
                    'completed_investments' => $completedInvestments,
                    'total_amount' => $totalAmount,
                    'total_distributed' => $totalDistributed,
                    'pending_distribution' => $totalAmount - $totalDistributed,
                    'investment_types' => [
                        'myself' => $myselfInvestments,
                        'authorize' => $authorizeInvestments,
                    ],
                    'merchandise_status' => [
                        'arrived' => $arrivedMerchandise,
                        'pending' => $myselfInvestments - $arrivedMerchandise,
                    ],
                    'distribution_status' => [
                        'distributed' => $distributedInvestments,
                        'pending' => $totalInvestments - $distributedInvestments,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في الحصول على الإحصائيات: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get distribution history
     * الحصول على تاريخ التوزيع
     */
    public function distributionHistory(Request $request): JsonResponse
    {
        try {
            $investor = $this->currentProfile->model;

            if (!$investor) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد بروفايل مستثمر نشط',
                ], 400);
            }

            $perPage = $request->get('per_page', 15);
            $history = $this->distributionService->getInvestorDistributionHistory($investor, $perPage);

            return response()->json([
                'success' => true,
                'data' => $history,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في الحصول على تاريخ التوزيع: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get investments by status
     * الحصول على الاستثمارات حسب الحالة
     */
    public function getByStatus(string $status): JsonResponse
    {
        try {
            $investor = $this->currentProfile->model;

            if (!$investor) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد بروفايل مستثمر نشط',
                ], 400);
            }

            $validStatuses = ['pending', 'arrived', 'distributed'];
            if (!in_array($status, $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'حالة غير صحيحة',
                ], 400);
            }

            $investments = $investor->investments()
                ->with(['opportunity.category', 'opportunity.ownerProfile.user']);

            if ($status === 'pending') {
                $investments->where(function ($query) {
                    $query->where('merchandise_status', 'pending')
                          ->orWhereNull('actual_return_amount');
                });
            } elseif ($status === 'arrived') {
                $investments->where('merchandise_status', 'arrived');
            } elseif ($status === 'distributed') {
                $investments->where('distribution_status', 'distributed');
            }

            $investments = $investments->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $investments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في الحصول على الاستثمارات: ' . $e->getMessage(),
            ], 500);
        }
    }
}
