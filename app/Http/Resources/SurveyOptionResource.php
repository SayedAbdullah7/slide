<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyOptionResource extends JsonResource
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
            'option_text' => $this->option_text,
//            'is_active' => (bool) $this->is_active,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
        ];
    }
}
