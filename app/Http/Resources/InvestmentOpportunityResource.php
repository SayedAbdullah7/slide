<?php

namespace App\Http\Resources;

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
            'risk_level' => $this->risk_level,
            'target_amount' => $this->target_amount,
            'price_per_share' => $this->price_per_share,
            'reserved_shares' => $this->reserved_shares,
            'available_shares' => $this->available_shares,
            'completion_rate' => $this->completion_rate,
            'is_fundable' => $this->is_fundable,

            // ✅ هنا الإضافات المطلوبة
            'expected_return_amount_by_myself' => $this->expected_return_amount,
            'expected_net_return_by_myself' => $this->expected_return_amount,

            'expected_return_amount_by_authorize' => $this->expected_return_amount,
            'expected_net_return_by_authorize' => $this->expected_net_return,

            'expected_return_amount' => $this->expected_return_amount,
            'expected_net_return' => $this->expected_net_return,
            'investment_duration' => $this->investment_duration,
            'profit_distribution_date' => ($this->profit_distribution_date ?? \Carbon\Carbon::parse('2025-01-01'))->toISOString(),

            'cover_image' => $this->getFirstMediaUrl('cover'),
            'terms_url' => $this->getFirstMediaUrl('terms'),
            'summary_url' => $this->getFirstMediaUrl('summary'),
            'show' => $this->show,
            'show_date' => $this->show_date,
            'offering_start_date' => $this->offering_start_date,
            'offering_end_date' => $this->offering_end_date,
            'created_at' => $this->created_at,
        ];
    }
}
