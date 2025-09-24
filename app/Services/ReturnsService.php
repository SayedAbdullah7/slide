<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use Illuminate\Support\Facades\DB;
use Exception;

// for authorize investments
class ReturnsService
{
    /**
     * Record actual returns for an investment (for authorize type)
     * تسجيل العوائد الفعلية لاستثمار (للنوع المفوض)
     */
    public function recordActualReturns(Investment $investment, float $actualReturnAmount, float $actualNetReturn): bool
    {
        if ($investment->investment_type !== 'authorize') {
            throw new Exception('يمكن فقط تسجيل العوائد الفعلية للاستثمارات من نوع "تفويض بالبيع"');
        }

        if ($investment->actual_return_amount !== null) {
            throw new Exception('العوائد الفعلية مسجلة مسبقاً لهذا الاستثمار');
        }

        return DB::transaction(function () use ($investment, $actualReturnAmount, $actualNetReturn) {
            $investment->update([
                'actual_return_amount' => $actualReturnAmount,
                'actual_net_return' => $actualNetReturn,
                'actual_returns_recorded_at' => now(),
            ]);

            // Log the actual returns recording
            \Log::info('Actual returns recorded for investment', [
                'investment_id' => $investment->id,
                'opportunity_id' => $investment->opportunity_id,
                'investor_id' => $investment->investor_id,
                'actual_return_amount' => $actualReturnAmount,
                'actual_net_return' => $actualNetReturn,
            ]);

            return true;
        });
    }

    /**
     * Record actual returns for all authorize investments in an opportunity
     * تسجيل العوائد الفعلية لجميع الاستثمارات المفوضة في فرصة معينة
     */
    public function recordOpportunityActualReturns(InvestmentOpportunity $opportunity, array $returnsData): int
    {
        $authorizeInvestments = $opportunity->investments()
            ->where('investment_type', 'authorize')
            ->whereNull('actual_return_amount')
            ->get();

        $recordedCount = 0;

        foreach ($authorizeInvestments as $investment) {
            try {
                $investorId = $investment->investor_id;

                if (isset($returnsData[$investorId])) {
                    $this->recordActualReturns(
                        $investment,
                        $returnsData[$investorId]['actual_return_amount'],
                        $returnsData[$investorId]['actual_net_return']
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
        $opportunity = $investment->opportunity;
        $shares = $investment->shares;

        if ($investment->investment_type === 'myself') {
            return [
                'expected_return_amount' => $shares * ($opportunity->expected_return_amount_by_myself ?? 0),
                'expected_net_return' => $shares * ($opportunity->expected_net_return_by_myself ?? 0),
                'shipping_and_service_fee' => $shares * ($opportunity->shipping_and_service_fee ?? 0),
            ];
        } else {
            return [
                'expected_return_amount' => $shares * ($opportunity->expected_return_amount_by_authorize ?? 0),
                'expected_net_return' => $shares * ($opportunity->expected_net_return_by_authorize ?? 0),
                'shipping_and_service_fee' => 0, // No shipping fee for authorize type
            ];
        }
    }

    /**
     * Get actual returns for an investment
     * الحصول على العوائد الفعلية لاستثمار معين
     */
    public function getActualReturns(Investment $investment): array
    {
        return [
            'actual_return_amount' => $investment->actual_return_amount ?? 0,
            'actual_net_return' => $investment->actual_net_return ?? 0,
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

        $returnVariance = $actual['actual_return_amount'] - $expected['expected_return_amount'];
        $netReturnVariance = $actual['actual_net_return'] - $expected['expected_net_return'];

        return [
            'expected' => $expected,
            'actual' => $actual,
            'variance' => [
                'return_amount' => $returnVariance,
                'net_return' => $netReturnVariance,
            ],
            'performance' => [
                'return_percentage' => $expected['expected_return_amount'] > 0
                    ? round(($returnVariance / $expected['expected_return_amount']) * 100, 2)
                    : 0,
                'net_return_percentage' => $expected['expected_net_return'] > 0
                    ? round(($netReturnVariance / $expected['expected_net_return']) * 100, 2)
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
        $authorizeInvestments = $opportunity->investments()
            ->where('investment_type', 'authorize')
            ->get();

        $totalExpectedReturn = 0;
        $totalActualReturn = 0;
        $recordedReturns = 0;

        foreach ($authorizeInvestments as $investment) {
            $expected = $this->getExpectedReturns($investment);
            $totalExpectedReturn += $expected['expected_return_amount'];

            if ($investment->actual_return_amount !== null) {
                $actual = $this->getActualReturns($investment);
                $totalActualReturn += $actual['actual_return_amount'];
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
        $query = Investment::where('investment_type', 'authorize')
            ->whereNull('actual_return_amount')
            ->with(['opportunity', 'investor.user']);

        if ($opportunity) {
            $query->where('opportunity_id', $opportunity->id);
        }

        return $query->get();
    }

    /**
     * Get investments with recorded actual returns
     * الحصول على الاستثمارات التي سجلت عوائدها الفعلية
     */
    public function getRecordedActualReturns(InvestmentOpportunity $opportunity = null)
    {
        $query = Investment::where('investment_type', 'authorize')
            ->whereNotNull('actual_return_amount')
            ->with(['opportunity', 'investor.user']);

        if ($opportunity) {
            $query->where('opportunity_id', $opportunity->id);
        }

        return $query->get();
    }

    /**
     * Check if all authorize investments have recorded actual returns
     * التحقق من تسجيل العوائد الفعلية لجميع الاستثمارات المفوضة
     */
    public function allAuthorizeReturnsRecorded(InvestmentOpportunity $opportunity): bool
    {
        $totalAuthorizeInvestments = $opportunity->investments()
            ->where('investment_type', 'authorize')
            ->count();

        $recordedInvestments = $opportunity->investments()
            ->where('investment_type', 'authorize')
            ->whereNotNull('actual_return_amount')
            ->count();

        return $totalAuthorizeInvestments > 0 && $totalAuthorizeInvestments === $recordedInvestments;
    }
}
