<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvestmentOpportunityRequestRequest;
use App\Http\Resources\InvestmentOpportunityRequestResource;
use App\Http\Resources\InvestmentOpportunityResource;
use App\Http\Resources\UserResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\InvestmentOpportunityRequest;
use App\Models\InvestmentOpportunity;
use App\Models\Investment;
use App\Models\OwnerProfile;
use App\Models\InvestorProfile;
use App\InvestmentOpportunityRequestStatusEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerOpportunityRequestController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get owner profile for authenticated user
     */
    private function getOwnerProfile(Request $request): ?OwnerProfile
    {
        return OwnerProfile::where('user_id', $request->user()->id)->first();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // optional filter by status
        $ownerProfile = $this->getOwnerProfile($request);
        $status = $request->query('status');
        $per_page = $request->query('per_page', 15);
        $page = $request->query('page', 1);

        if (!$ownerProfile) {
            return $this->respondNotFound('لم يتم العثور على ملف المالك');
        }

        $requests = InvestmentOpportunityRequest::with('ownerProfile.user')
            ->where('owner_profile_id', $ownerProfile->id)
            ->when(
                $status,
                function ($query) use ($status) {
                    $query->status($status);
                }
            )
            ->orderBy('created_at', 'desc')
            ->paginate($per_page, ['*'], 'page', $page);

        return $this->respondWithResourceCollection(
            InvestmentOpportunityRequestResource::collection($requests),
            'تم جلب الطلبات بنجاح'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvestmentOpportunityRequestRequest $request): JsonResponse
    {
        $ownerProfile = $this->getOwnerProfile($request);

        if (!$ownerProfile) {
            return $this->respondNotFound('لم يتم العثور على ملف المالك');
        }

        $opportunityRequest = InvestmentOpportunityRequest::create([
            'owner_profile_id' => $ownerProfile->id,
            'company_age' => $request->company_age,
            'commercial_experience' => $request->commercial_experience,
            'net_profit_margins' => $request->net_profit_margins,
            'required_amount' => $request->required_amount,
            'description' => $request->description,
            'guarantee_type' => $request->guarantee_type,
            'status' => InvestmentOpportunityRequestStatusEnum::PENDING->value,
        ]);

        $opportunityRequest->load('ownerProfile.user');

        return $this->respondWithResource(
            new InvestmentOpportunityRequestResource($opportunityRequest),
            'تم تقديم طلب الفرصة الاستثمارية بنجاح',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $ownerProfile = $this->getOwnerProfile($request);

        if (!$ownerProfile) {
            return $this->respondNotFound('لم يتم العثور على ملف المالك');
        }

        $opportunityRequest = InvestmentOpportunityRequest::with('ownerProfile.user')
            ->where('id', $id)
            ->where('owner_profile_id', $ownerProfile->id)
            ->first();

        if (!$opportunityRequest) {
            return $this->respondNotFound('لم يتم العثور على طلب الفرصة الاستثمارية');
        }

        return $this->respondWithResource(
            new InvestmentOpportunityRequestResource($opportunityRequest),
            'تم جلب تفاصيل الطلب بنجاح'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreInvestmentOpportunityRequestRequest $request, int $id): JsonResponse
    {
        $ownerProfile = $this->getOwnerProfile($request);

        if (!$ownerProfile) {
            return $this->respondNotFound('لم يتم العثور على ملف المالك');
        }

        $opportunityRequest = InvestmentOpportunityRequest::where('id', $id)
            ->where('owner_profile_id', $ownerProfile->id)
            ->first();

        if (!$opportunityRequest) {
            return $this->respondNotFound('لم يتم العثور على طلب الفرصة الاستثمارية');
        }

        // Only allow updates for editable requests
        if (!InvestmentOpportunityRequestStatusEnum::isEditable($opportunityRequest->status)) {
            return $this->respondBadRequest('لا يمكن تعديل الطلب في حالته الحالية');
        }

        $opportunityRequest->update([
            'company_age' => $request->company_age,
            'commercial_experience' => $request->commercial_experience,
            'net_profit_margins' => $request->net_profit_margins,
            'required_amount' => $request->required_amount,
            'description' => $request->description,
            'guarantee_type' => $request->guarantee_type,
        ]);

        $opportunityRequest->load('ownerProfile.user');

        return $this->respondWithResource(
            new InvestmentOpportunityRequestResource($opportunityRequest),
            'تم تحديث طلب الفرصة الاستثمارية بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $ownerProfile = $this->getOwnerProfile($request);

        if (!$ownerProfile) {
            return $this->respondNotFound('لم يتم العثور على ملف المالك');
        }

        $opportunityRequest = InvestmentOpportunityRequest::where('id', $id)
            ->where('owner_profile_id', $ownerProfile->id)
            ->first();

        if (!$opportunityRequest) {
            return $this->respondNotFound('لم يتم العثور على طلب الفرصة الاستثمارية');
        }

        // Only allow deletion for deletable requests
        if (!InvestmentOpportunityRequestStatusEnum::isDeletable($opportunityRequest->status)) {
            return $this->respondBadRequest('لا يمكن حذف الطلب في حالته الحالية');
        }

        $opportunityRequest->delete();

        return $this->respondSuccess('تم حذف طلب الفرصة الاستثمارية بنجاح');
    }

    /**
     * Get available guarantee types
     */
    public function getGuaranteeTypes(): JsonResponse
    {
        $guaranteeTypes = collect(\App\GuaranteeTypeEnum::cases())->map(function ($case) {
            return [
                'value' => $case->value,
                'label' => \App\GuaranteeTypeEnum::label($case->value),
                // 'color' => \App\GuaranteeTypeEnum::color($case->value),
            ];
        });

        return $this->respondSuccessWithData('تم جلب أنواع الرهن بنجاح', $guaranteeTypes);
    }

    /**
     * Get available request statuses
     */
    public function getStatuses(): JsonResponse
    {
        $statuses = collect(InvestmentOpportunityRequestStatusEnum::cases())->map(function ($case) {
            return [
                'value' => $case->value,
                'label' => InvestmentOpportunityRequestStatusEnum::label($case->value),
                'color' => InvestmentOpportunityRequestStatusEnum::color($case->value),
            ];
        });

        return $this->respondSuccessWithData('تم جلب حالات الطلبات بنجاح', $statuses);
    }

    /**
     * Get request statistics for the owner
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $ownerProfile = $this->getOwnerProfile($request);

        if (!$ownerProfile) {
            return $this->respondNotFound('لم يتم العثور على ملف المالك');
        }

        $statistics = [
            'total_requests' => InvestmentOpportunityRequest::where('owner_profile_id', $ownerProfile->id)->count(),
            'pending_requests' => InvestmentOpportunityRequest::where('owner_profile_id', $ownerProfile->id)->pending()->count(),
            'approved_requests' => InvestmentOpportunityRequest::where('owner_profile_id', $ownerProfile->id)->approved()->count(),
            'rejected_requests' => InvestmentOpportunityRequest::where('owner_profile_id', $ownerProfile->id)->rejected()->count(),
            'under_review_requests' => InvestmentOpportunityRequest::where('owner_profile_id', $ownerProfile->id)->underReview()->count(),
            'cancelled_requests' => InvestmentOpportunityRequest::where('owner_profile_id', $ownerProfile->id)->cancelled()->count(),
        ];

        return $this->respondSuccessWithData('تم جلب الإحصائيات بنجاح', $statistics);
    }

    /**
     * Get dashboard data for owner (statistics + latest projects)
     */
    public function getDashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $ownerProfile = $this->getOwnerProfile($request);

        if (!$ownerProfile) {
            return $this->respondNotFound('لم يتم العثور على ملف المالك');
        }

        // Get all investment opportunities created by this owner
        $opportunities = InvestmentOpportunity::where('owner_profile_id', $ownerProfile->id);

        // Calculate statistics
        // totalProjects = total investment opportunities + total investment opportunity requests
       // pending projects = total investment opportunity requests

        $totalFunding = $opportunities->sum('target_amount');
        $activeProjects = $opportunities->count();
        $pendingProjects = InvestmentOpportunityRequest::where('owner_profile_id', $ownerProfile->id)->pending()->count();
        // $pendingProjects = $ownerProfile->id;
        $totalProjects = $activeProjects + $pendingProjects;
        $totalInvestors = Investment::whereHas('opportunity', function ($query) use ($ownerProfile) {
            $query->where('owner_profile_id', $ownerProfile->id);
        })->distinct('investor_id')->count();
        // $fulfillmentRate = $opportunities->avg('completion_rate') ?? 0;
        $fulfillmentRate = 50;
        // $pendingProjects = $opportunities->where('status', 'pending')->count();

        // Get latest project (only the last one)
        $latestProject = InvestmentOpportunity::with(['category'])
            ->where('owner_profile_id', $ownerProfile->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $dashboardData = [
            'statistics' => [
                // 'total_funding' => number_format($totalFunding, 2) . ' ريال س',
                'total_funding' => $totalFunding,
                'total_projects' => $totalProjects,
                'active_projects' => $activeProjects,
                'total_investors' => $totalInvestors,
                'fulfillment_rate' => round($fulfillmentRate, 1) . '%',
                'pending_projects' => $pendingProjects
            ],
            'last_investment_opportunity' => $latestProject ? new InvestmentOpportunityResource($latestProject) : null
        ];

        // Add user data like login response
        if ($user) {
            $user->loadMissing('investorProfile', 'ownerProfile');
            $dashboardData['user'] = new UserResource($user);
            $dashboardData['active_profile_type'] = $user->active_profile_type;
            $dashboardData['notifications_enabled'] = (bool) $user->notifications_enabled;
            $dashboardData['has_password'] = $user->hasPassword();
        }

        return $this->respondSuccessWithData('', $dashboardData);
    }
}
