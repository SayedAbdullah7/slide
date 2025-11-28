<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use App\Models\InvestorProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\DataTables\Custom\InvestmentDataTable;

class InvestmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(InvestmentDataTable $dataTable, Request $request, $opportunityId = null): JsonResponse|View
    {
        // Support multiple ways to pass opportunity_id:
        // 1. Route parameter: /admin/investments/opportunity/{id}
        // 2. Query parameter: /admin/investments?opportunity_id={id}
        if (!$opportunityId) {
            $opportunityId = $request->get('opportunity_id');
        }

        if ($request->ajax()) {
            return $dataTable->handle($opportunityId);
        }

        $opportunity = null;

        if ($opportunityId) {
            $opportunity = InvestmentOpportunity::find($opportunityId);
        }

        // Generate proper AJAX URL based on whether we're filtering by opportunity
        $ajaxUrl = $opportunityId
            ? route('admin.investments.index', ['opportunity_id' => $opportunityId])
            : route('admin.investments.index');

        return view('pages.investment.index', [
            'columns' => collect($dataTable->columns())->map(function ($column) {
                return $column instanceof \App\Helpers\Column ? $column : new \App\Helpers\Column($column['data'], $column['name'] ?? null, $column['title'] ?? null, $column['searchable'] ?? true, $column['orderable'] ?? true);
            }),
            'filters' => $dataTable->filters(),
            'JsColumns' => $dataTable->columns(),
            'ajaxUrl' => $ajaxUrl,
            'opportunityId' => $opportunityId,
            'opportunity' => $opportunity,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $opportunityId = $request->get('opportunity_id');

        $investmentOpportunities = InvestmentOpportunity::all();
        $investorProfiles = InvestorProfile::with('user')->get();

        return view('pages.investment.form', [
            'investmentOpportunities' => $investmentOpportunities,
            'investorProfiles' => $investorProfiles,
            'opportunityId' => $opportunityId,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'opportunity_id' => 'required|exists:investment_opportunities,id',
            'investor_id' => 'required|exists:investor_profiles,id',
            'investment_type' => 'required|in:myself,authorize',
            'status' => 'required|in:pending,active,completed,cancelled',
            'shares' => 'required|integer|min:1',
            'share_price' => 'required|numeric|min:0',
            'investment_date' => 'required|date',
            'shipping_fee_per_share' => 'nullable|numeric|min:0',
            'expected_profit_per_share' => 'nullable|numeric|min:0',
            'expected_net_profit_per_share' => 'nullable|numeric|min:0',
            'merchandise_status' => 'nullable|in:pending,arrived',
            'expected_delivery_date' => 'nullable|date',
            'expected_distribution_date' => 'nullable|date',
            'merchandise_arrived_at' => 'nullable|date',
            'actual_profit_per_share' => 'nullable|numeric|min:0',
            'actual_net_profit_per_share' => 'nullable|numeric|min:0',
            'actual_returns_recorded_at' => 'nullable|date',
            'distribution_status' => 'nullable|in:pending,distributed',
            'distributed_profit' => 'nullable|numeric|min:0',
            'distributed_at' => 'nullable|date',
        ]);

        // Calculate total investment
        $validated['total_investment'] = $validated['shares'] * $validated['share_price'];

        // Calculate total payment required
        if ($validated['investment_type'] === 'myself') {
            $validated['total_payment_required'] = $validated['total_investment'] +
                ($validated['shipping_fee_per_share'] ?? 0) * $validated['shares'];
        } else {
            $validated['total_payment_required'] = $validated['total_investment'];
        }

        $investment = Investment::create($validated);

        return response()->json([
            'status' => true,
            'msg' => 'Investment created successfully!',
            'data' => $investment
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Investment $investment)
    {
        $investment->load(['opportunity', 'investorProfile.user']);

        return view('pages.investment.show', compact('investment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Investment $investment)
    {
        $investment->load(['opportunity', 'investorProfile.user']);
        $investmentOpportunities = InvestmentOpportunity::all();
        $investorProfiles = InvestorProfile::with('user')->get();

        return view('pages.investment.form', [
            'model' => $investment,
            'investment' => $investment,
            'investmentOpportunities' => $investmentOpportunities,
            'investorProfiles' => $investorProfiles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Investment $investment): JsonResponse
    {
        $validated = $request->validate([
            'opportunity_id' => 'required|exists:investment_opportunities,id',
            'investor_id' => 'required|exists:investor_profiles,id',
            'investment_type' => 'required|in:myself,authorize',
            'status' => 'required|in:pending,active,completed,cancelled',
            'shares' => 'required|integer|min:1',
            'share_price' => 'required|numeric|min:0',
            'investment_date' => 'required|date',
            'shipping_fee_per_share' => 'nullable|numeric|min:0',
            'expected_profit_per_share' => 'nullable|numeric|min:0',
            'expected_net_profit_per_share' => 'nullable|numeric|min:0',
            'merchandise_status' => 'nullable|in:pending,arrived',
            'expected_delivery_date' => 'nullable|date',
            'expected_distribution_date' => 'nullable|date',
            'merchandise_arrived_at' => 'nullable|date',
            'actual_profit_per_share' => 'nullable|numeric|min:0',
            'actual_net_profit_per_share' => 'nullable|numeric|min:0',
            'actual_returns_recorded_at' => 'nullable|date',
            'distribution_status' => 'nullable|in:pending,distributed',
            'distributed_profit' => 'nullable|numeric|min:0',
            'distributed_at' => 'nullable|date',
        ]);

        // Calculate total investment
        $validated['total_investment'] = $validated['shares'] * $validated['share_price'];

        // Calculate total payment required
        if ($validated['investment_type'] === 'myself') {
            $validated['total_payment_required'] = $validated['total_investment'] +
                ($validated['shipping_fee_per_share'] ?? 0) * $validated['shares'];
        } else {
            $validated['total_payment_required'] = $validated['total_investment'];
        }

        $investment->update($validated);

        return response()->json([
            'status' => true,
            'msg' => 'Investment updated successfully!',
            'data' => $investment
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Investment $investment): JsonResponse
    {
        $investment->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Investment deleted successfully!'
        ]);
    }

    /**
     * Mark merchandise as arrived for a Myself type investment
     */
    public function markMerchandiseArrived(Investment $investment): JsonResponse
    {
        // Validate that this is a Myself type investment
        if (!$investment->isMyselfType()) {
            return response()->json([
                'success' => false,
                'message' => 'This action is only available for "Myself" type investments.',
            ], 400);
        }

        // Check if merchandise is already arrived
        if (!$investment->isMerchandisePending()) {
            return response()->json([
                'success' => false,
                'message' => 'Merchandise has already been marked as arrived.',
            ], 400);
        }

        // Update the investment
        $investment->update([
            'merchandise_status' => 'arrived',
            'merchandise_arrived_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Merchandise marked as arrived successfully!',
            'data' => $investment->fresh(),
        ]);
    }

    /**
     * Distribute profit for an Authorize type investment
     */
    public function distributeProfit(Investment $investment): JsonResponse
    {
        // Validate that this is an Authorize type investment
        if (!$investment->isAuthorizeType()) {
            return response()->json([
                'success' => false,
                'message' => 'This action is only available for "Authorize" type investments.',
            ], 400);
        }

        // Check if the investment is ready for distribution
        if (!$investment->isReadyForDistribution()) {
            return response()->json([
                'success' => false,
                'message' => 'Investment is not ready for profit distribution. Ensure actual returns are recorded and profit is not already distributed.',
            ], 400);
        }

        // Calculate the distributed profit (total actual net profit)
        $distributedProfit = $investment->getTotalActualNetProfit();

        // Update the investment
        $investment->update([
            'distribution_status' => 'distributed',
            'distributed_profit' => $distributedProfit,
            'distributed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profit distributed successfully!',
            'data' => $investment->fresh(),
            'distributed_amount' => number_format($distributedProfit, 2),
        ]);
    }
}
