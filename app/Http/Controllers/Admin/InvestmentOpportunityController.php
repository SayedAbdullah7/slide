<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestmentOpportunity;
use App\Services\AdminInvestmentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvestmentOpportunityController extends Controller
{
    protected $adminInvestmentService;

    public function __construct(AdminInvestmentService $adminInvestmentService)
    {
        $this->adminInvestmentService = $adminInvestmentService;
    }

    /**
     * Process merchandise delivery for an opportunity
     */
    public function processMerchandiseDelivery(InvestmentOpportunity $opportunity): JsonResponse
    {
        $result = $this->adminInvestmentService->processMerchandiseDelivery($opportunity);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'reload' => true
        ]);
    }

    /**
     * Show record actual profit form
     */
    public function showRecordActualProfit(InvestmentOpportunity $opportunity)
    {
        return view('admin.investment-opportunities.record-actual-profit', compact('opportunity'));
    }

    /**
     * Record actual profit for an opportunity
     */
    public function recordActualProfit(Request $request, InvestmentOpportunity $opportunity): JsonResponse
    {
        // Check if actual profits can be edited (not already set)
        if (!$opportunity->canEditActualProfits()) {
            return response()->json([
                'success' => false,
                'message' => 'Actual profits have already been set for this opportunity and cannot be changed.',
            ], 403);
        }

        $request->validate([
            'actual_profit_per_share' => 'required|numeric|min:0',
            'actual_net_profit_per_share' => 'required|numeric|min:0',
        ]);

        // Update the opportunity with actual profit values
        $opportunity->update([
            'actual_profit_per_share' => $request->actual_profit_per_share,
            'actual_net_profit_per_share' => $request->actual_net_profit_per_share,
        ]);

        // Also record actual profits for all authorize investments
        $result = $this->adminInvestmentService->processActualProfitForAllAuthorizeInvestments(
            $opportunity,
            $request->actual_profit_per_share,
            $request->actual_net_profit_per_share
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'reload' => true
        ]);
    }

    /**
     * Distribute returns for an opportunity
     */
    public function distributeReturns(InvestmentOpportunity $opportunity)
    {
        $result = $this->adminInvestmentService->processReturnsDistribution($opportunity);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'reload' => true
        ]);
    }

    /**
     * Show merchandise status
     */
    public function showMerchandiseStatus(InvestmentOpportunity $opportunity)
    {
        $merchandiseStats = $this->adminInvestmentService->getOpportunityManagementData($opportunity)['merchandise'];

        return view('admin.investment-opportunities.merchandise-status', compact('opportunity', 'merchandiseStats'));
    }

    /**
     * Show returns status
     */
    public function showReturnsStatus(InvestmentOpportunity $opportunity)
    {
        $returnsStats = $this->adminInvestmentService->getOpportunityManagementData($opportunity)['returns'];

        return view('admin.investment-opportunities.returns-status', compact('opportunity', 'returnsStats'));
    }
}
