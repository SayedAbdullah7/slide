<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * ReturnsService - Service for managing investment returns recording
 *
 * This service handles the recording of actual returns for "authorize" type investments.
 * It manages the transition from expected returns to actual returns when sales are completed.
 *
 * ## Service Role:
 * - Records actual profit and net profit per share for authorize investments
 * - Manages the transition from expected to actual returns
 * - Provides statistics and reporting on returns recording
 * - Handles bulk operations for recording returns across multiple investments
 *
 * ## Returns Recording Conditions:
 * - Only processes "authorize" type investments (not "myself" type)
 * - Requires actual_profit_per_share and actual_net_profit_per_share values
 * - Investment must not have already recorded actual returns
 * - All recordings are timestamped and logged for audit purposes
 *
 * ## Returns Recording Process:
 * 1. Validates investment eligibility (authorize type, not already recorded)
 * 2. Records actual profit per share and actual net profit per share
 * 3. Sets actual_returns_recorded_at timestamp
 * 4. Logs the recording activity for audit purposes
 * 5. Enables the investment to be ready for distribution
 *
 * ## Key Methods:
 * - recordActualProfitPerShare(): Record returns for a single investment
 * - recordOpportunityActualProfitPerShare(): Record returns for multiple investments with custom data
 * - recordActualProfitForAllAuthorizeInvestments(): Record same returns for all investments
 * - getExpectedReturns(): Get expected returns data
 * - getActualReturns(): Get actual returns data
 * - getReturnsComparison(): Compare expected vs actual returns
 * - getOpportunityReturnsStatistics(): Get statistics for an opportunity
 * - getPendingActualReturns(): Get investments waiting for returns recording
 * - getRecordedActualReturns(): Get investments with recorded returns
 *
 * @package App\Services
 * @author AI Assistant
 * @version 1.0
 */
class ReturnsService
{
    /**
     * Record actual profit per share for an investment (for authorize type)
     * تسجيل الربح الفعلي لكل سهم لاستثمار (للنوع المفوض)
     */
    public function recordActualProfitPerShare(Investment $investment, float $actualProfitPerShare, float $actualNetProfitPerShare): bool
    {
        if ($investment->investment_type !== 'authorize') {
            throw new Exception('يمكن فقط تسجيل العوائد الفعلية للاستثمارات من نوع "تفويض بالبيع"');
        }

        if ($investment->actual_profit_per_share !== null) {
            throw new Exception('العوائد الفعلية مسجلة مسبقاً لهذا الاستثمار');
        }

        return DB::transaction(function () use ($investment, $actualProfitPerShare, $actualNetProfitPerShare) {
            $investment->update([
                'actual_profit_per_share' => $actualProfitPerShare,
                'actual_net_profit_per_share' => $actualNetProfitPerShare,
                'actual_returns_recorded_at' => now(),
            ]);

            // Log the actual returns recording
            \Log::info('Actual returns recorded for investment', [
                'investment_id' => $investment->id,
                'opportunity_id' => $investment->opportunity_id,
                'investor_id' => $investment->investor_id,
                'actual_profit_per_share' => $actualProfitPerShare,
                'actual_net_profit_per_share' => $actualNetProfitPerShare,
            ]);

            return true;
        });
    }

    /**
     * Record actual profit per share for all authorize investments in an opportunity
     * تسجيل الربح الفعلي لكل سهم لجميع الاستثمارات المفوضة في فرصة معينة
     */
    public function recordOpportunityActualProfitPerShare(InvestmentOpportunity $opportunity, array $profitData): int
    {
        $authorizeInvestments = $opportunity->investmentsAuthorize()
            ->whereNull('actual_profit_per_share')
            ->get();

        $recordedCount = 0;

        foreach ($authorizeInvestments as $investment) {
            try {
                $investorId = $investment->investor_id;

                if (isset($profitData[$investorId])) {
                    $this->recordActualProfitPerShare(
                        $investment,
                        $profitData[$investorId]['actual_profit_per_share'],
                        $profitData[$investorId]['actual_net_profit_per_share']
                    );
                    $recordedCount++;
                }
            } catch (Exception $e) {
                \Log::error('Failed to record actual returns', [
                    'investment_id' => $investment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $recordedCount;
    }

    /**
     * Get expected returns for an investment
     * الحصول على العوائد المتوقعة لاستثمار معين
     */
    public function getExpectedReturns(Investment $investment): array
    {
        return [
            'expected_profit_amount' => $investment->getTotalExpectedProfitAmount(),
            'expected_net_profit' => $investment->getTotalExpectedNetProfit(),
            'shipping_fee' => $investment->getTotalShippingAndServiceFee(),
        ];
    }

    /**
     * Get actual returns for an investment
     * الحصول على العوائد الفعلية لاستثمار معين
     */
    public function getActualReturns(Investment $investment): array
    {
        return [
            'actual_profit_amount' => $investment->getTotalActualProfitAmount(),
            'actual_net_profit' => $investment->getTotalActualNetProfit(),
            'returns_recorded_at' => $investment->actual_returns_recorded_at,
        ];
    }

    /**
     * Get returns comparison for an investment
     * الحصول على مقارنة العوائد لاستثمار معين
     */
    public function getReturnsComparison(Investment $investment): array
    {
        $expected = $this->getExpectedReturns($investment);
        $actual = $this->getActualReturns($investment);

        $profitVariance = $actual['actual_profit_amount'] - $expected['expected_profit_amount'];
        $netProfitVariance = $actual['actual_net_profit'] - $expected['expected_net_profit'];

        return [
            'expected' => $expected,
            'actual' => $actual,
            'variance' => [
                'profit_amount' => $profitVariance,
                'net_profit' => $netProfitVariance,
            ],
            'performance' => [
                'profit_percentage' => $expected['expected_profit_amount'] > 0
                    ? round(($profitVariance / $expected['expected_profit_amount']) * 100, 2)
                    : 0,
                'net_profit_percentage' => $expected['expected_net_profit'] > 0
                    ? round(($netProfitVariance / $expected['expected_net_profit']) * 100, 2)
                    : 0,
            ],
        ];
    }

    /**
     * Get returns statistics for an opportunity
     * الحصول على إحصائيات العوائد لفرصة معينة
     */
    public function getOpportunityReturnsStatistics(InvestmentOpportunity $opportunity): array
    {
        $authorizeInvestments = $opportunity->investmentsAuthorize()->get();

        $totalExpectedReturn = 0;
        $totalActualReturn = 0;
        $recordedReturns = 0;

        foreach ($authorizeInvestments as $investment) {
            $expected = $this->getExpectedReturns($investment);
            $totalExpectedReturn += $expected['expected_profit_amount'];

            if ($investment->actual_profit_per_share !== null) {
                $actual = $this->getActualReturns($investment);
                $totalActualReturn += $actual['actual_profit_amount'];
                $recordedReturns++;
            }
        }

        return [
            'total_authorize_investments' => $authorizeInvestments->count(),
            'recorded_returns_count' => $recordedReturns,
            'total_expected_return' => $totalExpectedReturn,
            'total_actual_return' => $totalActualReturn,
            'returns_variance' => $totalActualReturn - $totalExpectedReturn,
            'completion_rate' => $authorizeInvestments->count() > 0
                ? round(($recordedReturns / $authorizeInvestments->count()) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get investments waiting for actual returns recording
     * الحصول على الاستثمارات المنتظرة لتسجيل العوائد الفعلية
     */
    public function getPendingActualReturns(InvestmentOpportunity $opportunity = null)
    {
        $query = Investment::authorize()
            ->whereNull('actual_profit_per_share')
            ->with(['opportunity', 'investor.user']);

        if ($opportunity) {
            $query->forOpportunity($opportunity->id);
        }

        return $query->get();
    }

    /**
     * Get investments with recorded actual returns
     * الحصول على الاستثمارات التي سجلت عوائدها الفعلية
     */
    public function getRecordedActualReturns(InvestmentOpportunity $opportunity = null)
    {
        $query = Investment::authorize()
            ->withActualReturns()
            ->with(['opportunity', 'investor.user']);

        if ($opportunity) {
            $query->forOpportunity($opportunity->id);
        }

        return $query->get();
    }

    /**
     * Check if all authorize investments have recorded actual returns
     * التحقق من تسجيل العوائد الفعلية لجميع الاستثمارات المفوضة
     */
    public function allAuthorizeReturnsRecorded(InvestmentOpportunity $opportunity): bool
    {
        $totalAuthorizeInvestments = $opportunity->investmentsAuthorize()->count();

        $recordedInvestments = $opportunity->investmentsAuthorize()
            ->withActualReturns()
            ->count();

        return $totalAuthorizeInvestments > 0 && $totalAuthorizeInvestments === $recordedInvestments;
    }

    /**
     * Record actual profit per share for all authorize investments in an opportunity with same values
     * تسجيل الربح الفعلي لكل سهم لجميع الاستثمارات المفوضة في فرصة معينة بنفس القيم
     */
    public function recordActualProfitForAllAuthorizeInvestments(
        InvestmentOpportunity $opportunity,
        float $actualProfitPerShare,
        float $actualNetProfitPerShare
    ): array {
        $authorizeInvestments = $opportunity->investmentsAuthorize()
            ->whereNull('actual_profit_per_share') // Only process investments that haven't been recorded yet
            ->get();

        $recordedCount = 0;
        $errors = [];

        foreach ($authorizeInvestments as $investment) {
            try {
                $this->recordActualProfitPerShare(
                    $investment,
                    $actualProfitPerShare,
                    $actualNetProfitPerShare
                );
                $recordedCount++;
            } catch (Exception $e) {
                $errors[] = [
                    'investment_id' => $investment->id,
                    'investor_id' => $investment->investor_id,
                    'error' => $e->getMessage(),
                ];
                \Log::error('Failed to record actual profit for investment', [
                    'investment_id' => $investment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'total_authorize_investments' => $authorizeInvestments->count(),
            'recorded_count' => $recordedCount,
            'errors' => $errors,
            'success' => $recordedCount > 0,
        ];
    }
}
