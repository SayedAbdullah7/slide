<?php

namespace App\Http\Resources;

use App\GuaranteeTypeEnum;
use App\RiskLevelEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvestmentOpportunityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'description' => $this->description,
            'category' => $this->category?->name,
            'owner' => $this->ownerProfile?->user?->full_name,
            'status' => $this->status,
            'risk_level' => [
                'text' => RiskLevelEnum::text($this->risk_level),
                'color' => RiskLevelEnum::color($this->risk_level),
            ],
            'target_amount' => $this->target_amount,
            'price_per_share' => $this->price_per_share,
            'reserved_shares' => $this->reserved_shares,
            'available_shares' => $this->available_shares,
            'completion_rate' => $this->completion_rate,
            'is_fundable' => $this->is_fundable,

            'min_investment' => (string) min($this->min_investment,10),
            'max_investment' => (string) min($this->max_investment,50),

            // ✅ هنا الإضافات المطلوبة
            'expected_return_amount_by_myself' =>(string) $this->expected_return_amount_by_myself,
            'expected_net_return_by_myself' =>(string) $this->expected_net_return_by_myself,
            'expected_return_amount_by_myself_percentage' =>(string) $this->expectedReturnAmountByMyselfPercentage(),
            'expected_net_return_by_myself_percentage' => (string) $this->expectedNetReturnByMyselfPercentage(),

            'expected_return_amount_by_authorize' =>(string) $this->expected_return_amount_by_authorize,
            'expected_net_return_by_authorize' =>(string) $this->expected_net_return_by_authorize,
            'expected_return_amount_by_authorize_percentage' =>(string) $this->expectedReturnAmountByAuthorizePercentage(),
            'expected_net_return_by_authorize_percentage' =>(string) $this->expectedNetReturnByAuthorizePercentage(),

            'expected_return_amount' => $this->expected_return_amount_by_authorize,
            'expected_net_return' => $this->expected_net_return_by_authorize,
            'expected_return_amount_percentage' => $this->expectedReturnAmountByAuthorizePercentage(),
            'expected_net_return_percentage' => $this->expectedNetReturnByAuthorizePercentage(),

            'shipping_and_service_fee' => (string) $this->shipping_and_service_fee,
            'shipping_and_service_fee_percentage' => (string) $this->shippingDeliveryCostPercentage(),

            'investment_duration' => $this->investment_duration,
            'profit_distribution_date' => ($this->profit_distribution_date ?? \Carbon\Carbon::parse('2025-01-01'))->toISOString(),

            'cover_image' => $this->getFirstMediaUrl('cover'), // Main cover image (first one)
            'cover_images' => $this->getMedia('cover')->map(function ($media) {
                return $media->getUrl();
            }),
            // guarantee expect one and simple data
            // temp use guarantee enum based on risk level value choose one of enum values
            'guarantee' => GuaranteeTypeEnum::label($this->risk_level == 'low'?'real_estate_mortgage':($this->risk_level == 'medium'?'bank_guarantee':($this->risk_level == 'high'?'personal_guarantee':''))),
            // 'guarantees' => GuaranteeResource::collection($this->whenLoaded('guarantees')),
            // 'guarantees_summary' => [
            //     'has_guarantees' => $this->hasGuarantees(),
            //     'has_verified_guarantees' => $this->hasVerifiedGuarantees(),
            //     'total_guarantee_value' => (string) $this->total_guarantee_value,
            //     'guarantee_coverage_percentage' => (string) $this->guarantee_coverage_percentage,
            //     'guarantees_count' => $this->guarantees->count(),
            //     'verified_guarantees_count' => $this->verified_guarantees->count(),
            // ],
            // show terms and summary with his size in mb
            'terms' => [
                'url' => $this->getFirstMediaUrl('terms'),
                // in mb with 2 decimal places
                'size' => number_format($this->getFirstMedia('terms')->size / 1024 / 1024, 2)
            ],
            'summary' => [
                'url' => $this->getFirstMediaUrl('summary'),
                'size' => number_format($this->getFirstMedia('summary')->size / 1024 / 1024, 2),
            ],
            'show' => $this->show,
            'show_date' => $this->show_date,
            'offering_start_date' => $this->offering_start_date,
            'offering_end_date' => $this->offering_end_date,
            'created_at' => $this->created_at,
        ];
    }
}
