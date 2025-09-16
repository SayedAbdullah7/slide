<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'national_id' => $this->national_id,
            'birth_date' => $this->birth_date,
            'is_active' => $this->is_active,
            'is_registered' => $this->is_registered,
            'email_verified_at' => $this->email_verified_at,
            'profile_type' => $this->active_profile_type,
            'profile' => $this->mergeWhen($this->activeProfile(), fn () => match ($this->active_profile_type) {
                \App\Models\User::PROFILE_INVESTOR => new InvestorProfileResource($this->investorProfile),
                \App\Models\User::PROFILE_OWNER => new OwnerProfileResource($this->ownerProfile),
                default => null,
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
