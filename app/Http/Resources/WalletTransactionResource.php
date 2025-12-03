<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
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
            'uuid' => $this->uuid,
            'type' => $this->type,
            'amount' => $this->amount,
            'formatted_amount' => number_format($this->amount, 2),
            'confirmed' => $this->confirmed,
            'meta' => $this->meta ?? [],
            'description' => $this->meta['description'] ?? null,
            'reference' => $this->meta['reference'] ?? null,
            'created_at' => $this->created_at?->toISOString(),
            'formatted_date' => $this->created_at?->format('Y-m-d H:i:s'),
            'human_date' => $this->created_at?->diffForHumans(),

            // Wallet information
            'wallet' => [
                'id' => $this->wallet->id ?? null,
                'name' => $this->wallet->name ?? null,
                'slug' => $this->wallet->slug ?? null,
                'balance' => $this->wallet->balance ?? null,
                'formatted_balance' => $this->wallet->balance ? number_format($this->wallet->balance, 2) : null,
            ],

            // Profile information from meta
            'profile_info' => [
                'user_id' => $this->meta['user_id'] ?? null,
                'profile_type' => $this->meta['profile_type'] ?? null,
            ],

            // Transfer information (if applicable)
            'transfer_info' => [
                'from_user_id' => $this->meta['from_user_id'] ?? null,
                'from_profile_type' => $this->meta['from_profile_type'] ?? null,
                'to_profile_type' => $this->meta['to_profile_type'] ?? null,
                'to_profile_id' => $this->meta['to_profile_id'] ?? null,
            ],
        ];
    }
}

