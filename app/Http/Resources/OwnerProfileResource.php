<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerProfileResource extends JsonResource
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
            'business_name' => $this->business_name,
            'tax_number' => $this->tax_number,
            // 'business_address' => $this->business_address,
            // 'business_phone' => $this->business_phone,
            // 'business_email' => $this->business_email,
            // 'business_website' => $this->business_website,
            // 'business_description' => $this->business_description,
            // 'goal' => $this->goal,
            // 'has_wallet' => $this->hasWallet(),
            // 'wallet_balance' => $this->getWalletBalance(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
