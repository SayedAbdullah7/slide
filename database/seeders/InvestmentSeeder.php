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
        return;

        $faker = \Faker\Factory::create('ar_SA');

        // Get all available opportunities and investor profiles
//        $opportunities = InvestmentOpportunity::where('status', 'open')->get();
        $opportunities = InvestmentOpportunity::all(); // Include all opportunities for historical data
        $investorProfiles = InvestorProfile::all();

        if ($opportunities->isEmpty() || $investorProfiles->isEmpty()) {
            $this->command->warn('Please seed InvestmentOpportunity, InvestorProfile, and User before running this seeder.');
            return;
        }

        if (Investment::count() > 0) {
            $this->command->warn('Investments already seeded.');
            return;
        }

        $this->command->info('🚀 Starting investment seeding...');
        $totalInvestments = 0;

        // Create investments for each investor profile
        foreach ($investorProfiles as $investorProfile) {
            $user = $investorProfile->user;

            // Each investor will have 8-15 investments to reach ~100 total
//            $investmentCount = $faker->numberBetween(8, 15);
            $investmentCount = 10;

            $this->command->info("Creating {$investmentCount} investments for investor: {$user->full_name}");

            for ($i = 0; $i < $investmentCount; $i++) {
                // Get all opportunities that can be invested in (including completed ones for historical data)
//                $availableOpportunities = $opportunities->filter(function ($opp) {
//                    return $opp->status === 'open' || $opp->status === 'completed';
//                });
                $availableOpportunities = $opportunities;

                if ($availableOpportunities->isEmpty()) {
                    // If no opportunities available, get any opportunity for historical data
                    $availableOpportunities = InvestmentOpportunity::all();
                }

                if ($availableOpportunities->isEmpty()) {
                    $this->command->warn('No opportunities available for investment.');
                    break;
                }

                $opportunity = $availableOpportunities->random();

                // Calculate investment details
                // min_investment and max_investment are now stored as number of shares
                $minShares = max(1, (int) $opportunity->min_investment);
                $maxShares = $opportunity->max_investment ?
                    min((int) $opportunity->max_investment, $opportunity->available_shares) :
                    $opportunity->available_shares;

                // Ensure we have valid range
//                if ($minShares > $maxShares) {
//                    continue;
//                }

                $shares = $faker->numberBetween($minShares, $maxShares);
                $investmentAmount = $shares * $opportunity->share_price;

                // Skip if no shares available
                if ($shares <= 0) {
                    continue;
                }

                // Generate realistic investment date
                $investmentDate = $this->generateInvestmentDate($opportunity, $faker);

                // Generate realistic status based on opportunity and date
                $status = $this->generateInvestmentStatus($opportunity, $investmentDate, $faker);

                $investmentType = $faker->randomElement(['myself', 'authorize']);

                try {
                    $investmentData = [
                        'user_id' => $user->id,
                        'investor_id' => $investorProfile->id,
                        'opportunity_id' => $opportunity->id,
                        'shares' => $shares,
                        'share_price' => $opportunity->share_price,
                        'total_investment' => $investmentAmount,
                        'total_payment_required' => $investmentType === 'myself' ?
                            $investmentAmount + ($opportunity->shipping_fee_per_share * $shares) :
                            $investmentAmount,
                        'investment_type' => $investmentType,
                        'status' => $status,
                        'investment_date' => $investmentDate,
                        'shipping_fee_per_share' => $opportunity->shipping_fee_per_share,
                        'merchandise_status' => $faker->randomElement(['pending', 'arrived']),
                        'distribution_status' => $faker->randomElement(['pending', 'distributed']),
                        'created_at' => $investmentDate,
                        'updated_at' => now(),
                    ];

                    // Set expected returns based on investment type
                    $investmentData['expected_profit_per_share'] = $opportunity->expected_profit;
                    $investmentData['expected_net_profit_per_share'] = $opportunity->expected_net_profit;

                    if ($investmentType === 'myself') {
                        // Set delivery date for myself investments
                        if ($opportunity->investment_duration) {
                            $investmentData['expected_delivery_date'] = \Carbon\Carbon::parse($investmentDate)->addDays($opportunity->investment_duration);
                        }

                        // Set merchandise arrived date if status is arrived
                        if ($investmentData['merchandise_status'] === 'arrived') {
                            $investmentData['merchandise_arrived_at'] = \Carbon\Carbon::parse($investmentDate)->addDays($faker->numberBetween(30, 180));
                        }
                    } else {
                        // Set distribution date for authorize investments
                        if ($opportunity->expected_distribution_date) {
                            $investmentData['expected_distribution_date'] = $opportunity->expected_distribution_date;
                        }

                        // Set actual returns if status is completed (30% chance)
                        if ($faker->boolean(30)) {
                            $investmentData['actual_profit_per_share'] = $opportunity->expected_profit * $faker->randomFloat(2, 0.8, 1.2);
                            $investmentData['actual_net_profit_per_share'] = $opportunity->expected_net_profit * $faker->randomFloat(2, 0.8, 1.2);
                            $investmentData['actual_returns_recorded_at'] = \Carbon\Carbon::parse($investmentDate)->addDays($faker->numberBetween(60, 365));
                        }
                    }

                    // Set distributed amount if distribution status is distributed
                    if ($investmentData['distribution_status'] === 'distributed') {
                        $expectedNetProfit = $investmentData['expected_net_profit_per_share'] ?? 0;
                        $actualNetProfit = $investmentData['actual_net_profit_per_share'] ?? 0;
                        $investmentData['distributed_profit'] = ($expectedNetProfit + $actualNetProfit) * $shares;
                        $investmentData['distributed_at'] = \Carbon\Carbon::parse($investmentDate)->addDays($faker->numberBetween(90, 400));
                    }

                    Investment::create($investmentData);

                    // Update reserved shares in the opportunity (only for open opportunities)
                    if ($opportunity->status === 'open') {
                        $opportunity->reserveShares($shares);

                        // Update opportunity status if fully funded
                        $opportunity->refresh();
                        if ($opportunity->available_shares <= 0) {
                            $opportunity->updateDynamicStatus();
                        }
                    }

                    $totalInvestments++;

                } catch (\Exception $e) {
                    $this->command->error("Failed to create investment: " . $e->getMessage());
                    continue;
                }
            }
        }

        $this->command->info("✅ Seeded {$totalInvestments} investment records for {$investorProfiles->count()} investors.");
        $this->showInvestmentStats();
    }

    /**
     * Generate realistic investment date based on opportunity timeline
     */
    protected function generateInvestmentDate($opportunity, $faker)
    {
        $now = now();

        // If opportunity has offering dates, invest within that period
        if ($opportunity->offering_start_date && $opportunity->offering_end_date) {
            $startDate = $opportunity->offering_start_date;
            $endDate = min($opportunity->offering_end_date, $now);

            if ($startDate <= $endDate) {
                return $faker->dateTimeBetween($startDate, $endDate);
            }
        }

        // If opportunity has offering start date, invest after it
        if ($opportunity->offering_start_date && $opportunity->offering_start_date <= $now) {
            return $faker->dateTimeBetween($opportunity->offering_start_date, $now);
        }

        // Default: invest within last 6 months
        return $faker->dateTimeBetween('-6 months', 'now');
    }

    /**
     * Generate realistic investment status based on opportunity and date
     */
    protected function generateInvestmentStatus($opportunity, $investmentDate, $faker)
    {
        $now = now();
        $investmentDate = \Carbon\Carbon::parse($investmentDate);
        $daysSinceInvestment = $investmentDate->diffInDays($now);

        // If opportunity is completed, investment is likely completed
        if ($opportunity->status === 'completed') {
            return $faker->randomElement(['completed', 'active']);
        }

        // If opportunity is suspended, investment is likely cancelled
        if ($opportunity->status === 'suspended') {
            return $faker->randomElement(['cancelled', 'pending']);
        }

        // If investment is very recent, it might be pending
        if ($daysSinceInvestment < 1) {
            return $faker->randomElement(['pending', 'active']);
        }

        // If investment is old and opportunity is still open, it's active
        if ($daysSinceInvestment > 30) {
            return $faker->randomElement(['active', 'completed']);
        }

        // Default distribution
        return $faker->randomElement(['active', 'active', 'completed', 'pending']);
    }

    /**
     * Show investment statistics
     */
    protected function showInvestmentStats()
    {
        $stats = Investment::selectRaw('
            status,
            investment_type,
            merchandise_status,
            distribution_status,
            COUNT(*) as count,
            SUM(total_investment) as total_amount,
            SUM(shares) as total_shares,
            SUM(expected_profit_per_share * shares) as total_expected_returns,
            SUM(actual_profit_per_share * shares) as total_actual_returns,
            SUM(distributed_profit) as total_distributed
        ')
        ->groupBy('status', 'investment_type', 'merchandise_status', 'distribution_status')
        ->orderBy('count', 'desc')
        ->get();

        $this->command->info('📊 Investment Statistics:');
        foreach ($stats as $stat) {
            $this->command->info("  {$stat->status} ({$stat->investment_type}): {$stat->count} investments, " .
                number_format($stat->total_amount, 2) . " SAR, {$stat->total_shares} shares");

            if ($stat->investment_type === 'myself') {
                $this->command->info("    Merchandise: {$stat->merchandise_status}, Distribution: {$stat->distribution_status}");
            } else {
                $expectedReturns = $stat->total_expected_returns ? number_format($stat->total_expected_returns, 2) : 'N/A';
                $actualReturns = $stat->total_actual_returns ? number_format($stat->total_actual_returns, 2) : 'N/A';
                $distributed = $stat->total_distributed ? number_format($stat->total_distributed, 2) : 'N/A';
                $this->command->info("    Expected Returns: {$expectedReturns} SAR, Actual: {$actualReturns} SAR, Distributed: {$distributed} SAR");
            }
        }
    }
}
