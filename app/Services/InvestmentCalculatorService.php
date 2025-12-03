<?php

namespace App\Services;

use App\Models\InvestmentOpportunity;
use App\Models\InvestorProfile;
use Carbon\Carbon;

class InvestmentCalculatorService
{
    /**
     * Calculate investment amount
     * حساب مبلغ الاستثمار
     */
    public function calculateInvestmentAmount(int $shares, float $sharePrice): float
    {
        return $shares * $sharePrice;
    }

    /**
     * Calculate total payment required using the same logic as the Investment model
     * حساب إجمالي المبلغ المطلوب باستخدام نفس منطق نموذج الاستثمار
     */
    public function calculateTotalPaymentRequired(float $totalInvestment, int $shares, string $investmentType, InvestmentOpportunity $opportunity): float
    {
        if ($investmentType === 'myself') {
            // For myself: total_investment + shipping fee
            $shippingFee = $shares * ($opportunity->shipping_fee_per_share ?? 0);
            return $totalInvestment + $shippingFee;
        } else {
            // For authorize: only total_investment (no shipping fee)
            return $totalInvestment;
        }
    }

    /**
     * Build investment data array for creation
     * بناء مصفوفة بيانات الاستثمار للإنشاء
     */
    public function buildInvestmentData(
        InvestorProfile $investor,
        InvestmentOpportunity $opportunity,
        int $shares,
        float $amount,
        float $totalPaymentRequired,
        string $investmentType
    ): array {
        $investmentData = [
            'investor_id' => $investor->id,
            'opportunity_id' => $opportunity->id,
            'shares' => $shares,
            'share_price' => $opportunity->share_price,
            'total_investment' => $amount,
            'total_payment_required' => $totalPaymentRequired,
            'user_id' => $investor->user_id,
            'investment_type' => $investmentType,
            'status' => 'active',
            'investment_date' => now(),
            'merchandise_status' => 'pending',
            'distribution_status' => 'pending',
        ];

        // Set expected delivery date for myself investments
        if ($investmentType === 'myself' && $opportunity->investment_duration) {
            $investmentData['expected_delivery_date'] = now()->addDays($opportunity->investment_duration);
        }

        // Set expected distribution date for authorize investments
        if ($investmentType === 'authorize' && $opportunity->expected_distribution_date) {
            $investmentData['expected_distribution_date'] = $opportunity->expected_distribution_date;
        }

        // Set per-share values
        $investmentData['shipping_fee_per_share'] = $opportunity->shipping_fee_per_share ?? 0;
        $investmentData['expected_profit_per_share'] = $opportunity->expected_profit ?? 0;
        $investmentData['expected_net_profit_per_share'] = $opportunity->expected_net_profit ?? 0;

        return $investmentData;
    }

    /**
     * Calculate shipping fee for investment
     * حساب رسوم الشحن للاستثمار
     */
    public function calculateShippingFee(int $shares, float $shippingFeePerShare): float
    {
        return $shares * $shippingFeePerShare;
    }

    /**
     * Calculate expected profit for investment
     * حساب الربح المتوقع للاستثمار
     */
    public function calculateExpectedProfit(int $shares, float $expectedProfitPerShare): float
    {
        return $shares * $expectedProfitPerShare;
    }

    /**
     * Calculate expected net profit for investment
     * حساب صافي الربح المتوقع للاستثمار
     */
    public function calculateExpectedNetProfit(int $shares, float $expectedNetProfitPerShare): float
    {
        return $shares * $expectedNetProfitPerShare;
    }

    /**
     * Calculate profit percentage
     * حساب نسبة الربح
     */
    public function calculateProfitPercentage(float $totalInvested, float $realizedProfits): float
    {
        if ($totalInvested <= 0) {
            return 0;
        }
        return round(($realizedProfits / $totalInvested) * 100, 2);
    }

    /**
     * Calculate return on investment (ROI)
     * حساب العائد على الاستثمار
     */
    public function calculateROI(float $initialInvestment, float $currentValue): float
    {
        if ($initialInvestment <= 0) {
            return 0;
        }
        return round((($currentValue - $initialInvestment) / $initialInvestment) * 100, 2);
    }
}
