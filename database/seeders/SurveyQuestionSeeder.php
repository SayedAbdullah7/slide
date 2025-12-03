<?php

namespace Database\Seeders;

use App\Models\SurveyOption;
use App\Models\SurveyQuestion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SurveyQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (SurveyQuestion::count() > 0) {
            return; // Avoid duplicating questions if they already exist
        }
        $questions = [
            [
                'question' => 'هل أنت موظف حالياً؟',
                'type' => 'boolean',
                'options' => ['نعم', 'لا'],
            ],
            [
                'question' => 'ما المدة المتوقعة لاسترداد أموالك المستثمرة؟',
                'type' => 'single_choice',
                'options' => [
                    'استثمار على المدى القصير (أقل من سنة)',
                    'استثمار على المدى المتوسط (1-5 سنوات)',
                    'استثمار على المدى الطويل (أكثر من 5 سنوات)',
                ],
            ],
            [
                'question' => 'ما المدة المتوقعة لاسترداد أموالك المستثمرة؟',
                'type' => 'single_choice',
                'options' => [
                    'استثمار على المدى القصير (أقل من سنة)',
                    'استثمار على المدى المتوسط (1-5 سنوات)',
                    'استثمار على المدى الطويل (أكثر من 5 سنوات)',
                ],
            ],
            [
                'question' => 'ما المدة المتوقعة لاسترداد أموالك المستثمرة؟',
                'type' => 'single_choice',
                'options' => [
                    'استثمار على المدى القصير (أقل من سنة)',
                    'استثمار على المدى المتوسط (1-5 سنوات)',
                    'استثمار على المدى الطويل (أكثر من 5 سنوات)',
                ],
            ],
        ];

        foreach ($questions as $q) {
            $question = SurveyQuestion::create([
                'question' => $q['question'],
                'type' => $q['type'],
            ]);

            foreach ($q['options'] as $option) {
                SurveyOption::create([
                    'survey_question_id' => $question->id,
                    'option_text' => $option,
                ]);
            }
        }
    }
}
