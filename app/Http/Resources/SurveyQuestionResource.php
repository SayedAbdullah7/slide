<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyQuestionResource extends JsonResource
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
            'question' => $this->question,
            'type' => $this->type,
//            'is_active' => (bool) $this->is_active,
            'options' => SurveyOptionResource::collection($this->whenLoaded('options')),
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
        ];
    }
}
