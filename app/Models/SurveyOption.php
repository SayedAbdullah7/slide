<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $survey_question_id
 * @property string $option_text
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SurveyQuestion|null $question
 * @method static \Database\Factories\SurveyOptionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyOption query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyOption whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyOption whereOptionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyOption whereSurveyQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyOption whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
