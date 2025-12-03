<?php

namespace App\Services;

use App\Support\CurrentProfile;

class WalletStatisticsService
{
    protected CurrentProfile $currentProfile;

    public function __construct(CurrentProfile $currentProfile)
    {
        $this->currentProfile = $currentProfile;
    }

    /**
     * Get all wallet statistics for the current profile
     * الحصول على جميع إحصائيات المحفظة للبروفايل الحالي
     */
    public function getAllStatistics(): array
    {
        if ($this->currentProfile->type !== 'investor') {
            return $this->getEmptyStatistics();
        }

        $investor = $this->currentProfile->model;

        return [
            'realized_profits' => $this->calculateRealizedProfits($investor),
            'pending_profits' => $this->calculatePendingProfits($investor),
            'upcoming_earnings' => $this->calculateUpcomingEarnings($investor),
        ];
    }

    /**
     * Calculate realized profits from distributed authorize type investments
     * حساب الأرباح المحققة من الاستثمارات الموزعة من نوع authorize
     *
     * Realized profits are the distributed profits from authorize type investments only.
     * Uses the unified logic from Investment model.
     */
    public function calculateRealizedProfits($investor): array
    {
        $amount = $investor->investments()
            ->distributedAuthorize()
            ->get()
            ->sum(function ($investment) {
                return $investment->getRealizedProfit();
            });

        return [
            'amount' => $amount,
            'formatted_amount' => number_format($amount, 0) . ' ريال',
            'currency' => 'SAR',
        ];
    }

    /**
     * Calculate pending profits from not distributed authorize type investments
     * حساب الأرباح المعلقة من الاستثمارات غير الموزعة من نوع authorize
     *
     * Pending profits are the expected profits from authorize type investments
     * that have not been distributed yet.
     * Uses the unified logic from Investment model.
     */
    public function calculatePendingProfits($investor): array
    {
        $amount = $investor->investments()
            ->notDistributedAuthorize()
            ->get()
            ->sum(function ($investment) {
                return $investment->getPendingProfit();
            });

        return [
            'amount' => $amount,
            'formatted_amount' => number_format($amount, 0) . ' ريال',
            'currency' => 'SAR',
        ];
    }

    /**
     * Calculate upcoming earnings
     * حساب الأرباح القادمة
     *
     * Gets the next authorize type investment that has not been distributed yet
     * and has an expected distribution date.
     */
    public function calculateUpcomingEarnings($investor): array
    {
        // Get the next not distributed authorize investment with expected distribution date
        $nextInvestment = $investor->investments()
            ->notDistributedAuthorize()
            ->whereNotNull('expected_distribution_date')
            ->orderBy('expected_distribution_date', 'asc')
            ->first();

        if (!$nextInvestment) {
            return [
                'amount' => 0,
                'formatted_amount' => '0 SAR',
                'currency' => 'SAR',
                'next_due_date' => null,
                'formatted_due_date' => null,
            ];
        }

        // Use the unified pending profit method
        $amount = $nextInvestment->getPendingProfit();
        $dueDate = $nextInvestment->expected_distribution_date;

        return [
            'amount' => $amount,
            'formatted_amount' => number_format($amount, 0) . ' SAR',
            'currency' => 'SAR',
            'next_due_date' => $dueDate?->toDateString(),
            'formatted_due_date' => $dueDate?->format('Y-m-d'),
        ];
    }

    /**
     * Get empty statistics for non-investor profiles
     * الحصول على إحصائيات فارغة للبروفايلات غير المستثمرة
     */
    private function getEmptyStatistics(): array
    {
        return [
            'realized_profits' => [
                'amount' => 0,
                'formatted_amount' => '0 ريال',
                'currency' => 'SAR',
            ],
            'pending_profits' => [
                'amount' => 0,
                'formatted_amount' => '0 ريال',
                'currency' => 'SAR',
            ],
            'upcoming_earnings' => [
                'amount' => 0,
                'formatted_amount' => '0 SAR',
                'currency' => 'SAR',
                'next_due_date' => null,
                'formatted_due_date' => null,
            ],
        ];
    }
}
