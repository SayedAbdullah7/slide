<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use App\Models\InvestorProfile;
use App\Models\InvestmentDistribution;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * DistributionService - Service for managing investment profit distributions
 *
 * This service handles the distribution of profits from investment opportunities to investors.
 * It specifically focuses on "authorize" type investments where actual returns have been recorded.
 *
 * ## Service Role:
 * - Distributes profits to investor wallets when actual returns are recorded
 * - Manages distribution records and tracks distribution status
 * - Provides statistics and reporting on distribution activities
 * - Handles distribution history and audit trails
 *
 * ## Distribution Conditions:
 * - Only processes "authorize" type investments (not "myself" type)
 * - Requires actual_net_profit_per_share to be recorded (not null)
 * - Investment must not be already distributed (distribution_status != 'distributed')
 * - All distributions are recorded with timestamps and metadata
 *
 * ## Distribution Process:
 * 1. Validates investment eligibility (authorize type, has actual returns, not distributed)
 * 2. Creates distribution record in InvestmentDistribution table
 * 3. Deposits profit amount to investor's wallet via WalletService
 * 4. Updates investment status to 'distributed' with distribution details
 * 5. Logs the distribution activity for audit purposes
 *
 * ## Key Methods:
 * - distributeReturns(): Distribute returns for a single investment
 * - distributeOpportunityReturns(): Distribute returns for all eligible investments in an opportunity
 * - getDistributionStatistics(): Get distribution statistics for an opportunity
 * - getInvestmentsReadyForDistribution(): Get investments ready for distribution
 * - getDistributedInvestments(): Get already distributed investments
 * - getInvestorDistributionHistory(): Get distribution history for a specific investor
 *
 * @package App\Services
 * @author AI Assistant
 * @version 1.0
 */
class DistributionService
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Distribute returns to an investor's wallet
     * توزيع العوائد على محفظة المستثمر
     */
    public function distributeReturns(Investment $investment, float $amount, string $description = null): bool
    {
        if ($investment->distribution_status === 'distributed') {
            throw new Exception('العوائد موزعة مسبقاً لهذا الاستثمار');
        }

        if ($amount <= 0) {
            throw new Exception('مبلغ التوزيع يجب أن يكون أكبر من الصفر');
        }

        return DB::transaction(function () use ($investment, $amount, $description) {
            // Create distribution record
            $distribution = InvestmentDistribution::create([
                'investment_id' => $investment->id,
                'distributed_amount' => $amount,
                'is_distributed' => false,
            ]);

            // Deposit to investor's wallet
            $this->walletService->depositToWallet(
                $investment->investor,
                $amount,
                [
                    'type' => 'returns_distribution',
                    'investment_id' => $investment->id,
                    'distribution_id' => $distribution->id,
                    'opportunity_id' => $investment->opportunity_id,
                    'opportunity_name' => $investment->opportunity->name,
                    'description' => $description ?? "توزيع عوائد من فرصة: {$investment->opportunity->name}",
                ]
            );

            // Mark distribution as completed
            $distribution->markAsDistributed();

            // Update investment distribution status and mark as completed
            $investment->update([
                'distribution_status' => 'distributed',
                'distributed_profit' => $amount,
                'distributed_at' => now(),
                'status' => 'completed',
            ]);

            // Send notification to user
            $user = $investment->investor->user;
            if ($user) {
                $balance = $this->walletService->getWalletBalance($investment->investor);
                $user->notify(new \App\Notifications\ProfitDistributedNotification(
                    $investment,
                    $amount,
                    $balance
                ));
            }

            // Log the distribution
            \Log::info('Returns distributed to investor', [
                'investment_id' => $investment->id,
                'distribution_id' => $distribution->id,
                'investor_id' => $investment->investor_id,
                'opportunity_id' => $investment->opportunity_id,
                'distributed_amount' => $amount,
            ]);

            return true;
        });
    }

    /**
     * Distribute returns for all investments in an opportunity (authorize type only)
     */
    public function distributeOpportunityReturns(InvestmentOpportunity $opportunity)
    {
        $results = [
            'authorize_investments' => 0,
            'total_distributed' => 0,
            'errors' => [],
        ];

        // Use existing relationship and scope for authorize investments
        $authorizeInvestments = $opportunity->investmentsAuthorize()
            ->readyForDistribution()
            ->get();

        foreach ($authorizeInvestments as $investment) {
            try {
                $investment->actual_net_profit_per_share = $opportunity->actual_net_profit_per_share;

                // توزيع الأصل المدفوع مع الربح
                $totalAmountToDistribute = $investment->getTotalActualReturns();

                $this->distributeReturns($investment, $totalAmountToDistribute, 'توزيع عوائد مبيعات مفوضة');
                $results['authorize_investments']++;
                $results['total_distributed'] += $totalAmountToDistribute;
            } catch (Exception $e) {
                $results['errors'][] = [
                    'investment_id' => $investment->id,
                    'type' => 'authorize',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get distribution statistics for an opportunity (authorize type only)
     * الحصول على إحصائيات التوزيع لفرصة معينة (نوع مفوض فقط)
     */
    public function getDistributionStatistics(InvestmentOpportunity $opportunity): array
    {
        // Use existing relationship for authorize investments
        $totalAuthorizeInvestments = $opportunity->investmentsAuthorize()->count();

        $distributedAuthorizeInvestments = $opportunity->investmentsAuthorize()
            ->statusDistributed()
            ->count();

        $totalDistributedAmount = $opportunity->investmentsAuthorize()
            ->statusDistributed()
            ->sum('distributed_profit');

        $authorizeInvestmentsReady = $opportunity->investmentsAuthorize()
            ->readyForDistribution()
            ->count();

        return [
            'total_investments' => $totalAuthorizeInvestments,
            'distributed_investments' => $distributedAuthorizeInvestments,
            'pending_distribution' => $totalAuthorizeInvestments - $distributedAuthorizeInvestments,
            'total_distributed_amount' => $totalDistributedAmount,
            'ready_for_distribution' => [
                'authorize_investments' => $authorizeInvestmentsReady,
            ],
            'distribution_completion_rate' => $totalAuthorizeInvestments > 0
                ? round(($distributedAuthorizeInvestments / $totalAuthorizeInvestments) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get investments ready for distribution (authorize type only)
     * الحصول على الاستثمارات الجاهزة للتوزيع (نوع مفوض فقط)
     */
    public function getInvestmentsReadyForDistribution(InvestmentOpportunity $opportunity = null)
    {
        $query = Investment::readyForDistribution()
            ->with(['opportunity', 'investor.user']);

        if ($opportunity) {
            $query->forOpportunity($opportunity->id);
        }

        return $query->get();
    }

    /**
     * Get distributed investments (authorize type only)
     * الحصول على الاستثمارات الموزعة (نوع مفوض فقط)
     */
    public function getDistributedInvestments(InvestmentOpportunity $opportunity = null)
    {
        $query = Investment::authorize()
            ->statusDistributed()
            ->with(['opportunity', 'investor.user']);

        if ($opportunity) {
            $query->forOpportunity($opportunity->id);
        }

        return $query->get();
    }

    /**
     * Check if all eligible investments are distributed
     * التحقق من توزيع جميع الاستثمارات المؤهلة
     */
    public function allEligibleInvestmentsDistributed(InvestmentOpportunity $opportunity): bool
    {
        $readyForDistribution = $this->getInvestmentsReadyForDistribution($opportunity);
        $distributed = $this->getDistributedInvestments($opportunity);

        return $readyForDistribution->count() === 0 && $distributed->count() > 0;
    }

    /**
     * Get distribution history for an investor (authorize type only)
     * الحصول على تاريخ التوزيع لمستثمر معين (نوع مفوض فقط)
     */
    public function getInvestorDistributionHistory(InvestorProfile $investor, int $perPage = 15)
    {
        return $investor->investments()
            ->authorize()
            ->statusDistributed()
            ->with(['opportunity'])
            ->orderBy('distributed_at', 'desc')
            ->paginate($perPage);
    }
}
