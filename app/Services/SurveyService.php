<?php
namespace App\Services;

use App\Models\{SurveyQuestion, SurveyOption, SurveyAnswer};
use Illuminate\Validation\ValidationException;

class SurveyService
{
    public function questions()
    {
        $questions = SurveyQuestion::with(['options' => function ($query) {
            $query->where('is_active', true);
        }])
            ->where('is_active', true)
            ->get();
        return $questions;
//        return $questions = SurveyQuestion::where('is_active', true)->get();
    }
    public function validateAnswers(array $answers): void
    {
        $questions = SurveyQuestion::with('options')->get();

        // تحقق إن المستخدم جاوب على كل الأسئلة
        if (count($answers) !== $questions->count()) {
            throw ValidationException::withMessages([
                'answers' => 'يجب الإجابة على جميع الأسئلة',
            ]);
        }

        foreach ($questions as $q) {
            $ans = collect($answers)->firstWhere('question_id', $q->id);

            if (!$ans) {
                throw ValidationException::withMessages([
                    'answers' => "السؤال {$q->id} لم تتم الإجابة عليه",
                ]);
            }

            // لو السؤال اختياري (options)
            if ($q->options->count() > 0) {
                if (empty($ans['option_id']) || !$q->options->pluck('id')->contains($ans['option_id'])) {
                    throw ValidationException::withMessages([
                        'answers' => "الإجابة على السؤال {$q->id} غير صحيحة",
                    ]);
                }
            }
            // لو السؤال نصي
            else {
                if (empty($ans['answer_text'])) {
                    throw ValidationException::withMessages([
                        'answers' => "يجب إدخال إجابة نصية للسؤال {$q->id}",
                    ]);
                }
            }
        }
    }

    public function saveAnswers($user, array $answers)
    {
        foreach ($answers as $ans) {
            SurveyAnswer::updateOrCreate(
                [
                    'user_id'            => $user->id,
                    'survey_question_id' => $ans['question_id'],
                ],
                [
                    'survey_option_id' => $ans['option_id'] ?? null,
                    'answer_text'      => $ans['answer_text'] ?? null,
                ]
            );
        }
    }
}
