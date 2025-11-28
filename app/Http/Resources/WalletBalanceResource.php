<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletBalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'balance' => $this->resource['balance'],
            'formatted_balance' => number_format($this->resource['balance'], 2),
            'currency' => 'USD', // You can make this configurable
            'profile_type' => $this->resource['profile_type'],
            'profile_id' => $this->resource['profile_id'],
            'wallet_exists' => $this->resource['wallet_exists'] ?? true,
            'last_updated' => now()->toISOString(),
            'formatted_last_updated' => now()->format('Y-m-d H:i:s'),
        ];
    }
}

