<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuaranteeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'type_color' => $this->type_color,
            'name' => $this->name,
            'description' => $this->description,
            'value' => $this->value,
            'formatted_value' => $this->formatted_value,
            'currency' => $this->currency,
            'is_verified' => $this->is_verified,
            'is_expired' => $this->is_expired,
            'expiry_date' => $this->expiry_date?->toDateString(),
            'document_number' => $this->document_number,
            'created_at' => $this->created_at,
        ];
    }
}
