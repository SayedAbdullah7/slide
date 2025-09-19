<?php

namespace App\Services;

use App\Repositories\InvestmentOpportunityRepository;
use App\Http\Resources\InvestmentOpportunityResource;
use App\Models\InvestmentOpportunity;
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
                ? $this->getPaginatedData(
                    'my',
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

        return $data;
    }

    protected function getPaginatedData(string $type, int $perPage, int $page, $userId = null)
    {
        $method = 'get' . ucfirst($type);
        $paginator = $userId
            ? $this->repo->{$method}($userId, $perPage, $page)
            : $this->repo->{$method}($perPage, $page);

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
                    'balance' => '0',
                    'total_profits' => '0',
                    'pending_profits' => '0',
                    'error' => 'يجب تسجيل الدخول للوصول إلى المحفظة' // Must be logged in to access wallet
                ];
            }

            $investor = InvestorProfile::where('user_id', $userId)->first();

            if (!$investor) {
                return [
                    'balance' => '0',
                    'total_profits' => '0',
                    'pending_profits' => '0',
                    'error' => 'لم يتم العثور على بروفايل المستثمر' // Investor profile not found
                ];
            }

            $balance = $this->walletService->getWalletBalance($investor);
            $perPage = $params['wallet_per_page'] ?? 10;
            $transactions = $this->walletService->getWalletTransactions($investor, $perPage);

            return [
                'balance' => number_format($balance, 2),
                'total_profits' => '0', // TODO: Calculate from completed investments
                'pending_profits' => '0', // TODO: Calculate from active investments
                'transactions' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ]
            ];
        } catch (Exception $e) {
            return [
                'balance' => '0',
                'total_profits' => '0',
                'pending_profits' => '0',
                'error' => 'فشل في تحميل بيانات المحفظة: ' . $e->getMessage() // Failed to load wallet data
            ];
        }
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
                $data['user_investment_amount'] = $userInvestment ? $userInvestment->amount : 0;
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
}
