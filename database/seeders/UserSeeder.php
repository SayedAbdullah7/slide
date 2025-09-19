<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\InvestorProfile;
use App\Models\OwnerProfile;
use App\FundGoalEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(User::count() > 0) {
            $this->command->warn('Users already seeded.');
            return;
        }

        $faker = Faker::create();

        // Create admin user
        // $this->createAdminUser();

        // Create investors only
        $this->createInvestorUsers($faker);

        // Create owners only
        $this->createOwnerUsers($faker);

        // Create users with both profiles
        $this->createDualProfileUsers($faker);

        $this->command->info('Users and profiles seeded successfully!');
    }

    private function createAdminUser(): void
    {
        User::create([
            'full_name' => 'Admin User',
            'phone' => '+1234567890',
            'email' => 'admin@slide.com',
            'national_id' => '123456789012345',
            'birth_date' => '1985-01-01',
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_registered' => true,
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);
    }

    private function createInvestorUsers($faker): void
    {
        for ($i = 0; $i < 5; $i++) {
            $user = User::create([
                'full_name' => $faker->name(),
                'phone' => $faker->unique()->phoneNumber(),
                'email' => $faker->unique()->safeEmail(),
                'national_id' => $faker->unique()->numerify('##############'),
                'birth_date' => $faker->date('Y-m-d', '2000-01-01'),
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_registered' => true,
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'active_profile_type' => User::PROFILE_INVESTOR,
            ]);

            // Create investor profile
            InvestorProfile::create([
                'user_id' => $user->id,
                'extra_data' => json_encode([
                    'investment_preferences' => $faker->randomElements(['real_estate', 'stocks', 'bonds', 'crypto'], 2),
                    'risk_tolerance' => $faker->randomElement(['low', 'medium', 'high']),
                    'investment_amount' => $faker->numberBetween(1000, 100000),
                    'experience_level' => $faker->randomElement(['beginner', 'intermediate', 'expert']),
                ]),
            ]);
        }
    }

    private function createOwnerUsers($faker): void
    {
        for ($i = 0; $i < 5; $i++) {
            $user = User::create([
                'full_name' => $faker->name(),
                'phone' => $faker->unique()->phoneNumber(),
                'email' => $faker->unique()->safeEmail(),
                'national_id' => $faker->unique()->numerify('##############'),
                'birth_date' => $faker->date('Y-m-d', '2000-01-01'),
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_registered' => true,
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'active_profile_type' => User::PROFILE_OWNER,
            ]);

            // Create owner profile
            OwnerProfile::create([
                'user_id' => $user->id,
                'business_name' => $faker->company(),
                'goal' => $faker->randomElement(FundGoalEnum::values()),
                'tax_number' => $faker->unique()->numerify('TAX#######'),
            ]);
        }
    }

    private function createDualProfileUsers($faker): void
    {
        for ($i = 0; $i < 3; $i++) {
            $user = User::create([
                'full_name' => $faker->name(),
                'phone' => $faker->unique()->phoneNumber(),
                'email' => $faker->unique()->safeEmail(),
                'national_id' => $faker->unique()->numerify('##############'),
                'birth_date' => $faker->date('Y-m-d', '2000-01-01'),
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_registered' => true,
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'active_profile_type' => $faker->randomElement([User::PROFILE_INVESTOR, User::PROFILE_OWNER]),
            ]);

            // Create investor profile
            InvestorProfile::create([
                'user_id' => $user->id,
                'extra_data' => json_encode([
                    'investment_preferences' => $faker->randomElements(['real_estate', 'stocks', 'bonds', 'crypto'], 2),
                    'risk_tolerance' => $faker->randomElement(['low', 'medium', 'high']),
                    'investment_amount' => $faker->numberBetween(1000, 100000),
                    'experience_level' => $faker->randomElement(['beginner', 'intermediate', 'expert']),
                ]),
            ]);

            // Create owner profile
            OwnerProfile::create([
                'user_id' => $user->id,
                'business_name' => $faker->company(),
                'goal' => $faker->randomElement(FundGoalEnum::values()),
                'tax_number' => $faker->unique()->numerify('TAX#######'),
            ]);
        }
    }
}
