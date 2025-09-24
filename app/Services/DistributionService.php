<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use App\Models\InvestorProfile;
use Illuminate\Support\Facades\DB;
use Exception;

// for authorize investments
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
            // Deposit to investor's wallet
            $this->walletService->depositToWallet(
                $investment->investor,
                $amount,
                [
                    'type' => 'returns_distribution',
                    'investment_id' => $investment->id,
                    'opportunity_id' => $investment->opportunity_id,
                    'opportunity_name' => $investment->opportunity->name,
                    'description' => $description ?? "توزيع عوائد من فرصة: {$investment->opportunity->name}",
                ]
            );

            // Update investment distribution status
            $investment->update([
                'distribution_status' => 'distributed',
                'distributed_amount' => $amount,
                'distributed_at' => now(),
            ]);

            // Log the distribution
            \Log::info('Returns distributed to investor', [
                'investment_id' => $investment->id,
                'investor_id' => $investment->investor_id,
                'opportunity_id' => $investment->opportunity_id,
                'distributed_amount' => $amount,
            ]);

            return true;
        });
    }

    /**
     * Distribute returns for all investments in an opportunity
     * توزيع العوائد لجميع الاستثمارات في فرصة معينة
     */
    public function distributeOpportunityReturns(InvestmentOpportunity $opportunity): array
    {
        $results = [
            'myself_investments' => 0,
            'authorize_investments' => 0,
            'total_distributed' => 0,
            'errors' => [],
        ];

        // Distribute for myself investments (merchandise arrived)
        $myselfInvestments = $opportunity->investments()
            ->where('investment_type', 'myself')
            ->where('merchandise_status', 'arrived')
            ->where('distribution_status', '!=', 'distributed')
            ->get();

        foreach ($myselfInvestments as $investment) {
            try {
                $expectedReturns = $this->calculateExpectedReturnsForDistribution($investment);
                $this->distributeReturns($investment, $expectedReturns['net_return'], 'توزيع عوائد بضائع واصلة');
                $results['myself_investments']++;
                $results['total_distributed'] += $expectedReturns['net_return'];
            } catch (Exception $e) {
                $results['errors'][] = [
                    'investment_id' => $investment->id,
                    'type' => 'myself',
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Distribute for authorize investments (actual returns recorded)
        $authorizeInvestments = $opportunity->investments()
            ->where('investment_type', 'authorize')
            ->whereNotNull('actual_net_return')
            ->where('distribution_status', '!=', 'distributed')
            ->get();

        foreach ($authorizeInvestments as $investment) {
            try {
                $this->distributeReturns($investment, $investment->actual_net_return, 'توزيع عوائد مبيعات مفوضة');
                $results['authorize_investments']++;
                $results['total_distributed'] += $investment->actual_net_return;
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
     * Calculate expected returns for distribution (myself type)
     * حساب العوائد المتوقعة للتوزيع (نوع بيع بنفسي)
     */
    protected function calculateExpectedReturnsForDistribution(Investment $investment): array
    {
        $opportunity = $investment->opportunity;
        $shares = $investment->shares;

        $expectedReturnAmount = $shares * ($opportunity->expected_return_amount_by_myself ?? 0);
        $expectedNetReturn = $shares * ($opportunity->expected_net_return_by_myself ?? 0);
        $shippingAndServiceFee = $shares * ($opportunity->shipping_and_service_fee ?? 0);

        return [
            'return_amount' => $expectedReturnAmount,
            'net_return' => $expectedNetReturn,
            'shipping_and_service_fee' => $shippingAndServiceFee,
        ];
    }

    /**
     * Get distribution statistics for an opportunity
     * الحصول على إحصائيات التوزيع لفرصة معينة
     */
    public function getDistributionStatistics(InvestmentOpportunity $opportunity): array
    {
        $totalInvestments = $opportunity->investments()->count();
        $distributedInvestments = $opportunity->investments()
            ->where('distribution_status', 'distributed')
            ->count();

        $totalDistributedAmount = $opportunity->investments()
            ->where('distribution_status', 'distributed')
            ->sum('distributed_amount');

        $myselfInvestments = $opportunity->investments()
            ->where('investment_type', 'myself')
            ->where('merchandise_status', 'arrived')
            ->count();

        $authorizeInvestments = $opportunity->investments()
            ->where('investment_type', 'authorize')
            ->whereNotNull('actual_net_return')
            ->count();

        return [
            'total_investments' => $totalInvestments,
            'distributed_investments' => $distributedInvestments,
            'pending_distribution' => $totalInvestments - $distributedInvestments,
            'total_distributed_amount' => $totalDistributedAmount,
            'ready_for_distribution' => [
                'myself_investments' => $myselfInvestments,
                'authorize_investments' => $authorizeInvestments,
            ],
            'distribution_completion_rate' => $totalInvestments > 0
                ? round(($distributedInvestments / $totalInvestments) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get investments ready for distribution
     * الحصول على الاستثمارات الجاهزة للتوزيع
     */
    public function getInvestmentsReadyForDistribution(InvestmentOpportunity $opportunity = null)
    {
        $query = Investment::where('distribution_status', '!=', 'distributed')
            ->with(['opportunity', 'investor.user']);

        if ($opportunity) {
            $query->where('opportunity_id', $opportunity->id);
        }

        return $query->get()->filter(function ($investment) {
            if ($investment->investment_type === 'myself') {
                return $investment->merchandise_status === 'arrived';
            } else {
                return $investment->actual_net_return !== null;
            }
        });
    }

    /**
     * Get distributed investments
     * الحصول على الاستثمارات الموزعة
     */
    public function getDistributedInvestments(InvestmentOpportunity $opportunity = null)
    {
        $query = Investment::where('distribution_status', 'distributed')
            ->with(['opportunity', 'investor.user']);

        if ($opportunity) {
            $query->where('opportunity_id', $opportunity->id);
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
     * Get distribution history for an investor
     * الحصول على تاريخ التوزيع لمستثمر معين
     */
    public function getInvestorDistributionHistory(InvestorProfile $investor, int $perPage = 15)
    {
        return $investor->investments()
            ->where('distribution_status', 'distributed')
            ->with(['opportunity'])
            ->orderBy('distributed_at', 'desc')
            ->paginate($perPage);
    }
}
