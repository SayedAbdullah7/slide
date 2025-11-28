<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InvestmentOpportunity;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use App\Models\BankTransferRequest;
use App\Models\UserDeletionRequest;
use App\Models\ContactMessage;
use App\Models\InvestmentCategory;
use App\Models\Bank;
use App\Models\InvestorProfile;
use App\Models\OwnerProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $actualProfitKey = 'actual_net_profit_per_share';
    protected $expectedProfitKey = 'expected_net_profit_per_share';
    protected $shortInvestmentsKey = 'short_investments';

    /**
     * Get base investments query (excludes cancelled)
     */
    protected function getInvestmentsQuery()
    {
        return Investment::where('status', '!=', 'cancelled');
    }

    /**
     * Get investments by date range (uses investment_date or created_at as fallback)
     */
    protected function getInvestmentsByDateRange($startDate, $endDate)
    {
        return $this->getInvestmentsQuery()
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('investment_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->whereNull('investment_date')
                            ->whereBetween('created_at', [$startDate, $endDate]);
                      });
            })
            ->get();
    }

    /**
     * Calculate expected profit from investments collection
     */
    protected function calculateExpectedProfit($investments): float
    {
        return round($investments->sum(function($inv) {
            return ($inv->{$this->expectedProfitKey} ?? 0) * ($inv->shares ?? 0);
        }), 2);
    }

    /**
     * Calculate actual profit from investments collection
     */
    protected function calculateActualProfit($investments): float
    {
        return round($investments->sum(function($inv) {
            return ($inv->{$this->actualProfitKey} ?? 0) * ($inv->shares ?? 0);
        }), 2);
    }

    /**
     * Calculate short investments count (distributed)
     */
    protected function calculateShortInvestments($investments): int
    {
        return $investments->where('distribution_status', 'distributed')->count();
    }
    /**
     * Calculate investment amount from investments collection
     */
    protected function calculateInvestmentAmount($investments): float
    {
        return round($investments->sum(function($inv) {
            return $inv->total_investment;
        }), 2);
    }

    /**
     * Calculate performance metrics for a date range
     * If dates are null, calculates for all investments
     */
    protected function calculatePerformanceMetrics($startDate = null, $endDate = null): array
    {
        if ($startDate === null || $endDate === null) {
            // Get all investments (no date filter)
            $investments = $this->getInvestmentsQuery()->get();
        } else {
            $investments = $this->getInvestmentsByDateRange($startDate, $endDate);
        }

        return [
            'expected_profit' => $this->calculateExpectedProfit($investments),
            'actual_profit' => $this->calculateActualProfit($investments),
            'short_investments' => $this->calculateShortInvestments($investments),
        ];
    }

    /**
     * Group performance data by period (daily or monthly)
     */
    protected function groupPerformanceData($startDate, $endDate): array
    {
        $daysDiff = $startDate->diffInDays($endDate);
        $useDaily = $daysDiff <= 90;

        $performanceMonths = [];
        $expectedProfitData = [];
        $actualProfitData = [];
        $shortInvestmentsData = [];
        $investmentAmountData = [];

        if ($useDaily) {
            // Daily grouping
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dayStart = $currentDate->copy()->startOfDay();
                $dayEnd = $currentDate->copy()->endOfDay();

                $performanceMonths[] = $currentDate->format('M d');
                $investments = $this->getInvestmentsByDateRange($dayStart, $dayEnd);

                $expectedProfitData[] = $this->calculateExpectedProfit($investments);
                $actualProfitData[] = $this->calculateActualProfit($investments);
                $shortInvestmentsData[] = $this->calculateShortInvestments($investments);
                $investmentAmountData[] = $this->calculateInvestmentAmount($investments);
                $currentDate->addDay();
            }
        } else {
            // Monthly grouping
            $currentDate = $startDate->copy()->startOfMonth();
            while ($currentDate <= $endDate) {
                $monthStart = $currentDate->copy()->startOfMonth();
                $monthEnd = min($currentDate->copy()->endOfMonth(), $endDate->copy());

                $performanceMonths[] = $currentDate->format('M Y');
                $investments = $this->getInvestmentsByDateRange($monthStart, $monthEnd);

                $expectedProfitData[] = $this->calculateExpectedProfit($investments);
                $actualProfitData[] = $this->calculateActualProfit($investments);
                $shortInvestmentsData[] = $this->calculateShortInvestments($investments);
                $investmentAmountData[] = $this->calculateInvestmentAmount($investments);
                $currentDate->addMonth();
            }
        }

        return [
            'months' => $performanceMonths,
            'expected_profit' => $expectedProfitData,
            'actual_profit' => $actualProfitData,
            'short_investments' => $shortInvestmentsData,
            'investment_amount' => $investmentAmountData,
        ];
    }

    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Users Statistics
        $usersStats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'registered' => User::where('is_registered', true)->count(),
            'with_investor_profile' => User::whereHas('investorProfile')->count(),
            'with_owner_profile' => User::whereHas('ownerProfile')->count(),
            'new_today' => User::whereDate('created_at', $today)->count(),
            'new_this_week' => User::where('created_at', '>=', $thisWeek)->count(),
            'new_this_month' => User::where('created_at', '>=', $thisMonth)->count(),
            'new_last_month' => User::whereBetween('created_at', [$lastMonth, $thisMonth])->count(),
        ];

        // Investment Opportunities Statistics
        $opportunitiesStats = [
            'total' => InvestmentOpportunity::count(),
            'active' => InvestmentOpportunity::where('status', 'active')->count(),
            'pending' => InvestmentOpportunity::where('status', 'pending')->count(),
            'completed' => InvestmentOpportunity::where('status', 'completed')->count(),
            'visible' => InvestmentOpportunity::where('show', true)->count(),
            'new_this_month' => InvestmentOpportunity::where('created_at', '>=', $thisMonth)->count(),
        ];

        // Investments Statistics
        $investmentsStats = [
            'total' => Investment::count(),
            'total_amount' => Investment::sum('total_investment') ?? 0,
            'active' => Investment::where('status', 'active')->count(),
            'pending' => Investment::where('status', 'pending')->count(),
            'completed' => Investment::where('status', 'completed')->count(),
            'new_this_month' => Investment::where('created_at', '>=', $thisMonth)->count(),
            'pending_merchandise' => Investment::where('merchandise_status', 'pending')->count(),
            'pending_distribution' => Investment::where('distribution_status', 'pending')->count(),
        ];

        // Investment Performance Data for Chart (Last 12 months)
        $performanceStartDate = Carbon::now()->subMonths(11)->startOfMonth();
        $performanceEndDate = Carbon::now()->endOfMonth();

        $groupedData = $this->groupPerformanceData($performanceStartDate, $performanceEndDate);
        // Calculate totals for all investments (no date filter)
        $totals = $this->calculatePerformanceMetrics();

        $investmentPerformance = array_merge($groupedData, [
            'total_expected_profit' => $totals['expected_profit'],
            'total_actual_profit' => $totals['actual_profit'],
            'total_short_investments' => $totals['short_investments'],
        ]);

        // Transactions Statistics
        $transactionsStats = [
            'total' => Transaction::count(),
            'deposits' => Transaction::where('type', 'deposit')->count(),
            'withdrawals' => Transaction::where('type', 'withdraw')->count(),
            'confirmed' => Transaction::where('confirmed', true)->count(),
            'pending' => Transaction::where('confirmed', false)->count(),
            'total_deposits_amount' => Transaction::where('type', 'deposit')
                ->where('confirmed', true)
                ->get()
                ->sum(function($t) { return floatval($t->amount) / 100; }) ?? 0,
            'total_withdrawals_amount' => Transaction::where('type', 'withdraw')
                ->where('confirmed', true)
                ->get()
                ->sum(function($t) { return floatval($t->amount) / 100; }) ?? 0,
            'today' => Transaction::whereDate('created_at', $today)->count(),
            'this_week' => Transaction::where('created_at', '>=', $thisWeek)->count(),
            'this_month' => Transaction::where('created_at', '>=', $thisMonth)->count(),
        ];

        // Withdrawals Statistics
        $withdrawalsStats = [
            'total' => WithdrawalRequest::count(),
            'pending' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PENDING)->count(),
            'processing' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PROCESSING)->count(),
            'completed' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_COMPLETED)->count(),
            'rejected' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_REJECTED)->count(),
            'total_pending_amount' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PENDING)->sum('amount') ?? 0,
            'total_completed_amount' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_COMPLETED)->sum('amount') ?? 0,
        ];

        // Bank Transfers Statistics
        $bankTransfersStats = [
            'total' => BankTransferRequest::count(),
            'pending' => BankTransferRequest::where('status', BankTransferRequest::STATUS_PENDING)->count(),
            'approved' => BankTransferRequest::where('status', BankTransferRequest::STATUS_APPROVED)->count(),
            'rejected' => BankTransferRequest::where('status', BankTransferRequest::STATUS_REJECTED)->count(),
            'total_pending_amount' => BankTransferRequest::where('status', BankTransferRequest::STATUS_PENDING)->sum('amount') ?? 0,
            'total_approved_amount' => BankTransferRequest::where('status', BankTransferRequest::STATUS_APPROVED)->sum('amount') ?? 0,
        ];

        // User Deletion Requests Statistics
        $deletionRequestsStats = [
            'total' => UserDeletionRequest::count(),
            'pending' => UserDeletionRequest::where('status', UserDeletionRequest::STATUS_PENDING)->count(),
            'approved' => UserDeletionRequest::where('status', UserDeletionRequest::STATUS_APPROVED)->count(),
            'rejected' => UserDeletionRequest::where('status', UserDeletionRequest::STATUS_REJECTED)->count(),
            'cancelled' => UserDeletionRequest::where('status', UserDeletionRequest::STATUS_CANCELLED)->count(),
        ];

        // Contact Messages Statistics
        $contactMessagesStats = [
            'total' => ContactMessage::count(),
            'pending' => ContactMessage::where('status', ContactMessage::STATUS_PENDING)->count(),
            'in_progress' => ContactMessage::where('status', ContactMessage::STATUS_IN_PROGRESS)->count(),
            'resolved' => ContactMessage::where('status', ContactMessage::STATUS_RESOLVED)->count(),
            'closed' => ContactMessage::where('status', ContactMessage::STATUS_CLOSED)->count(),
            'new_this_week' => ContactMessage::where('created_at', '>=', $thisWeek)->count(),
        ];

        // Investment Categories Statistics
        $categoriesStats = [
            'total' => InvestmentCategory::count(),
            'active' => InvestmentCategory::where('is_active', true)->count(),
            'inactive' => InvestmentCategory::where('is_active', false)->count(),
        ];

        // Banks Statistics
        $banksStats = [
            'total' => Bank::count(),
            'active' => Bank::where('is_active', true)->count(),
            'inactive' => Bank::where('is_active', false)->count(),
        ];

        // Recent Activity
        $recentUsers = User::with(['investorProfile', 'ownerProfile'])
            ->latest()
            ->take(5)
            ->get();
        $recentOpportunities = InvestmentOpportunity::latest()->take(5)->get();
        $recentInvestments = Investment::with(['user.investorProfile', 'user.ownerProfile', 'investmentOpportunity'])
            ->latest()
            ->take(5)
            ->get();
        $recentTransactions = Transaction::with('payable')
            ->latest()
            ->take(5)
            ->get();

        // Pending Actions
        $pendingActions = [
            'pending_withdrawals' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PENDING)->count(),
            'pending_bank_transfers' => BankTransferRequest::where('status', BankTransferRequest::STATUS_PENDING)->count(),
            'pending_deletion_requests' => UserDeletionRequest::where('status', UserDeletionRequest::STATUS_PENDING)->count(),
            'pending_contact_messages' => ContactMessage::where('status', ContactMessage::STATUS_PENDING)->count(),
            'pending_transactions' => Transaction::where('confirmed', false)->count(),
        ];

        return view('pages.dashboard.index', compact(
            'usersStats',
            'opportunitiesStats',
            'investmentsStats',
            'transactionsStats',
            'withdrawalsStats',
            'bankTransfersStats',
            'deletionRequestsStats',
            'contactMessagesStats',
            'categoriesStats',
            'banksStats',
            'recentUsers',
            'recentOpportunities',
            'recentInvestments',
            'recentTransactions',
            'pendingActions',
            'investmentPerformance'
        ));
    }

    /**
     * Display the new admin dashboard2 focused on Opportunities, Investments, and Financials.
     */
    public function dashboard2(): View
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // ============================================
        // OPPORTUNITIES (الفرص) - Investment Opportunities
        // ============================================
        $opportunitiesStats = [
            'total' => InvestmentOpportunity::count(),
            'open' => InvestmentOpportunity::where('status', 'open')->count(),
            'completed' => InvestmentOpportunity::where('status', 'completed')->count(),
            'suspended' => InvestmentOpportunity::where('status', 'suspended')->count(),
            'visible' => InvestmentOpportunity::where('show', true)->count(),
            'total_target_amount' => InvestmentOpportunity::sum('target_amount') ?? 0,
            'total_reserved_shares' => InvestmentOpportunity::sum('reserved_shares') ?? 0,
            'new_this_month' => InvestmentOpportunity::where('created_at', '>=', $thisMonth)->count(),
            'new_this_week' => InvestmentOpportunity::where('created_at', '>=', $thisWeek)->count(),
            'new_today' => InvestmentOpportunity::whereDate('created_at', $today)->count(),
        ];

        // Calculate total raised amount for opportunities
        $opportunitiesRaised = DB::table('investment_opportunities')
            ->join('investments', 'investment_opportunities.id', '=', 'investments.opportunity_id')
            ->where('investments.status', '!=', 'cancelled')
            ->selectRaw('SUM(investments.total_investment) as total')
            ->value('total') ?? 0;

        $opportunitiesStats['total_raised'] = $opportunitiesRaised;

        // Recent opportunities
        $recentOpportunities = InvestmentOpportunity::with(['category', 'ownerProfile'])
            ->latest()
            ->take(10)
            ->get();

        // Top opportunities by investment amount
        $topOpportunities = InvestmentOpportunity::with(['category'])
            ->withCount(['investments' => function ($query) {
                $query->where('status', '!=', 'cancelled');
            }])
            ->withSum(['investments' => function ($query) {
                $query->where('status', '!=', 'cancelled');
            }], 'total_investment')
            ->orderBy('investments_sum_total_investment', 'desc')
            ->take(5)
            ->get();

        // Opportunities completion rate
        $opportunitiesStats['avg_completion_rate'] = InvestmentOpportunity::get()->avg(function ($opp) {
            return $opp->completion_rate;
        }) ?? 0;

        // ============================================
        // INVESTMENTS (الاستثمارات) - User Investments
        // ============================================
        $investmentsStats = [
            'total' => Investment::count(),
            'active' => Investment::where('status', 'active')->count(),
            'completed' => Investment::where('status', 'completed')->count(),
            'pending' => Investment::where('status', 'pending')->count(),
            'total_amount' => Investment::where('status', '!=', 'cancelled')->sum('total_investment') ?? 0,
            'total_shares' => Investment::where('status', '!=', 'cancelled')->sum('shares') ?? 0,
            'new_this_month' => Investment::where('created_at', '>=', $thisMonth)->count(),
            'new_this_week' => Investment::where('created_at', '>=', $thisWeek)->count(),
            'new_today' => Investment::whereDate('created_at', $today)->count(),
            'pending_merchandise' => Investment::where('merchandise_status', 'pending')->count(),
            'pending_distribution' => Investment::where('distribution_status', 'pending')->count(),
            'total_expected_profit' => Investment::where('status', '!=', 'cancelled')
                ->get()
                ->sum(function ($inv) {
                    return ($inv->expected_profit_per_share ?? 0) * ($inv->shares ?? 0);
                }),
            'total_actual_profit' => Investment::where('status', '!=', 'cancelled')
                ->get()
                ->sum(function ($inv) {
                    return ($inv->actual_profit_per_share ?? 0) * ($inv->shares ?? 0);
                }),
            'total_distributed_profit' => Investment::where('status', '!=', 'cancelled')
                ->sum('distributed_profit') ?? 0,
        ];

        // Recent investments
        $recentInvestments = Investment::with(['user.investorProfile', 'investmentOpportunity.category'])
            ->latest()
            ->take(10)
            ->get();

        // Top investors by investment amount
        $topInvestors = Investment::where('status', '!=', 'cancelled')
            ->select('user_id', DB::raw('SUM(total_investment) as total_invested'), DB::raw('SUM(shares) as total_shares'))
            ->with('user.investorProfile')
            ->groupBy('user_id')
            ->orderBy('total_invested', 'desc')
            ->take(5)
            ->get();

        // Investment growth trend (last 6 months)
        $investmentTrend = Investment::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_investment) as total, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // ============================================
        // MONEY/FINANCIALS (الفلوس) - Transactions & Financials
        // ============================================
        // Transactions
        $confirmedDeposits = Transaction::where('type', 'deposit')
            ->where('confirmed', true)
            ->get();
        $confirmedWithdrawals = Transaction::where('type', 'withdraw')
            ->where('confirmed', true)
            ->get();

        $financialsStats = [
            // Total transactions
            'total_transactions' => Transaction::count(),
            'confirmed_transactions' => Transaction::where('confirmed', true)->count(),
            'pending_transactions' => Transaction::where('confirmed', false)->count(),

            // Deposits
            'total_deposits' => $confirmedDeposits->count(),
            'total_deposits_amount' => $confirmedDeposits->sum(function($t) { return floatval($t->amount) / 100; }),
            'deposits_this_month' => Transaction::where('type', 'deposit')
                ->where('confirmed', true)
                ->where('created_at', '>=', $thisMonth)
                ->get()
                ->sum(function($t) { return floatval($t->amount) / 100; }),
            'deposits_today' => Transaction::where('type', 'deposit')
                ->where('confirmed', true)
                ->whereDate('created_at', $today)
                ->count(),

            // Withdrawals
            'total_withdrawals' => $confirmedWithdrawals->count(),
            'total_withdrawals_amount' => $confirmedWithdrawals->sum(function($t) { return floatval($t->amount) / 100; }),
            'withdrawals_this_month' => Transaction::where('type', 'withdraw')
                ->where('confirmed', true)
                ->where('created_at', '>=', $thisMonth)
                ->get()
                ->sum(function($t) { return floatval($t->amount) / 100; }),
            'withdrawals_today' => Transaction::where('type', 'withdraw')
                ->where('confirmed', true)
                ->whereDate('created_at', $today)
                ->count(),

            // Net balance
            'net_balance' => $confirmedDeposits->sum(function($t) { return floatval($t->amount) / 100; })
                          - $confirmedWithdrawals->sum(function($t) { return floatval($t->amount) / 100; }),
        ];

        // Withdrawal Requests
        $withdrawalRequestsStats = [
            'total' => WithdrawalRequest::count(),
            'pending' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PENDING)->count(),
            'processing' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PROCESSING)->count(),
            'completed' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_COMPLETED)->count(),
            'rejected' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_REJECTED)->count(),
            'total_pending_amount' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PENDING)->sum('amount') ?? 0,
            'total_completed_amount' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_COMPLETED)->sum('amount') ?? 0,
            'total_processing_amount' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PROCESSING)->sum('amount') ?? 0,
        ];

        // Bank Transfer Requests
        $bankTransferRequestsStats = [
            'total' => BankTransferRequest::count(),
            'pending' => BankTransferRequest::where('status', BankTransferRequest::STATUS_PENDING)->count(),
            'approved' => BankTransferRequest::where('status', BankTransferRequest::STATUS_APPROVED)->count(),
            'rejected' => BankTransferRequest::where('status', BankTransferRequest::STATUS_REJECTED)->count(),
            'total_pending_amount' => BankTransferRequest::where('status', BankTransferRequest::STATUS_PENDING)->sum('amount') ?? 0,
            'total_approved_amount' => BankTransferRequest::where('status', BankTransferRequest::STATUS_APPROVED)->sum('amount') ?? 0,
        ];

        // Recent transactions
        $recentTransactions = Transaction::with('payable')
            ->latest()
            ->take(10)
            ->get();

        // Financial trend (last 6 months)
        $financialTrend = Transaction::where('confirmed', true)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                SUM(CASE WHEN type = "deposit" THEN amount ELSE 0 END) / 100 as deposits,
                SUM(CASE WHEN type = "withdraw" THEN amount ELSE 0 END) / 100 as withdrawals
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Calculate total wallet balance from all wallets
        $totalWalletBalance = DB::table('wallets')
            ->sum('balance') ?? 0;
        $totalWalletBalance = $totalWalletBalance / 100; // Convert from cents

        $financialsStats['total_wallet_balance'] = $totalWalletBalance;

        return view('pages.dashboard2.index', compact(
            'opportunitiesStats',
            'recentOpportunities',
            'topOpportunities',
            'investmentsStats',
            'recentInvestments',
            'topInvestors',
            'investmentTrend',
            'financialsStats',
            'withdrawalRequestsStats',
            'bankTransferRequestsStats',
            'recentTransactions',
            'financialTrend',
            'today',
            'thisWeek',
            'thisMonth'
        ));
    }

    /**
     * Get investment performance data filtered by date range (API endpoint)
     */
    public function getInvestmentPerformance(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Default to last 12 months if no dates provided
        if (!$startDate || !$endDate) {
            $endDate = Carbon::now()->endOfMonth();
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        } else {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        // Group performance data by period
        $groupedData = $this->groupPerformanceData($startDate, $endDate);

        // Calculate totals for the selected date range
        $totals = $this->calculatePerformanceMetrics($startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => array_merge($groupedData, [
                'total_expected_profit' => $totals['expected_profit'],
                'total_actual_profit' => $totals['actual_profit'],
                'total_short_investments' => $totals['short_investments'],
            ])
        ]);
    }
}



