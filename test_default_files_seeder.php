<?php

/**
 * Quick test script to run the SetDefaultFilesForInvestmentOpportunitiesSeeder
 *
 * Usage: php test_default_files_seeder.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Bootstrap Laravel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "🔄 Running SetDefaultFilesForInvestmentOpportunitiesSeeder...\n\n";

try {
    Artisan::call('db:seed', [
        '--class' => 'SetDefaultFilesForInvestmentOpportunitiesSeeder'
    ]);

    echo "\n✅ Seeder completed successfully!\n";
    echo Artisan::output();

} catch (\Exception $e) {
    echo "\n❌ Error running seeder: " . $e->getMessage() . "\n";
    exit(1);
}

