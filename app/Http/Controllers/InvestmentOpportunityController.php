<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\InvestmentOpportunityDataTable;
use App\Models\InvestmentOpportunity;
use App\Models\InvestmentCategory;
use App\Models\OwnerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class InvestmentOpportunityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(InvestmentOpportunityDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.investment-opportunity.index', [
            'columns' => collect($dataTable->columns())->map(function ($column) {
                return $column instanceof \App\Helpers\Column ? $column : new \App\Helpers\Column($column['data'], $column['name'] ?? null, $column['title'] ?? null, $column['searchable'] ?? true, $column['orderable'] ?? true);
            }),
            'filters' => $dataTable->filters(),
            'JsColumns' => $dataTable->columns(),
            'ajaxUrl' => route('investment-opportunity.index'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = InvestmentCategory::where('is_active', true)->get();
        $ownerProfiles = OwnerProfile::with('user')->get();

        return view('pages.investment-opportunity.form', compact('categories', 'ownerProfiles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:investment_categories,id',
            'owner_profile_id' => 'required|exists:owner_profiles,id',
            'risk_level' => 'nullable|string|in:low,medium,high',
            'target_amount' => 'required|numeric|min:0',
            'share_price' => 'required|numeric|min:0',
            'investment_duration' => 'nullable|integer|min:1',
            'expected_profit' => 'nullable|numeric|min:0',
            'expected_net_profit' => 'nullable|numeric|min:0',
            'shipping_fee_per_share' => 'nullable|numeric|min:0',
            'min_investment' => 'required|integer|min:1',
            'max_investment' => 'nullable|integer|min:1',
            'fund_goal' => 'nullable|string|in:growth,stability,income',
            'guarantee' => 'nullable|string|in:' . implode(',', \App\GuaranteeTypeEnum::values()),
            'show' => 'boolean',
            'show_date' => 'nullable|date',
            'offering_start_date' => 'nullable|date',
            'offering_end_date' => 'nullable|date|after:offering_start_date',
            'profit_distribution_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'expected_distribution_date' => 'nullable|date',
        ]);

        // Status and reserved_shares are auto-managed by the system
        $investmentOpportunity = InvestmentOpportunity::create($validated);

        return response()->json([
            'status' => true,
            'msg' => 'Investment opportunity created successfully.',
            'data' => $investmentOpportunity
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(InvestmentOpportunity $investmentOpportunity): View
    {
        $investmentOpportunity->load([
            'category',
            'ownerProfile.user',
            'attachments',
            'guarantees',
            'investments.investorProfile.user'
        ]);

        return view('pages.investment-opportunity.show', compact('investmentOpportunity'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvestmentOpportunity $investmentOpportunity): View
    {
        $categories = InvestmentCategory::where('is_active', true)->get();
        $ownerProfiles = OwnerProfile::with('user')->get();

        return view('pages.investment-opportunity.form', [
            'model' => $investmentOpportunity,
            'categories' => $categories,
            'ownerProfiles' => $ownerProfiles
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvestmentOpportunity $investmentOpportunity): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:investment_categories,id',
            'owner_profile_id' => 'required|exists:owner_profiles,id',
            'risk_level' => 'nullable|string|in:low,medium,high',
            'target_amount' => 'required|numeric|min:0',
            'share_price' => 'required|numeric|min:0',
            'investment_duration' => 'nullable|integer|min:1',
            'expected_profit' => 'nullable|numeric|min:0',
            'expected_net_profit' => 'nullable|numeric|min:0',
            'shipping_fee_per_share' => 'nullable|numeric|min:0',
            // Actual profits must be set together - if one is provided, both are required
            'actual_profit_per_share' => 'nullable|numeric|min:0|required_with:actual_net_profit_per_share',
            'actual_net_profit_per_share' => 'nullable|numeric|min:0|required_with:actual_profit_per_share',
            'min_investment' => 'required|integer|min:1',
            'max_investment' => 'nullable|integer|min:1',
            'fund_goal' => 'nullable|string|in:growth,stability,income',
            'guarantee' => 'nullable|string|in:' . implode(',', \App\GuaranteeTypeEnum::values()),
            'show' => 'boolean',
            'show_date' => 'nullable|date',
            'offering_start_date' => 'nullable|date',
            'offering_end_date' => 'nullable|date|after:offering_start_date',
            'profit_distribution_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'expected_distribution_date' => 'nullable|date',
        ]);

        // Protect actual profits from being changed once they're set
        if (!$investmentOpportunity->canEditActualProfits()) {
            // Remove actual profit fields from validated data if they were already set
            unset($validated['actual_profit_per_share']);
            unset($validated['actual_net_profit_per_share']);
        }

        // Status and reserved_shares are auto-managed by the system
        $investmentOpportunity->update($validated);

        return response()->json([
            'status' => true,
            'msg' => 'Investment opportunity updated successfully.',
            'data' => $investmentOpportunity
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvestmentOpportunity $investmentOpportunity): JsonResponse
    {
        $investmentOpportunity->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Investment opportunity deleted successfully.'
        ]);
    }
}
