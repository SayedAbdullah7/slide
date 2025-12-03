<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhoneSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
//            'id'         => $this->id,
//            'phone'      => $this->phone,
            'token'      => $this->token,
            'expires_at' => $this->expires_at?->toDateTimeString(),
//            'is_expired' => $this->isExpired(),
//            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
