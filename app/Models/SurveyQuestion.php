<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
