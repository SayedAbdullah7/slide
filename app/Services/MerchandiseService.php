<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use Illuminate\Support\Facades\DB;
use Exception;

// for myself investments
class MerchandiseService
{
    /**
     * Mark merchandise as arrived for a specific investment
     * وضع علامة على وصول البضائع لاستثمار محدد
     */
    public function markAsArrived(Investment $investment): bool
    {
        if ($investment->investment_type !== 'myself') {
            throw new Exception('يمكن فقط وضع علامة وصول البضائع للاستثمارات من نوع "بيع بنفسي"');
        }

        if ($investment->merchandise_status === 'arrived') {
            throw new Exception('البضائع مسجلة مسبقاً كواصلة');
        }

        return DB::transaction(function () use ($investment) {
            $investment->update([
                'merchandise_status' => 'arrived',
                'merchandise_arrived_at' => now(),
            ]);

            // Log the merchandise arrival
            \Log::info('Merchandise marked as arrived', [
                'investment_id' => $investment->id,
                'opportunity_id' => $investment->opportunity_id,
                'investor_id' => $investment->investor_id,
                'shares' => $investment->shares,
            ]);

            return true;
        });
    }

    /**
     * Mark merchandise as arrived for all investments in an opportunity
     * وضع علامة على وصول البضائع لجميع الاستثمارات في فرصة معينة
     */
    public function markOpportunityMerchandiseAsArrived(InvestmentOpportunity $opportunity): int
    {
        $myselfInvestments = $opportunity->investments()
            ->where('investment_type', 'myself')
            ->where('merchandise_status', '!=', 'arrived')
            ->get();

        $markedCount = 0;

        foreach ($myselfInvestments as $investment) {
            try {
                $this->markAsArrived($investment);
                $markedCount++;
            } catch (Exception $e) {
                \Log::error('Failed to mark merchandise as arrived', [
                    'investment_id' => $investment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $markedCount;
    }

    /**
     * Get investments waiting for merchandise delivery
     * الحصول على الاستثمارات المنتظرة لتسليم البضائع
     */
    public function getPendingMerchandiseDeliveries(InvestmentOpportunity $opportunity = null)
    {
        $query = Investment::where('investment_type', 'myself')
            ->where('merchandise_status', 'pending')
            ->with(['opportunity', 'investor.user']);

        if ($opportunity) {
            $query->where('opportunity_id', $opportunity->id);
        }

        return $query->get();
    }

    /**
     * Get investments with arrived merchandise
     * الحصول على الاستثمارات التي وصلت بضائعها
     */
    public function getArrivedMerchandise(InvestmentOpportunity $opportunity = null)
    {
        $query = Investment::where('investment_type', 'myself')
            ->where('merchandise_status', 'arrived')
            ->with(['opportunity', 'investor.user']);

        if ($opportunity) {
            $query->where('opportunity_id', $opportunity->id);
        }

        return $query->get();
    }

    /**
     * Get merchandise delivery statistics for an opportunity
     * الحصول على إحصائيات تسليم البضائع لفرصة معينة
     */
    public function getMerchandiseStatistics(InvestmentOpportunity $opportunity): array
    {
        $totalMyselfInvestments = $opportunity->investments()
            ->where('investment_type', 'myself')
            ->count();

        $arrivedInvestments = $opportunity->investments()
            ->where('investment_type', 'myself')
            ->where('merchandise_status', 'arrived')
            ->count();

        $pendingInvestments = $opportunity->investments()
            ->where('investment_type', 'myself')
            ->where('merchandise_status', 'pending')
            ->count();

        return [
            'total_myself_investments' => $totalMyselfInvestments,
            'arrived_investments' => $arrivedInvestments,
            'pending_investments' => $pendingInvestments,
            'delivery_completion_rate' => $totalMyselfInvestments > 0
                ? round(($arrivedInvestments / $totalMyselfInvestments) * 100, 2)
                : 0,
        ];
    }

    /**
     * Update expected delivery date for an investment
     * تحديث تاريخ التسليم المتوقع لاستثمار معين
     */
    public function updateExpectedDeliveryDate(Investment $investment, $expectedDate): bool
    {
        if ($investment->investment_type !== 'myself') {
            throw new Exception('يمكن فقط تحديث تاريخ التسليم للاستثمارات من نوع "بيع بنفسي"');
        }

        return $investment->update([
            'expected_delivery_date' => $expectedDate,
        ]);
    }

    /**
     * Get investments by delivery status
     * الحصول على الاستثمارات حسب حالة التسليم
     */
    public function getInvestmentsByDeliveryStatus(string $status, InvestmentOpportunity $opportunity = null)
    {
        $query = Investment::where('investment_type', 'myself')
            ->where('merchandise_status', $status)
            ->with(['opportunity', 'investor.user']);

        if ($opportunity) {
            $query->where('opportunity_id', $opportunity->id);
        }

        return $query->get();
    }
}
