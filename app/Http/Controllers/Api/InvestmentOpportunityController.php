<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvestmentOpportunityResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\InvestmentOpportunity;
use App\Services\InvestmentOpportunityService;
use App\Services\InvestmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    /**
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
    **/
    public function home()
    {
        $types = explode(',', request()->query('type', '')); // Type of opportunities to fetch available|cameing|my|closed|wallet
//        $types = explode(',', request()->query('type', 'available,coming,my,closed,wallet')); // Type of opportunities to fetch available|cameing|my|closed|wallet
        $params = request()->all();
        $userId = Auth::check() ? Auth::id() : null;

       $data = $this->service->getHomeData($types, $params, $userId);

        return $this->respondSuccessWithData('Investment opportunities retrieved successfully', $data);
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
     * إنشاء استثمار جديد
     */
    public function invest(Request $request)
    {
        // Validate request input
        try {
            $data = $request->validate([
                'investment_opportunity_id' => 'required|exists:investment_opportunities,id',
                'shares' => 'required|integer|min:1',
                'type' => 'required|string|in:myself,authorize',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->respondValidationErrors($e);
        }

        // Get investor profile
        $investor = Auth::user()?->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر'); // Investor profile not found
        }

        // Get investment opportunity
        $opportunity = InvestmentOpportunity::findOrFail($data['investment_opportunity_id']);

        try {
            $investmentService = app()->make(InvestmentService::class);
            $investment = $investmentService->invest($investor, $opportunity, $data['shares'], $data['type']);

            return $this->respondCreated([
                'success' => true,
                'message' => 'تم شراء الأسهم بنجاح', // Investment successfully created
                'result' => $investment,
            ]);
//use ApiResponseTrait in all responses
        } catch (\App\Exceptions\InvestmentException $e) {
            return $this->respondError($e->getMessage(), $e->getStatusCode(), error_code: $e->getErrorCode());
        } catch (\Exception $e) {
            return $this->respondBadRequest('حدث خطأ أثناء معالجة الاستثمار: ' . $e->getMessage()); // Error occurred while processing investment
        }
    }
    /**
     * Display the specified resource.
     * عرض تفاصيل الفرصة الاستثمارية
     */
    public function show(InvestmentOpportunity $investmentOpportunity)
    {
        $userId = Auth::check() ? Auth::id() : null;
        $data = $this->service->getOpportunityDetails($investmentOpportunity, $userId);

        return $this->respondSuccessWithData('تم جلب تفاصيل الفرصة بنجاح', $data); // Opportunity details retrieved successfully
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

    /**
     * Search investment opportunities
     * البحث في الفرص الاستثمارية
     */
    public function search(Request $request)
    {
        $filters = $request->only([
            'category_id', 'risk_level', 'min_amount', 'max_amount',
            'status', 'search', 'per_page'
        ]);

        $perPage = $filters['per_page'] ?? 15;
        $data = $this->service->searchOpportunities($filters, $perPage);

        return $this->respondSuccessWithData('تم البحث في الفرص بنجاح', $data); // Search completed successfully
    }

    /**
     * Get user's investment history
     * الحصول على تاريخ استثمارات المستخدم
     */
    public function myInvestments(Request $request)
    {
        $investor = Auth::user()?->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر'); // Investor profile not found
        }

        $perPage = $request->query('per_page', 15);
        //        $data = $this->service->getUserInvestments($investor->user_id, ['investments_per_page' => $perPage]);
        $data = $this->service->getHomeData(['investments'], ['investments_per_page' => $perPage], $investor->user_id)['investments'];

        return $this->respondSuccessWithData('تم جلب استثماراتك بنجاح', $data); // Your investments retrieved successfully
    }

    /**
     * Get investment statistics for user
     * الحصول على إحصائيات الاستثمار للمستخدم
     */
    public function investmentStats()
    {
        $investor = Auth::user()?->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر'); // Investor profile not found
        }

        try {
            $investmentService = app()->make(InvestmentService::class);
            $stats = $investmentService->getInvestmentStatistics($investor);

            return $this->respondSuccessWithData('تم جلب إحصائيات الاستثمار بنجاح', $stats); // Investment statistics retrieved successfully
        } catch (\Exception $e) {
            return $this->respondBadRequest('حدث خطأ أثناء جلب الإحصائيات: ' . $e->getMessage()); // Error occurred while retrieving statistics
        }
    }
}
