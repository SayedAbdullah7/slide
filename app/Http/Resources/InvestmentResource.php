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
            'amount' => $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'investment_type' => $this->investment_type,
            'investment_type_arabic' => $this->investment_type_arabic,
            'status' => $this->status,
            'status_arabic' => $this->status_arabic,
            'investment_date' => $this->investment_date?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

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
            // use InvestmentOpportunityResource
            'investment_opportunity' => $this->whenLoaded('investmentOpportunity', function () {
                return new InvestmentOpportunityResource($this->investmentOpportunity);
            }),

            // Calculated fields
            'total_investment_value' => $this->shares * $this->investmentOpportunity?->price_per_share,
            'expected_return_by_myself' => $this->investment_type === 'myself'
                ? $this->shares * ($this->investmentOpportunity?->expected_return_amount_by_myself ?? 0)
                : null,
            'expected_net_return_by_myself' => $this->investment_type === 'myself'
                ? $this->shares * ($this->investmentOpportunity?->expected_net_return_by_myself ?? 0)
                : null,
            'expected_return_by_authorize' => $this->investment_type === 'authorize'
                ? $this->shares * ($this->investmentOpportunity?->expected_return_amount_by_authorize ?? 0)
                : null,
            'expected_net_return_by_authorize' => $this->investment_type === 'authorize'
                ? $this->shares * ($this->investmentOpportunity?->expected_net_return_by_authorize ?? 0)
                : null,
        ];
    }
}
