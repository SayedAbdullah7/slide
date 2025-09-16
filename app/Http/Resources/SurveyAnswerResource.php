<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyAnswerResource extends JsonResource
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
            'question' => new SurveyQuestionResource($this->whenLoaded('question')),
            'selected_option' => new SurveyOptionResource($this->whenLoaded('option')),
            'answer_text' => $this->answer_text,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
