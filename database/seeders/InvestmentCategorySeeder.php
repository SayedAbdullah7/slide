<?php

namespace Database\Seeders;

use App\Models\InvestmentCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvestmentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'العقارات', 'description' => 'استثمارات في الأراضي والمباني والمجمعات السكنية.'],
            ['name' => 'التكنولوجيا', 'description' => 'شركات ناشئة، برامج، ذكاء اصطناعي، وحلول تقنية.'],
            ['name' => 'الزراعة', 'description' => 'مزارع، إنتاج غذائي، وتكنولوجيا زراعية.'],
            ['name' => 'الطاقة', 'description' => 'الطاقة المتجددة، النفط، الغاز، والبنية التحتية للطاقة.'],
            ['name' => 'الصحة', 'description' => 'مستشفيات، شركات أدوية، وتكنولوجيا صحية.'],
            ['name' => 'التمويل', 'description' => 'بنوك، شركات تأمين، وخدمات مالية وتقنية مالية (FinTech).'],
            ['name' => 'البنية التحتية', 'description' => 'طرق، جسور، مشاريع نقل ولوجستيات.'],
            ['name' => 'التعليم', 'description' => 'مدارس، منصات تعليمية، وخدمات تعليمية متنوعة.'],
        ];

        foreach ($categories as $category) {
            InvestmentCategory::firstOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
