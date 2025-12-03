<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvestorProfileResource extends JsonResource
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
            'full_name' => $this->full_name,
            'birth_date' => $this->birth_date,
            'national_id' => $this->national_id,
            // 'has_wallet' => $this->hasWallet(),
            // 'wallet_balance' => $this->getWalletBalance(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
