<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);
        $this->call([
            SetDefaultFilesForInvestmentOpportunitiesSeeder::class,
            BankSeeder::class,
            // Add your other seeders here
            // UserSeeder::class,
            // SurveyQuestionSeeder::class,
            // InvestmentCategorySeeder::class,
            // InvestmentOpportunitySeeder::class,
            // // InvestmentSeeder::class,
            // AdminSeeder::class,
            // ContentSeeder::class,

            // Test and validation seeder (run last)

//            SurveyOptionSeeder::class,
//            SurveyAnswerSeeder::class,
        ]);
    }
}
