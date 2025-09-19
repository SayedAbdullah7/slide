<?php

namespace Database\Seeders;

use App\Models\InvestmentOpportunity;
use App\Models\InvestmentCategory;
use App\Models\OwnerProfile;
use Illuminate\Database\Seeder;

class InvestmentOpportunitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(InvestmentOpportunity::count() > 0) {
            $this->command->warn('InvestmentOpportunities already seeded.');
            return;
        }
        $descriptions = [
            // قصيرة
            'فرصة استثمارية تحقق عوائد مجزية.',
            'مشروع عقاري بموقع استراتيجي.',
            'استثمار مضمون في سوق متنامٍ.',
            'شركة ناشئة بتقنية واعدة.',
            'فرصة نمو سريعة في قطاع مربح.',
            'مبادرة مدعومة من برامج حكومية.',
            'استثمار بتكلفة منخفضة وعوائد عالية.',
            'مشروع طاقة نظيفة بمردود مستدام.',
            'شركة واعدة في قطاع التجارة الإلكترونية.',
            'استثمار مستقر في قطاع التعليم الخاص.',

            // متوسطة
            'مشروع عقاري في موقع نابض بالحياة، يقدم وحدات سكنية ذات تصميم عصري وإمكانيات نمو مستقبلية.',
            'فرصة استثمارية في شركة تقنية تقدم حلولًا ذكية للمؤسسات الصغيرة والمتوسطة.',
            'مبادرة استثمارية جديدة تركز على الأغذية الصحية وتستهدف السوق المحلي سريع النمو.',
            'شركة ناشئة في قطاع التقنية المالية تقدم خدمات رقمية مبتكرة في مجال الدفع الإلكتروني.',
            'فرصة للاستثمار في مزرعة عضوية متطورة تعتمد تقنيات الزراعة الذكية وتستهدف التصدير.',
            'استثمار في مشروع لوجستي يربط بين المدن الرئيسية عبر شبكة توزيع حديثة.',
            'مشروع تطوير منتجع سياحي في منطقة جذابة، مع خطة تشغيل تضمن العوائد على المدى المتوسط.',
            'شركة واعدة تقدم خدمات تعليم إلكتروني، ولديها قاعدة مستخدمين متنامية.',
            'فرصة استثمارية في قطاع التجارة الإلكترونية تستهدف شريحة المستهلكين الشباب.',
            'مبادرة لدعم المشاريع الصغيرة من خلال حاضنة أعمال تركز على الابتكار المحلي.',

            // طويلة
            'يتيح هذا المشروع للمستثمرين فرصة الدخول إلى قطاع التقنية عبر منصة مبتكرة تقدم حلولًا رقمية للشركات الناشئة. المشروع في مرحلة نمو متقدمة، ويستهدف أسواقًا محلية وإقليمية، ويتميز بفريق قيادي متمرس ورؤية استراتيجية واضحة للنمو المستدام.',
            'فرصة استثمارية عقارية في أحد الأحياء السكنية الأسرع نموًا، تقدم وحدات سكنية عصرية بأسعار منافسة وخطة تمويل مرنة. المشروع مرخص بالكامل ويمر بمرحلة التنفيذ الفعلي، مما يجعله خيارًا موثوقًا للمستثمرين الباحثين عن دخل ثابت.',
            'شركة ناشئة في قطاع الطاقة المتجددة تقدم حلولًا مبتكرة للطاقة الشمسية للمنازل والشركات. المشروع مدعوم من جهات تمويل محلية ودولية، ويستهدف التوسع في عدة مناطق خلال السنوات الثلاث القادمة.',
            'استثمار في مصنع متطور لإنتاج الأغذية المجمدة، يلتزم بأعلى معايير الجودة ويستهدف الأسواق المحلية والخليجية. المشروع يشمل خط إنتاج عالي التقنية، ومستودعات حديثة، ونظام توزيع متكامل.',
            'فرصة لدخول قطاع النقل الذكي من خلال تطبيق يوفر حلول مشاركة المركبات داخل المدن. يتميز التطبيق بقاعدة مستخدمين نشطة، وتحالفات مع مزودي خدمات محليين، بالإضافة إلى خطة تسويق واسعة النطاق.',
            'فرصة استثمارية فريدة في مشروع متعدد الاستخدامات يجمع بين الوحدات السكنية والمرافق التجارية والترفيهية. يقع المشروع في قلب المدينة، ويتميز بتصميم معماري عصري وبنية تحتية متكاملة.',
            'مشروع مبتكر في قطاع الخدمات الصحية الرقمية يهدف إلى تحسين الوصول إلى الرعاية الصحية من خلال تطبيق ذكي يربط المرضى بالأطباء والاستشاريين. يتمتع المشروع بدعم حكومي وشراكات استراتيجية في القطاع الطبي.',
            'استثمار في شركة تكنولوجيا تعليم تعمل على تطوير منصات تعلم تفاعلية للطلاب والمعلمين، وتستخدم تقنيات الذكاء الاصطناعي لتحسين تجربة التعلم ومتابعة الأداء الأكاديمي.',
            'مبادرة استثمارية تستهدف تطوير سلسلة مطاعم تقدم وجبات صحية وسريعة، تعتمد على مكونات عضوية، وتتبنى نموذج تشغيل مرن يدعم التوسع السريع عبر الامتياز التجاري.',
            'شركة ناشئة تركز على التجارة الاجتماعية، تربط بين البائعين والمستهلكين من خلال تطبيق يعتمد على المحتوى والتفاعل. تمتلك الشركة فريقًا متمكنًا، وقاعدة جماهيرية تنمو بسرعة في عدة أسواق.',
        ];

        $faker = \Faker\Factory::create('ar_SA');

        // التأكد من وجود بيانات التصنيفات وأصحاب المشاريع
        $categories = InvestmentCategory::all();
        $owners = OwnerProfile::all();

        // drop old data if exists
        // InvestmentOpportunity::whereNotNull('id')->delete();

        if ($categories->isEmpty() || $owners->isEmpty()) {
            $this->command->warn('Please seed InvestmentCategory and OwnerProfile before running this seeder.');
            return;
        }

        // مسارات الملفات الثابتة
        $termsPath = storage_path('app/seeder_files/sample_terms.pdf');
        $summaryPath = storage_path('app/seeder_files/sample_summary.pdf');
        $coversFolder = storage_path('app/seeder_files/covers');

        if (!file_exists($termsPath) || !file_exists($summaryPath)) {
            $this->command->error('Sample PDF files missing in storage/app/seeder_files/');
            return;
        }

        if (!$this->hasImageFiles($coversFolder)) {
            $this->command->error('No cover images found in ' . $coversFolder);
            return;
        }

        // Increase number of records to 50
        for ($i = 0; $i < 50; $i++) {
            $targetAmount = $faker->randomFloat(2, 100000, 2000000);
            $pricePerShare = $faker->randomFloat(2, 100, 2000);
            $totalShares = floor($targetAmount / $pricePerShare);
            $reservedShares = $faker->numberBetween(0, $totalShares);

            $offeringStart = $faker->dateTimeBetween('-6 months', 'now');
            $offeringEnd = (clone $offeringStart)->modify('+' . rand(1, 12) . ' months');
            $showDate = $faker->dateTimeBetween('-3 months', 'now');
            $profitDistributionDate = (clone $offeringEnd)->modify('+' . rand(6, 24) . ' months');

            // Calculate expected returns based on price per share
            $expectedReturnByMyself = $pricePerShare * $faker->randomFloat(2, 0.15, 0.35); // 15-35% return
            $expectedNetReturnByMyself = $expectedReturnByMyself * $faker->randomFloat(2, 0.7, 0.9); // 70-90% of gross return
            $expectedReturnByAuthorize = $pricePerShare * $faker->randomFloat(2, 0.20, 0.40); // 20-40% return
            $expectedNetReturnByAuthorize = $expectedReturnByAuthorize * $faker->randomFloat(2, 0.6, 0.8); // 60-80% of gross return

            $opportunity = InvestmentOpportunity::create([
                'name' => 'مشروع ' . $faker->company,
                'location' => $faker->city,
                'description' => $faker->randomElement($descriptions),
                'category_id' => $categories->random()->id,
                'owner_profile_id' => $owners->random()->id,
                'status' => $faker->randomElement(['open', 'completed', 'suspended']),
                'risk_level' => $faker->randomElement(['low', 'medium', 'high']),
                'target_amount' => $targetAmount,
                'price_per_share' => $pricePerShare,
                'reserved_shares' => $reservedShares,
                'shipping_and_service_fee' => $faker->randomFloat(2, 5, 20),
                'investment_duration' => $faker->numberBetween(6, 60),
                'expected_return_amount_by_myself' => $expectedReturnByMyself,
                'expected_net_return_by_myself' => $expectedNetReturnByMyself,
                'expected_return_amount_by_authorize' => $expectedReturnByAuthorize,
                'expected_net_return_by_authorize' => $expectedNetReturnByAuthorize,
                'min_investment' => $faker->randomFloat(2, 100, 1000),
                'max_investment' => $faker->randomFloat(2, 10000, 100000),
                'fund_goal' => $faker->randomElement(['growth', 'stability', 'income']),
                'show' => $faker->boolean(80),
                'show_date' => $showDate,
                'offering_start_date' => $offeringStart,
                'offering_end_date' => $offeringEnd,
                'profit_distribution_date' => $profitDistributionDate,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // إضافة ملفات الميديا
            $opportunity->addMedia($termsPath)->preservingOriginal()->toMediaCollection('terms');
            $opportunity->addMedia($summaryPath)->preservingOriginal()->toMediaCollection('summary');

            // صور غلاف عشوائية من المجلد
            // add from 2 to 5 images
            for ($x = 0; $x < $faker->numberBetween(2, 5); $x++) {
                $coverPath = $this->getRandomImageFromFolder($coversFolder);
                $opportunity->addMedia($coverPath)->preservingOriginal()->toMediaCollection('cover');
            }
            //         $coverPath = $this->getRandomImageFromFolder($coversFolder);
            //         $opportunity->addMedia($coverPath)->preservingOriginal()->toMediaCollection('cover');
            // }
            $this->command->info('✅ Seeded investment opportunity ' . $opportunity->name . ' number ' . $i);
        }

        $this->command->info('✅ Seeded 50 investment opportunities with media files.');
    }

    /**
     * تجلب مسار صورة عشوائية من مجلد معين
     */
    protected function getRandomImageFromFolder(string $folderPath): ?string
    {
        $files = glob($folderPath . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE);

        if (empty($files)) {
            return null;
        }

        return $files[array_rand($files)];
    }

    /**
     * تحقق إذا يوجد أي صورة في المجلد
     */
    protected function hasImageFiles(string $folderPath): bool
    {
        $files = glob($folderPath . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
        return !empty($files);
    }
}
