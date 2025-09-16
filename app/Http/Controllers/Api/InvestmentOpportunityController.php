<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvestmentOpportunityResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\InvestmentOpportunity;
use App\Services\InvestmentOpportunityService;
use App\Services\InvestmentService;
use Illuminate\Http\Request;

class InvestmentOpportunityController extends Controller
{
    use ApiResponseTrait;
    protected $service;

    public function __construct(InvestmentOpportunityService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
//        $opportunities = InvestmentOpportunity::activeAndVisible()
        $per_page = request()->query('per_page', 15); // Number of items per page, default is 15
        $page = request()->query('page', 1); // Current page number, default is 1
        $type = request()->query('type', 'available'); // Type of opportunities to fetch available|cameing|my

        $opportunities = InvestmentOpportunity::
            with(['category', 'ownerProfile'])
//            ->activeAndVisible()
//            ->when($type === 'available', function ($query) {
//                $query->where('is_fundable', true);
//            })
            ->orderBy('offering_start_date', 'desc')
            ->paginate($per_page, ['*'], 'page', $page);

        return $this->respondWithResource(InvestmentOpportunityResource::collection($opportunities), 'Investment opportunities retrieved successfully');
        return InvestmentOpportunityResource::collection($opportunities);
    }
    public function home()
    {
        $types = explode(',', request()->query('type', '')); // Type of opportunities to fetch available|cameing|my|closed|wallet
//        $types = explode(',', request()->query('type', 'available,coming,my,closed,wallet')); // Type of opportunities to fetch available|cameing|my|closed|wallet
        $params = request()->all();
        $userId = auth()->check() ? auth()->id() : null;

       $data = $this->service->getHomeData($types, $params, $userId);

        return $this->respondSuccessWithData('Investment opportunities retrieved successfully', $data);
        return InvestmentOpportunityResource::collection($opportunities);
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function invest(Request $request)
    {
        // Get currentProfile from request attributes (set by middleware)
//        $currentProfile = $request->attributes->get('currentProfile');
//        $currentProfile = auth()->user()?->currentProfile;
//
//        // Check current profile validity
//        if (!$currentProfile || $currentProfile->type !== 'investor' || ! $currentProfile->model) {
//            return $this->respondForbidden('Current profile is not an investor.');
//        }

        // Validate request input
        try {
            $data = $request->validate([
                'investment_opportunity_id' => 'required|exists:investment_opportunities,id',
                'shares' => 'required|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e);
        }

//        $investor = $currentProfile->model;
        $investor = auth()->user()?->investorProfile;
        $opportunity = InvestmentOpportunity::find($data['investment_opportunity_id']);

        try {
            /** @var InvestmentService $investmentService */
            $investmentService = app()->make(InvestmentService::class);

            $investment = $investmentService->invest($investor, $opportunity, $data['shares']);

            return $this->respondCreated([
                'success' => true,
                'message' => 'Investment successfully created.',
                'result' => $investment,
            ]);

        } catch (\Exception $e) {
            return $this->respondBadRequest($e->getMessage());
        }

    }
    /**
     * Display the specified resource.
     */
    public function show(InvestmentOpportunity $investmentOpportunity)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvestmentOpportunity $investmentOpportunity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvestmentOpportunity $investmentOpportunity)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvestmentOpportunity $investmentOpportunity)
    {
        //
    }
}
