<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvestmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'investor_id' => $this->investor_id,
            'opportunity_id' => $this->opportunity_id,
            'shares' => $this->shares,
            'share_price' => (string) $this->share_price,
            'total_investment' => (string) $this->total_investment,
            'investment_amount' => $this->total_investment,
            'price_per_share' => (string) $this->getPricePerShare(),
            'total_payment_required' => (string) $this->total_payment_required,
            'formatted_investment_amount' => $this->formatted_amount,
            'investment_type' => $this->investment_type,
            'investment_type_arabic' => $this->investment_type_arabic,
            'status' => $this->status,
            'status_arabic' => $this->status_arabic,

            // Relationships
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),

            'investor_profile' => $this->whenLoaded('investorProfile', function () {
                return [
                    'id' => $this->investorProfile->id,
                    'user_id' => $this->investorProfile->user_id,
                    'investment_experience' => $this->investorProfile->investment_experience,
                    'risk_tolerance' => $this->investorProfile->risk_tolerance,
                ];
            }),


            // Per-share fields
            'shipping_and_service_fee_per_share' => $this->shipping_fee_per_share,
            'expected_profit_per_share' => $this->expected_profit_per_share,
            'expected_net_profit_per_share' => $this->expected_net_profit_per_share,
            'actual_profit_per_share' => $this->actual_profit_per_share,
            'actual_net_profit_per_share' => $this->actual_net_profit_per_share,

            // Per-share percentage fields
            'expected_profit_percentage' => (string) $this->getExpectedProfitPercentage(),
            'expected_net_profit_percentage' => (string) $this->getExpectedNetProfitPercentage(),
            'actual_profit_percentage' => (string) $this->getActualProfitPercentage(),
            'actual_net_profit_percentage' => (string) $this->getActualNetProfitPercentage(),
            'expected_merchandise_delivery_date' => $this->expected_delivery_date?->format('Y-m-d H:i:s'),
            'expected_profit_distribution_date' => $this->expected_distribution_date?->format('Y-m-d H:i:s'),

            'merchandise_delivery_status' => $this->merchandise_status,
            'merchandise_delivered_at' => $this->merchandise_arrived_at?->format('Y-m-d H:i:s'),
            'actual_returns_recorded_at' => $this->actual_returns_recorded_at?->format('Y-m-d H:i:s'),
            'profit_distribution_status' => $this->distribution_status,
            'total_distributed_profit' => $this->distributed_profit,
            'distributed_at' => $this->distributed_at?->format('Y-m-d H:i:s'),

            // Calculated totals using new methods
            'total_shipping_and_service_fee' => (string) $this->getTotalShippingAndServiceFee(),
            'total_expected_profit_amount' => (string) $this->getTotalExpectedProfitAmount(),
            'total_expected_net_profit_amount' => (string) $this->getTotalExpectedNetProfit(),
            'total_actual_profit_amount' => (string) $this->getTotalActualProfitAmount(),
            'total_actual_net_profit_amount' => (string) $this->getTotalActualNetProfit(),
            'total_investment_cost' => (string) $this->getTotalInvestmentCost(),
            'total_expected_net_profit' => (string) $this->getExpectedNetProfit(),
            'total_actual_net_profit' => (string) $this->getActualNetProfit(),
            // 'expected_profit_percentage' => (string) $this->getExpectedProfitPercentage(),
            // 'actual_profit_percentage' => (string) $this->getActualProfitPercentage(),
            'profit_performance_percentage' => (string) $this->getProfitPerformancePercentage(),
            'profit_performance_status' => $this->getProfitPerformanceStatus(),

            // Legacy calculated fields for backward compatibility
            'total_investment_value' => (string) $this->total_investment,

                'investment_date' => $this->investment_date?->format('Y-m-d H:i:s'),
                'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

                            // use InvestmentOpportunityResource
            'investment_opportunity' => $this->whenLoaded('investmentOpportunity', function () {
                return new InvestmentOpportunityResource($this->investmentOpportunity);
            }),
        ];
    }
}
