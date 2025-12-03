<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvestmentOpportunityResource;
use App\Http\Resources\UserResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\InvestmentOpportunity;
use App\Models\PaymentLog;
use App\Services\InvestmentOpportunityService;
use App\Services\InvestmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestmentOpportunityController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected InvestmentOpportunityService $service,
        protected InvestmentService $investmentService,
        protected \App\Services\PaymentService $paymentService
    ) {}

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
        $user = Auth::user();
        $userId = $user ? $user->id : null;

        $data = $this->service->getHomeData($types, $params, $userId);

        // Add user data like login response
        if ($user) {
            $user->loadMissing('investorProfile', 'ownerProfile');
            $data['user'] = new UserResource($user);
            $data['active_profile_type'] = $user->active_profile_type;
            $data['notifications_enabled'] = (bool) $user->notifications_enabled;
            $data['has_password'] = $user->hasPassword();
        }

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
                // 'investment_opportunity_id' => 'required|exists:investment_opportunities,id',
                'investment_opportunity_id' => 'required',
                'shares' => 'required|integer|min:1',
                'type' => 'required|string|in:myself,authorize',
                'pay_by' => 'nullable|string|in:card,apple_pay,wallet,online',
                // 'payment_method' => 'nullable|string|in:card,apple_pay,wallet',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->respondValidationErrors($e);
        }

        $pay_by = $request->input('pay_by', 'wallet');
        if($pay_by == 'online'){
            $pay_by = 'card';
        }

        // Get investor profile
        $investor = Auth::user()?->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر'); // Investor profile not found
        }

        // Get investment opportunity
        $opportunity = InvestmentOpportunity::findOrFail($data['investment_opportunity_id']);

        try {
            // If online payment, validate first then create payment intention
            if ($pay_by === 'card' || $pay_by === 'apple_pay') {
                return $this->handleOnlinePayment($investor, $opportunity, $data, $pay_by);
            }

            // If wallet payment, process immediately
            return $this->handleWalletPayment($investor, $opportunity, $data);

        } catch (\App\Exceptions\InvestmentException $e) {
            return $this->respondError($e->getMessage(), $e->getStatusCode(), error_code: $e->getErrorCode());
        } catch (\Exception $e) {
            PaymentLog::error('Investment process failed', [
                'opportunity_id' => $data['investment_opportunity_id'],
                'shares' => $data['shares'],
                'exception' => PaymentLog::formatException($e, 2000)
            ], Auth::id(), null, null, 'investment_process_failed');

            return $this->respondBadRequest('حدث خطأ أثناء معالجة الاستثمار: ' . $e->getMessage());
        }
    }
    /**
     * Handle online payment for investment
     */
    private function handleOnlinePayment($investor, $opportunity, $data, $pay_by)
    {
        // Validate investment without processing payment
        $this->investmentService->validateInvestment($investor, $opportunity, $data['shares'], $data['type']);

        // Create payment intention
        $result = $this->paymentService->createInvestmentIntention([
            'opportunity_id' => $data['investment_opportunity_id'],
            'shares' => $data['shares'],
            'investment_type' => $data['type'],
            'pay_by' => $pay_by,
        ], Auth::id(), $pay_by);



        if ($result['success']) {
            return $this->respondCreated([
                'success' => true,
                'message' => 'تم إنشاء نية الدفع بنجاح',
                'result' => $result['data'],
                'payment_required' => true,
            ]);
        }

        return $this->respondBadRequest($result['error'], $result['details'] ?? []);
    }

    /**
     * Handle wallet payment for investment
     */
    private function handleWalletPayment($investor, $opportunity, $data)
    {
        $investment = $this->investmentService->invest($investor, $opportunity, $data['shares'], $data['type']);

        return $this->respondCreated([
            'success' => true,
            'message' => 'تم شراء الأسهم بنجاح',
            'result' => $investment,
        ]);
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
        $page = $request->query('page', 1);

        // Get opportunities with investment details
        $data = $this->service->getHomeData(['my'], [
            'my_per_page' => $perPage,
            'my_page' => $page
        ], $investor->user_id)['my'];

        return $this->respondSuccessWithData('تم جلب استثماراتك بنجاح', $data); // Your investments retrieved successfully
    }

    /**
     * Get detailed investment information for a specific opportunity
     * الحصول على تفاصيل الاستثمار لفرصة محددة
     */
    public function myInvestmentDetails(Request $request, $opportunityId)
    {
        $investor = Auth::user()?->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر'); // Investor profile not found
        }

        try {
            $opportunity = InvestmentOpportunity::with(['investment' => function ($query) use ($investor) {
                $query->where('user_id', $investor->user_id);
            }])
            ->whereHas('investments', function ($query) use ($investor) {
                $query->where('user_id', $investor->user_id);
            })
            ->findOrFail($opportunityId);

            return $this->respondSuccessWithData('تم جلب تفاصيل الاستثمار بنجاح', new InvestmentOpportunityResource($opportunity));
        } catch (\Exception $e) {
            return $this->respondNotFound('لم يتم العثور على الاستثمار المطلوب'); // Investment not found
        }
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
