<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyOption extends Model
{
    /** @use HasFactory<\Database\Factories\SurveyOptionFactory> */
    use HasFactory;

    protected $fillable = ['survey_question_id','option_text','is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function question() {
        return $this->belongsTo(SurveyQuestion::class);
    }
}
