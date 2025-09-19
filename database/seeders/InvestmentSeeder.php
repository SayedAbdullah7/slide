<?php

namespace Database\Seeders;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use App\Models\InvestorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvestmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('ar_SA');

        // Get all available opportunities and investor profiles
        $opportunities = InvestmentOpportunity::all();
        $investorProfiles = InvestorProfile::all();
        // $users = User::all();

        if ($opportunities->isEmpty() || $investorProfiles->isEmpty() ) {
            $this->command->warn('Please seed InvestmentOpportunity, InvestorProfile, and User before running this seeder.');
            return;
        }
        if(Investment::count() > 0) {
            $this->command->warn('Investments already seeded.');
            return;
        }

        // Clear existing investments
        // Investment::whereNotNull('id')->delete();

        // Create 100 investment records
        for ($i = 0; $i < 100; $i++) {
            $opportunity = $opportunities->random();
            $investorProfile = $investorProfiles->random();
            $user = $investorProfile->user;

            // Calculate investment details
            $minInvestment = $opportunity->min_investment;
            $maxInvestment = $opportunity->max_investment ?? $opportunity->target_amount;
            $investmentAmount = $faker->randomFloat(2, $minInvestment, min($maxInvestment, 50000));

            // Calculate shares based on price per share
            $shares = floor($investmentAmount / $opportunity->price_per_share);

            // Ensure we don't exceed available shares
            if ($shares > $opportunity->available_shares) {
                $shares = $opportunity->available_shares;
                $investmentAmount = $shares * $opportunity->price_per_share;
            }

            // Skip if no shares available
            if ($shares <= 0) {
                continue;
            }

            $investmentDate = $faker->dateTimeBetween(
                $opportunity->offering_start_date ?? '-3 months',
                $opportunity->offering_end_date ?? 'now'
            );

            $status = $faker->randomElement(['active', 'completed', 'cancelled', 'pending']);

            // Adjust status based on opportunity status
            if ($opportunity->status === 'completed') {
                $status = $faker->randomElement(['completed', 'active']);
            } elseif ($opportunity->status === 'suspended') {
                $status = $faker->randomElement(['cancelled', 'pending']);
            }

            Investment::create([
                'user_id' => $user->id,
                'investor_id' => $investorProfile->id,
                'opportunity_id' => $opportunity->id,
                'shares' => $shares,
                'amount' => $investmentAmount,
                'investment_type' => $faker->randomElement(['myself', 'authorize']),
                'status' => $status,
                'investment_date' => $investmentDate,
                'created_at' => $investmentDate,
                'updated_at' => now(),
            ]);

            // Update reserved shares in the opportunity
            $opportunity->reserveShares($shares);
        }

        $this->command->info('✅ Seeded 100 investment records.');
    }
}
