<?php

namespace App\Services;

use App\Repositories\InvestmentOpportunityRepository;
use App\Http\Resources\InvestmentOpportunityResource;
use App\Http\Resources\InvestmentResource;
use App\Http\Resources\WalletTransactionResource;
use App\Models\InvestmentOpportunity;
use App\Models\Investment;
use App\Models\InvestorProfile;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class InvestmentOpportunityService
{
    protected $repo;
    protected $walletService;

    public function __construct(InvestmentOpportunityRepository $repo, WalletService $walletService)
    {
        $this->repo = $repo;
        $this->walletService = $walletService;
    }

    public function getHomeData(array $types, array $params, $userId = null)
    {
        $data = [];

        if (in_array('available', $types)) {
            $data['available'] = $this->getPaginatedData(
                'available',
                $params['available_per_page'] ?? 7,
                $params['available_page'] ?? 1
            );
        }

        if (in_array('coming', $types)) {
            $data['coming'] = $this->getPaginatedData(
                'coming',
                $params['coming_per_page'] ?? 7,
                $params['coming_page'] ?? 1
            );
        }

        if (in_array('my', $types)) {
            $data['my'] = $userId
                ? $this->getUserInvestmentsPaginated(
                    $params['my_per_page'] ?? 15,
                    $params['my_page'] ?? 1,
                    $userId
                )
                : $this->getEmptyPagination();
        }

        if (in_array('closed', $types)) {
            $data['closed'] = $this->getPaginatedData(
                'closed',
                $params['closed_per_page'] ?? 3,
                $params['closed_page'] ?? 1
            );
        }
        if (in_array('wallet', $types)) {
            $data['wallet'] = $this->getWalletData($userId, $params);
        }

        if (in_array('investments', $types)) {
            $data['investments'] = $this->getUserInvestments($userId, $params);
        }

        if (in_array('reminders', $types)) {
            $data['reminders'] = $this->getPaginatedData(
                'reminders',
                $params['reminders_per_page'] ?? 7,
                $params['reminders_page'] ?? 1,
                $userId
            );
        }

        if (in_array('saved', $types)) {
            $data['saved'] = $this->getPaginatedData(
                'saved',
                $params['saved_per_page'] ?? 7,
                $params['saved_page'] ?? 1,
                $userId
            );
        }

        // saved opportunities that are coming
        if (in_array('coming_saved', $types)) {
            $data['coming_saved'] = $this->getPaginatedData(
                'comingSaved',
                $params['saved_per_page'] ?? 7,
                $params['saved_page'] ?? 1,
                $userId
            );
        }

        // saved opportunities that are available
        if (in_array('available_saved', $types)) {
            $data['available_saved'] = $this->getPaginatedData(
                'availableSaved',
                $params['saved_per_page'] ?? 7,
                $params['saved_page'] ?? 1,
                $userId
            );
        }

        return $data;
    }

    protected function getPaginatedData(string $type, int $perPage, int $page, $userId = null)
    {
        $method = 'get' . ucfirst($type);

        // All methods now have userId as the last parameter
        $paginator = $this->repo->{$method}($perPage, $page, $userId);

        return [
            'data' => InvestmentOpportunityResource::collection($paginator->items()),
            'links' => $this->getPaginationLinks($paginator),
            'meta' => $this->getPaginationMeta($paginator)
        ];
    }

    protected function getPaginationLinks($paginator)
    {
        return [
            'first' => $paginator->url(1),
            'last' => $paginator->url($paginator->lastPage()),
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
        ];
    }

    protected function getPaginationMeta($paginator)
    {
        return [
            'current_page' => $paginator->currentPage(),
            'from' => $paginator->firstItem(),
            'last_page' => $paginator->lastPage(),
            'path' => $paginator->path(),
            'per_page' => $paginator->perPage(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
        ];
    }

    protected function getEmptyPagination()
    {
        return [
            'data' => [],
            'links' => [
                'first' => null,
                'last' => null,
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                'current_page' => 1,
                'from' => null,
                'last_page' => 1,
                'path' => null,
                'per_page' => 15,
                'to' => null,
                'total' => 0,
            ]
        ];
    }

    /**
     * Get wallet data for user
     * الحصول على بيانات المحفظة للمستخدم
     */
    protected function getWalletData($userId, array $params): array
    {
        try {
            if (!$userId) {
                return [
                    'balance' => '0.00',
                    'formatted_balance' => '0.00',
                    'total_profits' => '0.00',
                    'pending_profits' => '0.00',
                    'transactions' => [],
                    'pagination' => $this->getEmptyPagination()['meta'],
                    'error' => 'يجب تسجيل الدخول للوصول إلى المحفظة' // Must be logged in to access wallet
                ];
            }

            $investor = InvestorProfile::where('user_id', $userId)->first();

            if (!$investor) {
                return [
                    'balance' => '0.00',
                    'formatted_balance' => '0.00',
                    'total_profits' => '0.00',
                    'pending_profits' => '0.00',
                    'transactions' => [],
                    'pagination' => $this->getEmptyPagination()['meta'],
                    'error' => 'لم يتم العثور على بروفايل المستثمر' // Investor profile not found
                ];
            }

            // Get wallet balance
            $balance = $this->walletService->getWalletBalance($investor);


            // Get transactions with pagination
            $perPage = $params['wallet_per_page'] ?? 10;
            $page = $params['wallet_page'] ?? 1;
            $transactions = $this->walletService->getWalletTransactions($investor, $perPage);

            // Calculate profits from investments
            $profitData = $this->calculateInvestmentProfits($investor);

            return [
                'balance' => number_format($balance, 2),
                'formatted_balance' => number_format($balance, 2),
                'total_profits' => number_format($profitData['total_profits'], 2),
                'pending_profits' => number_format($profitData['pending_profits'], 2),
                'transactions' => WalletTransactionResource::collection($transactions->items()),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                ],
                'profit_breakdown' => $profitData['breakdown'],
                'wallet_info' => [
                    'profile_type' => 'investor',
                    'profile_id' => $investor->id,
                    'user_id' => $userId,
                    'wallet_exists' => true,
                ]
            ];
        } catch (Exception $e) {
            return [
                'balance' => '0.00',
                'formatted_balance' => '0.00',
                'total_profits' => '0.00',
                'pending_profits' => '0.00',
                'transactions' => [],
                'pagination' => $this->getEmptyPagination()['meta'],
                'error' => 'فشل في تحميل بيانات المحفظة: ' . $e->getMessage() // Failed to load wallet data
            ];
        }
    }

    /**
     * Calculate investment profits for investor
     * حساب أرباح الاستثمارات للمستثمر
     *
     * Uses unified logic from Investment model:
     * - Realized profits: distributed profits from authorize type investments
     * - Pending profits: expected profits from not distributed authorize type investments
     */
    protected function calculateInvestmentProfits($investor): array
    {
        try {
            $investments = $investor->investments()
                ->with(['opportunity'])
                ->get();

            // Use unified methods from Investment model
            $realizedProfits = 0;
            $pendingProfits = 0;
            $breakdown = [
                'completed_investments' => 0,
                'active_investments' => 0,
                'pending_investments' => 0,
                'cancelled_investments' => 0,
                'total_invested' => 0,
                'expected_returns' => 0,
                'distributed_investments' => 0,
                'not_distributed_investments' => 0,
            ];

            foreach ($investments as $investment) {
                $amount = (float) $investment->total_investment;
                $breakdown['total_invested'] += $amount;

                // Use unified profit calculation methods
                $realizedProfits += $investment->getRealizedProfit();
                $pendingProfits += $investment->getPendingProfit();

                // Update breakdown based on investment status
                switch ($investment->status) {
                    case 'completed':
                        $breakdown['completed_investments']++;
                        break;

                    case 'active':
                        $breakdown['active_investments']++;
                        break;

                    case 'pending':
                        $breakdown['pending_investments']++;
                        break;

                    case 'cancelled':
                        $breakdown['cancelled_investments']++;
                        break;
                }

                // Count distributed vs not distributed authorize investments
                if ($investment->isAuthorizeType()) {
                    if ($investment->isDistributed()) {
                        $breakdown['distributed_investments']++;
                    } else {
                        $breakdown['not_distributed_investments']++;
                    }

                    // Add expected returns for authorize investments
                    if (!$investment->isDistributed()) {
                        $breakdown['expected_returns'] += $investment->getExpectedNetProfit();
                    }
                }
            }

            return [
                'total_profits' => $realizedProfits,
                'pending_profits' => $pendingProfits,
                'breakdown' => $breakdown,
            ];
        } catch (Exception $e) {
            return [
                'total_profits' => 0,
                'pending_profits' => 0,
                'breakdown' => [
                    'completed_investments' => 0,
                    'active_investments' => 0,
                    'pending_investments' => 0,
                    'cancelled_investments' => 0,
                    'total_invested' => 0,
                    'expected_returns' => 0,
                    'distributed_investments' => 0,
                    'not_distributed_investments' => 0,
                ],
            ];
        }
    }

    /**
     * Get user's investments with pagination for 'my' section
     * الحصول على استثمارات المستخدم مع التصفح لقسم 'my'
     */
    protected function getUserInvestmentsPaginated(int $perPage, int $page, $userId): array
    {
        if (!$userId) {
            return $this->getEmptyPagination();
        }

        $investor = InvestorProfile::where('user_id', $userId)->first();

        if (!$investor) {
            return $this->getEmptyPagination();
        }

        $investments = $investor->investments()
            ->with(['investmentOpportunity.category', 'investmentOpportunity.ownerProfile'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'my_page', $page);

        return [
            'data' => InvestmentResource::collection($investments->items()),
            'links' => $this->getPaginationLinks($investments),
            'meta' => $this->getPaginationMeta($investments)
        ];
    }

    /**
     * Get user's investments
     * الحصول على استثمارات المستخدم
     */
    protected function getUserInvestments($userId, array $params): array
    {
        if (!$userId) {
            return $this->getEmptyPagination();
        }

        $perPage = $params['investments_per_page'] ?? 15;
        $page = $params['investments_page'] ?? 1;

        $investor = InvestorProfile::where('user_id', $userId)->first();

        if (!$investor) {
            return $this->getEmptyPagination();
        }

        $investments = $investor->investments()
            ->with(['opportunity.category', 'opportunity.ownerProfile'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $investments->items(),
            'links' => $this->getPaginationLinks($investments),
            'meta' => $this->getPaginationMeta($investments)
        ];
    }

    /**
     * Get opportunity details with additional information
     * الحصول على تفاصيل الفرصة مع معلومات إضافية
     */
    public function getOpportunityDetails(InvestmentOpportunity $opportunity, $userId = null): array
    {
        $data = [
            'opportunity' => new InvestmentOpportunityResource($opportunity),
            'is_investable' => $opportunity->isInvestable(),
            'can_invest' => false,
            'user_has_invested' => false,
            'user_investment_amount' => 0,
        ];

        if ($userId) {
            $investor = InvestorProfile::where('user_id', $userId)->first();

            if ($investor) {
                $userInvestment = $investor->investments()
                    ->where('opportunity_id', $opportunity->id)
                    ->first();

                $data['can_invest'] = $opportunity->isInvestable() &&
                                    $investor->user_id !== optional($opportunity->ownerProfile)->user_id;
                $data['user_has_invested'] = $userInvestment !== null;
                $data['user_investment_amount'] = $userInvestment ? $userInvestment->total_investment : 0;
            }
        }

        return $data;
    }

    /**
     * Search opportunities with filters
     * البحث في الفرص مع المرشحات
     */
    public function searchOpportunities(array $filters, int $perPage = 15): array
    {
        $query = InvestmentOpportunity::with(['category', 'ownerProfile']);

        // Apply filters
        if (isset($filters['category_id']) && $filters['category_id']) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['risk_level']) && $filters['risk_level']) {
            $query->where('risk_level', $filters['risk_level']);
        }

        if (isset($filters['min_amount']) && $filters['min_amount']) {
            $query->where('min_investment', '>=', $filters['min_amount']);
        }

        if (isset($filters['max_amount']) && $filters['max_amount']) {
            $query->where('max_investment', '<=', $filters['max_amount']);
        }

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search']) && $filters['search']) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('location', 'like', "%{$searchTerm}%");
            });
        }

        $opportunities = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return [
            'data' => InvestmentOpportunityResource::collection($opportunities->items()),
            'links' => $this->getPaginationLinks($opportunities),
            'meta' => $this->getPaginationMeta($opportunities)
        ];
    }

    /**
     * Update investment opportunity and process reminders if needed
     * تحديث فرصة الاستثمار ومعالجة التذكيرات إذا لزم الأمر
     */
    public function updateOpportunity(InvestmentOpportunity $opportunity, array $data): InvestmentOpportunity
    {
        $oldStatus = $opportunity->status;
        $oldOfferingStartDate = $opportunity->offering_start_date;

        // Update the opportunity
        $opportunity->update($data);

        // Check if opportunity became available and process reminders
        if (($oldStatus !== 'open' && $opportunity->status === 'open') ||
            ($oldOfferingStartDate !== $opportunity->offering_start_date && $opportunity->isInvestable())) {
            $opportunity->processReminders();
        }

        return $opportunity;
    }

    /**
     * Process reminders for opportunities that became available
     * معالجة التذكيرات للفرص التي أصبحت متاحة
     */
    public function processAvailableOpportunityReminders(): int
    {
        $reminderService = app(\App\Services\InvestmentOpportunityReminderService::class);
        return $reminderService->sendReminders();
    }

}
