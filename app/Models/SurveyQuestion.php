<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $question
 * @property string $type
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SurveyAnswer> $answers
 * @property-read int|null $answers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SurveyOption> $options
 * @property-read int|null $options_count
 * @method static \Database\Factories\SurveyQuestionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyQuestion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyQuestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyQuestion whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyQuestion whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyQuestion whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SurveyQuestion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SurveyQuestion extends Model
{
    /** @use HasFactory<\Database\Factories\SurveyQuestionFactory> */
    use HasFactory;

    protected $fillable = ['question','type','is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function options() {
        return $this->hasMany(SurveyOption::class);
    }

    public function answers() {
        return $this->hasMany(SurveyAnswer::class);
    }
}
