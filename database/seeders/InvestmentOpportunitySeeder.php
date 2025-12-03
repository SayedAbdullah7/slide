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
//        return;
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

        // إنشاء 10 مشاريع حالية
        $this->command->info('🔄 Creating 10 current opportunities...');
        for ($i = 0; $i < 10; $i++) {
            $targetAmount = 2000000; // 2 مليون ريال
            $pricePerShare = 500; // 500 ريال للسهم
            $totalShares = floor($targetAmount / $pricePerShare); // 4000 سهم
            $reservedShares = 0; // لا يوجد أسهم محجوزة

            $now = now();
            $offeringStart = $now->copy()->subDays(30); // بدأ منذ 30 يوم
            $offeringEnd = $now->copy()->addMonths(6); // ينتهي بعد 6 أشهر
            $showDate = $now->copy()->subDays(45); // ظهر قبل 45 يوم
            $profitDistributionDate = $offeringEnd->copy()->addMonths(5); // توزيع الأرباح بعد 5 اشهر من الانتهاء

            // Calculate expected returns per share
            $expectedReturnByAuthorize = $faker->randomFloat(2, 100, 300); // عائد متوقع للسهم
            $expectedNetReturnByAuthorize = $expectedReturnByAuthorize * $faker->randomFloat(2, 0.7, 0.85);

            $opportunity = InvestmentOpportunity::create([
                'name' => 'مشروع ' . $faker->company . ' - حالي #' . ($i + 1),
                'location' => $faker->city,
                'description' => $faker->randomElement($descriptions),
                'category_id' => $categories->random()->id,
                'owner_profile_id' => $owners->random()->id,
                'status' => 'open', // مشروع حالي ومفتوح
                'risk_level' => $faker->randomElement(['low', 'medium', 'high']),
                'target_amount' => $targetAmount,
                'share_price' => $pricePerShare,
                'reserved_shares' => $reservedShares,
                'investment_duration' => 24, // سنتين
                'expected_profit' => $expectedReturnByAuthorize,
                'expected_net_profit' => $expectedNetReturnByAuthorize,
                'shipping_fee_per_share' => $faker->randomFloat(2, 10, 30),
                'min_investment' => 10, // حد أدنى 10 أسهم
                'max_investment' => 1000, // حد أقصى 1000 سهم
                'fund_goal' => $faker->randomElement(['growth', 'stability', 'income']),
                'guarantee' => $faker->randomElement(['real_estate_mortgage', 'bank_guarantee', 'personal_guarantee', 'asset_pledge', 'insurance_policy']),
                'show' => true,
                'show_date' => $showDate,
                'offering_start_date' => $offeringStart,
                'offering_end_date' => $offeringEnd,
                'profit_distribution_date' => $profitDistributionDate,
                'expected_delivery_date' => $offeringEnd->copy()->addDays(90),
                'expected_distribution_date' => $profitDistributionDate,
                'all_merchandise_delivered' => false,
                'all_returns_distributed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->addMediaToOpportunity($opportunity, $termsPath, $summaryPath, $coversFolder, $faker);
            $this->command->info('✅ Created current opportunity: ' . $opportunity->name);
        }

        // إنشاء 5 مشاريع قادمة (كل واحد ينزل بعد 12 ساعة)
        $this->command->info('🔄 Creating 5 upcoming opportunities...');
        for ($i = 0; $i < 5; $i++) {
            $targetAmount = 2000000; // 2 مليون ريال
            $pricePerShare = 500; // 500 ريال للسهم
            $totalShares = floor($targetAmount / $pricePerShare); // 4000 سهم
            $reservedShares = 0; // لا يوجد أسهم محجوزة

            $now = now();
            // كل مشروع يبدأ بعد 12 ساعة من اللي قبله
            $offeringStart = $now->copy()->addHours(12 * ($i + 1));
            $offeringEnd = $offeringStart->copy()->addMonths(6);
            $showDate = $offeringStart->copy()->subDays(15); // يظهر قبل البداية ب 15 يوم
            $profitDistributionDate = $offeringEnd->copy()->addMonths(12);

            // Calculate expected returns per share
            $expectedReturnByAuthorize = $faker->randomFloat(2, 100, 300);
            $expectedNetReturnByAuthorize = $expectedReturnByAuthorize * $faker->randomFloat(2, 0.7, 0.85);

            $opportunity = InvestmentOpportunity::create([
                'name' => 'مشروع ' . $faker->company . ' - قادم #' . ($i + 1),
                'location' => $faker->city,
                'description' => $faker->randomElement($descriptions),
                'category_id' => $categories->random()->id,
                'owner_profile_id' => $owners->random()->id,
                'status' => 'upcoming', // مشروع قادم
                'risk_level' => $faker->randomElement(['low', 'medium', 'high']),
                'target_amount' => $targetAmount,
                'share_price' => $pricePerShare,
                'reserved_shares' => $reservedShares,
                'investment_duration' => 24,
                'expected_profit' => $expectedReturnByAuthorize,
                'expected_net_profit' => $expectedNetReturnByAuthorize,
                'shipping_fee_per_share' => $faker->randomFloat(2, 10, 30),
                'min_investment' => 10, // حد أدنى 10 أسهم
                'max_investment' => 1000, // حد أقصى 1000 سهم
                'fund_goal' => $faker->randomElement(['growth', 'stability', 'income']),
                'guarantee' => $faker->randomElement(['real_estate_mortgage', 'bank_guarantee', 'personal_guarantee', 'asset_pledge', 'insurance_policy']),
                'show' => true,
                'show_date' => $showDate,
                'offering_start_date' => $offeringStart,
                'offering_end_date' => $offeringEnd,
                'profit_distribution_date' => $profitDistributionDate,
                'expected_delivery_date' => $offeringEnd->copy()->addDays(90),
                'expected_distribution_date' => $profitDistributionDate,
                'all_merchandise_delivered' => false,
                'all_returns_distributed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->addMediaToOpportunity($opportunity, $termsPath, $summaryPath, $coversFolder, $faker);
            $hours = 12 * ($i + 1);
            $this->command->info('✅ Created upcoming opportunity (starts after ' . $hours . ' hours): ' . $opportunity->name);
        }

        $this->command->info('✅ Seeded 10 current and 5 upcoming investment opportunities with media files.');

        // Update all opportunities with dynamic status based on their dates
        $this->command->info('🔄 Updating opportunity statuses based on dates...');
        $this->updateOpportunityStatuses();
    }

    /**
     * Update all opportunities with dynamic status based on their dates
     */
    protected function updateOpportunityStatuses(): void
    {
        $opportunities = InvestmentOpportunity::all();
        $updated = 0;

        foreach ($opportunities as $opportunity) {
            if ($opportunity->shouldUpdateStatus()) {
                $oldStatus = $opportunity->status;
                $opportunity->updateDynamicStatus();
                $newStatus = $opportunity->status;

                if ($oldStatus !== $newStatus) {
                    $updated++;
                }
            }
        }

        $this->command->info("✅ Updated {$updated} opportunities with dynamic statuses.");
    }

    /**
     * إضافة ملفات الميديا لفرصة استثمارية
     */
    protected function addMediaToOpportunity($opportunity, $termsPath, $summaryPath, $coversFolder, $faker): void
    {
        // إضافة ملفات الميديا
        $opportunity->addMedia($termsPath)->preservingOriginal()->toMediaCollection('terms');
        $opportunity->addMedia($summaryPath)->preservingOriginal()->toMediaCollection('summary');

        // إضافة صورة المالك
        $ownerAvatarPath = $this->getRandomImageFromFolder(storage_path('app/seeder_files/avatars'));
        if ($ownerAvatarPath) {
            $opportunity->addMedia($ownerAvatarPath)->preservingOriginal()->toMediaCollection('owner_avatar');
        }

        // صور غلاف عشوائية من المجلد (من 2 إلى 5 صور)
        for ($x = 0; $x < $faker->numberBetween(2, 5); $x++) {
            $coverPath = $this->getRandomImageFromFolder($coversFolder);
            if ($coverPath) {
                $opportunity->addMedia($coverPath)->preservingOriginal()->toMediaCollection('cover');
            }
        }
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
