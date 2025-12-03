<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\OwnerProfile;
use Illuminate\Http\Request;
use App\Models\InvestmentOpportunity;
use App\Http\Resources\InvestmentOpportunityResource;

class OwnerInvestmentOpportunityController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $ownerProfile = OwnerProfile::where('user_id', $request->user()->id)->first();
        if (!$ownerProfile) {
            return $this->respondNotFound('Owner profile not found');
        }

        $per_page = $request->query('per_page', 15);
        $page = $request->query('page', 1);
        $opportunities = InvestmentOpportunity::where('owner_profile_id', $ownerProfile->id)->paginate($per_page, ['*'], 'page', $page);
        // $opportunities = $opportunities->paginate($per_page, ['*'], 'page', $page);
        return $this->respondWithResourceCollection(
            InvestmentOpportunityResource::collection($opportunities),
            'Investment opportunities retrieved successfully'
        );
    }
}
