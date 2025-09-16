<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $survey_question_id
 * @property int|null $survey_option_id
 * @property string|null $answer_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SurveyOption|null $option
 * @property-read \App\Models\SurveyQuestion|null $question
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\SurveyAnswerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyAnswer whereAnswerText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyAnswer whereSurveyOptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyAnswer whereSurveyQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyAnswer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyAnswer whereUserId($value)
 * @mixin \Eloquent
 */
class SurveyAnswer extends Model
{
    /** @use HasFactory<\Database\Factories\SurveyAnswerFactory> */
    use HasFactory;

    protected $fillable = ['user_id','survey_question_id','survey_option_id','answer_text'];

    public function question() {
        return $this->belongsTo(SurveyQuestion::class);
    }

    public function option() {
        return $this->belongsTo(SurveyOption::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
