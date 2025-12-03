<?php

namespace Database\Seeders;

use App\Models\InvestmentOpportunity;
use Illuminate\Database\Seeder;

class SetDefaultFilesForInvestmentOpportunitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Starting to set default files for InvestmentOpportunities...');

        // مسارات الملفات الثابتة
        $termsPath = storage_path('app/seeder_files/sample_terms.pdf');
        $summaryPath = storage_path('app/seeder_files/sample_summary.pdf');
        $coversFolder = storage_path('app/seeder_files/covers');
        $avatarsFolder = storage_path('app/seeder_files/avatars');

        // Check if required files exist
        if (!file_exists($termsPath) || !file_exists($summaryPath)) {
            $this->command->error('Sample PDF files missing in storage/app/seeder_files/');
            $this->command->info('Required files:');
            $this->command->info('- storage/app/seeder_files/sample_terms.pdf');
            $this->command->info('- storage/app/seeder_files/sample_summary.pdf');
            return;
        }

        if (!$this->hasImageFiles($coversFolder)) {
            $this->command->error('No cover images found in ' . $coversFolder);
            return;
        }

        if (!$this->hasImageFiles($avatarsFolder)) {
            $this->command->error('No avatar images found in ' . $avatarsFolder);
            return;
        }

        // Get all investment opportunities
        $opportunities = InvestmentOpportunity::all();

        if ($opportunities->isEmpty()) {
            $this->command->warn('No investment opportunities found in the database.');
            return;
        }

        $this->command->info("Found {$opportunities->count()} investment opportunities to process...");

        $updatedCount = 0;
        $termsAdded = 0;
        $summaryAdded = 0;
        $coverAdded = 0;
        $ownerAvatarAdded = 0;

        foreach ($opportunities as $opportunity) {
            $updated = false;

            // Check and add terms file if missing
            if (!$opportunity->getFirstMedia('terms')) {
                try {
                    $opportunity->addMedia($termsPath)->preservingOriginal()->toMediaCollection('terms');
                    $termsAdded++;
                    $updated = true;
                    $this->command->info("✅ Added terms to opportunity #{$opportunity->id} ({$opportunity->name})");
                } catch (\Exception $e) {
                    $this->command->error("❌ Failed to add terms to opportunity #{$opportunity->id}: " . $e->getMessage());
                }
            }

            // Check and add summary file if missing
            if (!$opportunity->getFirstMedia('summary')) {
                try {
                    $opportunity->addMedia($summaryPath)->preservingOriginal()->toMediaCollection('summary');
                    $summaryAdded++;
                    $updated = true;
                    $this->command->info("✅ Added summary to opportunity #{$opportunity->id} ({$opportunity->name})");
                } catch (\Exception $e) {
                    $this->command->error("❌ Failed to add summary to opportunity #{$opportunity->id}: " . $e->getMessage());
                }
            }

            // Check and add cover image if missing
            if ($opportunity->getMedia('cover')->isEmpty()) {
                try {
                    $coverPath = $this->getRandomImageFromFolder($coversFolder);
                    if ($coverPath) {
                        // Add 2-5 cover images
                        for ($i = 0; $i < 3; $i++) {
                            $coverPath = $this->getRandomImageFromFolder($coversFolder);
                            if ($coverPath) {
                                $opportunity->addMedia($coverPath)->preservingOriginal()->toMediaCollection('cover');
                                $coverAdded++;
                                $updated = true;
                            }
                        }
                        if ($updated) {
                            $this->command->info("✅ Added cover images to opportunity #{$opportunity->id} ({$opportunity->name})");
                        }
                    }
                } catch (\Exception $e) {
                    $this->command->error("❌ Failed to add cover to opportunity #{$opportunity->id}: " . $e->getMessage());
                }
            }

            // Check and add owner avatar if missing
            if (!$opportunity->getFirstMedia('owner_avatar')) {
                try {
                    $avatarPath = $this->getRandomImageFromFolder($avatarsFolder);
                    if ($avatarPath) {
                        $opportunity->addMedia($avatarPath)->preservingOriginal()->toMediaCollection('owner_avatar');
                        $ownerAvatarAdded++;
                        $updated = true;
                        $this->command->info("✅ Added owner avatar to opportunity #{$opportunity->id} ({$opportunity->name})");
                    }
                } catch (\Exception $e) {
                    $this->command->error("❌ Failed to add owner avatar to opportunity #{$opportunity->id}: " . $e->getMessage());
                }
            }

            if ($updated) {
                $updatedCount++;
            }
        }

        $this->command->newLine();
        $this->command->info('✅ Process completed!');
        $this->command->info("📊 Statistics:");
        $this->command->info("   - Total opportunities processed: {$opportunities->count()}");
        $this->command->info("   - Opportunities updated: {$updatedCount}");
        $this->command->info("   - Terms files added: {$termsAdded}");
        $this->command->info("   - Summary files added: {$summaryAdded}");
        $this->command->info("   - Cover images added: {$coverAdded}");
        $this->command->info("   - Owner avatars added: {$ownerAvatarAdded}");
    }

    /**
     * Get a random image file path from a folder
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
     * Check if any image files exist in the folder
     */
    protected function hasImageFiles(string $folderPath): bool
    {
        $files = glob($folderPath . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
        return !empty($files);
    }
}

