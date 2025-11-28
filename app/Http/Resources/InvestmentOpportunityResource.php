<?php

namespace App\Http\Resources;

use App\GuaranteeTypeEnum;
use App\FundGoalEnum;
use App\InvestmentStatusEnum;
use App\RiskLevelEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

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
            'owner' => [
                'name' => $this->ownerProfile?->user?->full_name,
                'photo' => $this->getFirstMediaUrl('owner_avatar') ?? null,

            ],
            // 'status' => $this->status,
            //in arabic
            'status' => InvestmentStatusEnum::label($this->status),
            // 'status' => [
            //     'value' => $this->status,
            //     'text' => InvestmentStatusEnum::label($this->status),
            //     'color' => InvestmentStatusEnum::color($this->status),
            // ],
            'risk_level' => [
                'text' => RiskLevelEnum::text($this->risk_level),
                'color' => RiskLevelEnum::color($this->risk_level),
            ],
            'target_amount' => $this->target_amount,
            'price_per_share' => $this->share_price,
            'total_reserved_shares' => $this->reserved_shares,
            'available_shares_for_investment' => $this->available_shares,
            'funding_progress_percentage' => $this->completion_rate,
            'is_available_for_investment' => $this->is_fundable,

            'minimum_shares_to_invest' => (string) $this->min_investment,
            'maximum_shares_to_invest' => (string) $this->max_investment,

            // Investment type availability
            // 'allowed_investment_types' => $this->allowed_investment_types ?? 'both',
            'investment_type_availability' => $this->getAllowedInvestmentTypesArray(),

            // Investment goal
            'investment_purpose' => $this->fund_goal ? FundGoalEnum::label($this->fund_goal) : null,

            // ✅ Investment returns and fees
            'expected_profit_per_share' => (string) $this->expected_profit,
            'expected_net_profit_per_share' => (string) $this->expected_net_profit,
            'expected_profit_percentage' => (string) $this->expectedProfitPercentage(),
            'expected_net_profit_percentage' => (string) $this->expectedNetProfitPercentage(),

            'shipping_and_service_fee_per_share' => (string) $this->shipping_fee_per_share,
            'shipping_and_service_fee_percentage' => (string) $this->shippingFeePercentage(),

            // ✅ New fields for actual returns and distribution
            'actual_profit_per_share' => (string) $this->actual_profit_per_share,
            'actual_net_profit_per_share' => (string) $this->actual_net_profit_per_share,
            'total_distributed_profit' => (string) $this->distributed_profit,
            'is_all_merchandise_delivered' => $this->all_merchandise_delivered,
            'is_all_returns_distributed' => $this->all_returns_distributed,
            'expected_merchandise_delivery_date' => $this->expected_delivery_date?->toISOString(),
            'expected_profit_distribution_date' => $this->expected_distribution_date?->toISOString(),

            'investment_duration_in_days' => $this->investment_duration,
            'final_profit_distribution_date' => ($this->profit_distribution_date ?? \Carbon\Carbon::parse('2025-01-01'))->toISOString(),

            'main_cover_image_url' => $this->getFirstMediaUrl('cover') ?: $this->getDefaultCoverImageUrl(), // Main cover image (first one) or default
            'all_cover_images_urls' => $this->getMedia('cover')->count() > 0
                ? $this->getMedia('cover')->map(function ($media) {
                    return $media->getUrl();
                })
                : [$this->getDefaultCoverImageUrl()], // Default image if no cover images
            // guarantee from model field
            'guarantee_type' => $this->guarantee ? GuaranteeTypeEnum::label($this->guarantee) : null,
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
            'terms_and_conditions' => [
                'document_url' => $this->getFirstMediaUrl('terms') ?: null,
                // in mb with 2 decimal places. Default is null if no file.
                'file_size_mb' => $this->getFirstMedia('terms') ? number_format($this->getFirstMedia('terms')->size / 1024 / 1024, 2) : null,
            ],
            'summary' => [
                'url' => $this->getFirstMediaUrl('summary') ?: null,
                'size' => $this->getFirstMedia('summary') ? number_format($this->getFirstMedia('summary')->size / 1024 / 1024, 2) : null,
            ],
            'is_visible_to_users' => $this->show,
            'visibility_start_date' => $this->show_date,
            'investment_acceptance_start_date' => $this->offering_start_date,
            'investment_acceptance_end_date' => $this->offering_end_date,
            'created_at' => $this->created_at,

            // Check if the authenticated investor has saved this opportunity
            'saved' => $this->getSavedStatus(),

            // Investment information (when loaded) - use InvestmentResource to avoid code duplication
            // 'my_investment' => $this->whenLoaded('investment', function () {
            //     // Use InvestmentResource to transform the investment data
            //     return new InvestmentResource($this->investment);
            // }),
            'my_investment' => $this->whenLoaded('investments', function () {
                // first investment
                return new InvestmentResource($this->investments->first());
            }),
            //investments count when loaded

            // 'investments_count' => $this->whenLoaded('investments', function () {
            //     return $this->investments->count();
            // }),
            'investments_count' => $this->investments_count??0,
        ];
    }

    /**
     * Get default cover image URL
     * الحصول على رابط الصورة الافتراضية للغلاف
     */
    private function getDefaultCoverImageUrl(): string
    {
        // return asset('images/default-cover.jpg');
        return asset('images/cover2.jpeg');
    }

    /**
     * Check if the authenticated investor has saved this opportunity
     * Uses eager loaded data to avoid N+1 queries
     */
    private function getSavedStatus(): bool
    {
        $user = Auth::user();

        if (!$user || !$user->investorProfile) {
            return false;
        }

        // Use eager loaded savedOpportunities relationship if available
        if ($this->resource->relationLoaded('savedOpportunities')) {
            return $this->resource->savedOpportunities->isNotEmpty();
        }

        // Fallback to query if not eager loaded (shouldn't happen in normal flow)
        return $this->resource->savedOpportunities()
            ->where('investor_profile_id', $user->investorProfile->id)
            ->exists();
    }
}
