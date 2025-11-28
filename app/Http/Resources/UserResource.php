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
        $investorProfile = $this->resource->relationLoaded('investorProfile') ? $this->investorProfile : null;
        $ownerProfile = $this->resource->relationLoaded('ownerProfile') ? $this->ownerProfile : null;

        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'is_registered' => $this->is_registered,
            'email_verified_at' => $this->email_verified_at,
            'active_profile_type' => $this->active_profile_type,
            'notifications_enabled' => (bool) $this->notifications_enabled,
            'has_password' => $this->hasPassword(),
            'profiles' => [
                'investor' => [
                    'exists' => (bool) $investorProfile,
                    'data' => $investorProfile ? new InvestorProfileResource($investorProfile) : null,
                ],
                'owner' => [
                    'exists' => (bool) $ownerProfile,
                    'data' => $ownerProfile ? new OwnerProfileResource($ownerProfile) : null,
                ],
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
