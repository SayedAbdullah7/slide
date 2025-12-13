<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvestmentRequest;
use App\Http\Requests\UpdateInvestmentRequest;
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
    public function index(InvestmentDataTable $dataTable, Request $request, $opportunityId = null, $investorId = null): JsonResponse|View
    {
        // Get route name to determine which parameter to use
        $routeName = $request->route()->getName();

        // Get parameters based on route name
        if ($routeName === 'admin.investments.by-investor') {
            // Route: /admin/investments/investor/{investor_id}
            $investorId = $request->route('investor_id') ?? $opportunityId; // Fallback if bound to wrong param
            $opportunityId = null;
        } elseif ($routeName === 'admin.investments.by-opportunity') {
            // Route: /admin/investments/opportunity/{opportunity_id}
            $opportunityId = $request->route('opportunity_id') ?? $opportunityId;
            $investorId = null;
        } else {
            // Regular index route - get from route params or query string
            $investorId = $request->route('investor_id') ?? $request->get('investor_id') ?? $investorId;
            $opportunityId = $request->route('opportunity_id') ?? $request->get('opportunity_id') ?? $opportunityId;
        }

        if ($request->ajax()) {
            return $dataTable->handle($opportunityId, $investorId);
        }

        $opportunity = null;
        $investor = null;

        if ($opportunityId) {
            $opportunity = InvestmentOpportunity::find($opportunityId);
        }

        if ($investorId) {
            $investor = InvestorProfile::with('user')->find($investorId);
        }

        // Generate proper AJAX URL based on whether we're filtering by opportunity or investor
        if ($opportunityId) {
            $ajaxUrl = route('admin.investments.index', ['opportunity_id' => $opportunityId]);
        } elseif ($investorId) {
            $ajaxUrl = route('admin.investments.index', ['investor_id' => $investorId]);
        } else {
            $ajaxUrl = route('admin.investments.index');
        }

        return view('pages.investment.index', [
            'columns' => collect($dataTable->columns())->map(function ($column) {
                return $column instanceof \App\Helpers\Column ? $column : new \App\Helpers\Column($column['data'], $column['name'] ?? null, $column['title'] ?? null, $column['searchable'] ?? true, $column['orderable'] ?? true);
            }),
            'filters' => $dataTable->filters(),
            'JsColumns' => $dataTable->columns(),
            'ajaxUrl' => $ajaxUrl,
            'opportunityId' => $opportunityId,
            'opportunity' => $opportunity,
            'investorId' => $investorId,
            'investor' => $investor,
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
    public function store(StoreInvestmentRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Get opportunity to copy additional data
        $opportunity = InvestmentOpportunity::findOrFail($validated['opportunity_id']);

        // Add data from opportunity
        $validated['share_price'] = $opportunity->share_price;
        $validated['shipping_fee_per_share'] = $opportunity->shipping_fee_per_share ?? 0;
        $validated['expected_profit_per_share'] = $opportunity->expected_profit ?? 0;
        $validated['expected_net_profit_per_share'] = $opportunity->expected_net_profit ?? 0;
        $validated['status'] = 'active';
        $validated['investment_date'] = now();
        $validated['merchandise_status'] = 'pending';
        $validated['distribution_status'] = 'pending';
        $validated['user_id'] = \App\Models\InvestorProfile::findOrFail($validated['investor_id'])->user_id;

        // Calculate total investment
        $validated['total_investment'] = $validated['shares'] * $validated['share_price'];

        // Calculate total payment required
        if ($validated['investment_type'] === 'myself') {
            $validated['total_payment_required'] = $validated['total_investment'] +
                ($validated['shipping_fee_per_share'] ?? 0) * $validated['shares'];

            // Set expected delivery date
            if ($opportunity->investment_duration) {
                $validated['expected_delivery_date'] = now()->addDays($opportunity->investment_duration);
            }
        } else {
            $validated['total_payment_required'] = $validated['total_investment'];

            // Set expected distribution date
            if ($opportunity->expected_distribution_date) {
                $validated['expected_distribution_date'] = $opportunity->expected_distribution_date;
            }
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
    public function update(UpdateInvestmentRequest $request, Investment $investment): JsonResponse
    {
        $validated = $request->validated();

        // Recalculate totals if shares changed
        if (isset($validated['shares'])) {
            $validated['total_investment'] = $validated['shares'] * $investment->share_price;

            // Recalculate total payment required
            if ($investment->investment_type === 'myself') {
                $validated['total_payment_required'] = $validated['total_investment'] +
                    ($investment->shipping_fee_per_share ?? 0) * $validated['shares'];
            } else {
                $validated['total_payment_required'] = $validated['total_investment'];
            }
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
