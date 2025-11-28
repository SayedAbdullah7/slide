<?php

/**
 * Simple Wallet API Test
 * Quick test using Laravel's testing framework
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 Simple Wallet API Test\n";
echo "=========================\n\n";

try {
    // Test 1: Check if routes are registered
    echo "1. Testing Route Registration...\n";
    $router = app('router');
    $routes = $router->getRoutes();

    $walletRoutes = collect($routes)->filter(function ($route) {
        return str_contains($route->uri(), 'wallet');
    });

    echo "   ✅ Found " . $walletRoutes->count() . " wallet routes\n";
    foreach ($walletRoutes as $route) {
        echo "   - {$route->methods()[0]} {$route->uri()} -> {$route->getActionName()}\n";
    }
    echo "\n";

    // Test 2: Check if services are properly bound
    echo "2. Testing Service Binding...\n";

    try {
        $walletService = app(\App\Services\WalletService::class);
        echo "   ✅ WalletService bound successfully\n";
    } catch (Exception $e) {
        echo "   ❌ WalletService binding failed: " . $e->getMessage() . "\n";
    }

    try {
        $walletStatsService = app(\App\Services\WalletStatisticsService::class);
        echo "   ✅ WalletStatisticsService bound successfully\n";
    } catch (Exception $e) {
        echo "   ❌ WalletStatisticsService binding failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 3: Check if controller exists and methods are accessible
    echo "3. Testing Controller Methods...\n";

    $controller = new \App\Http\Controllers\Api\WalletController(
        app(\App\Services\WalletService::class),
        app(\App\Services\WalletStatisticsService::class),
        app(\App\Support\CurrentProfile::class)
    );

    $methods = ['index', 'getBalance', 'deposit', 'withdraw', 'getTransactions', 'transfer', 'createWallet', 'toggleBalanceVisibility', 'getQuickActions'];

    foreach ($methods as $method) {
        if (method_exists($controller, $method)) {
            echo "   ✅ Method '{$method}' exists\n";
        } else {
            echo "   ❌ Method '{$method}' missing\n";
        }
    }
    echo "\n";

    // Test 4: Check if WalletStatisticsService methods work
    echo "4. Testing WalletStatisticsService...\n";

    $statsService = app(\App\Services\WalletStatisticsService::class);

    try {
        // Test with mock investor profile
        $mockProfile = new \App\Support\CurrentProfile();
        $mockProfile->type = 'investor';

        // Create a mock investor model
        $mockInvestor = new class {
            public function investments() {
                return new class {
                    public function where($column, $value) { return $this; }
                    public function whereIn($column, $values) { return $this; }
                    public function whereNotNull($column) { return $this; }
                    public function orderBy($column, $direction) { return $this; }
                    public function first() { return null; }
                    public function get() { return collect([]); }
                };
            }
        };

        $mockProfile->model = $mockInvestor;

        $statsService = new \App\Services\WalletStatisticsService($mockProfile);
        $statistics = $statsService->getAllStatistics();

        echo "   ✅ getAllStatistics() works\n";
        echo "   - Realized Profits: " . $statistics['realized_profits']['formatted_amount'] . "\n";
        echo "   - Pending Profits: " . $statistics['pending_profits']['formatted_amount'] . "\n";
        echo "   - Upcoming Earnings: " . $statistics['upcoming_earnings']['formatted_amount'] . "\n";

    } catch (Exception $e) {
        echo "   ❌ WalletStatisticsService test failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 5: Check database connectivity (if possible)
    echo "5. Testing Database Connection...\n";

    try {
        $pdo = app('db')->connection()->getPdo();
        echo "   ✅ Database connection successful\n";

        // Check if wallet-related tables exist
        $tables = ['investor_profiles', 'owner_profiles', 'investments'];
        foreach ($tables as $table) {
            try {
                app('db')->table($table)->limit(1)->get();
                echo "   ✅ Table '{$table}' exists and accessible\n";
            } catch (Exception $e) {
                echo "   ⚠️  Table '{$table}' issue: " . $e->getMessage() . "\n";
            }
        }

    } catch (Exception $e) {
        echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    echo "🎉 Simple Test Complete!\n";
    echo "========================\n";
    echo "\n";
    echo "Next Steps:\n";
    echo "1. Run 'php artisan serve' to start the development server\n";
    echo "2. Use the full test script with real authentication tokens\n";
    echo "3. Test with actual user accounts and wallet data\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
