<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvestmentOpportunityRequestResource extends JsonResource
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
            'company_age' => $this->company_age,
            'commercial_experience' => $this->commercial_experience,
            'net_profit_margins' => $this->net_profit_margins,
            'required_amount' => $this->required_amount,
            'formatted_required_amount' => $this->formatted_required_amount,
            'description' => $this->description,
            'guarantee_type' => $this->guarantee_type,
            // 'guarantee_type_label' => $this->guarantee_type_label,
            // 'guarantee_type_color' => $this->guarantee_type_color,
            'status' => $this->status,
            // 'status_label' => $this->status_label,
            // 'status_color' => $this->status_color,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // 'owner_profile' => [
            //     'id' => $this->ownerProfile->id,
            //     'business_name' => $this->ownerProfile->business_name,
            //     'user' => [
            //         'id' => $this->ownerProfile->user->id,
            //         'name' => $this->ownerProfile->user->name,
            //         'email' => $this->ownerProfile->user->email,
            //     ],
            // ],
        ];
    }
}
