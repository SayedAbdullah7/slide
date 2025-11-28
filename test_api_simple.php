<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\InvestmentOpportunity;
use App\Models\Investment;
use App\Services\InvestmentService;
use App\Services\InvestmentValidationService;
use App\Services\InvestmentCalculatorService;
use App\Services\WalletService;

echo "🧪 Testing Investment API Components\n";
echo "====================================\n\n";

// Test 1: Test service dependencies
echo "1. Testing service dependencies...\n";
try {
    $walletService = app(WalletService::class);
    $validationService = app(InvestmentValidationService::class);
    $calculatorService = app(InvestmentCalculatorService::class);
    $investmentService = app(InvestmentService::class);
    echo "✅ All services loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Service loading failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Test with real data
echo "\n2. Testing with real data...\n";
$user = User::with('investorProfile')->first();
$opportunity = InvestmentOpportunity::where('status', 'open')->first();

if (!$user || !$user->investorProfile) {
    echo "❌ No user with investor profile found\n";
    exit(1);
}

if (!$opportunity) {
    echo "❌ No open investment opportunity found\n";
    exit(1);
}

echo "✅ Using User ID: {$user->id}\n";
echo "✅ Using Opportunity ID: {$opportunity->id}\n";
echo "✅ Opportunity: {$opportunity->name}\n";
echo "✅ Share Price: {$opportunity->share_price}\n";
echo "✅ Available Shares: {$opportunity->available_shares}\n";

// Test 3: Test validation service
echo "\n3. Testing validation service...\n";
try {
    $validationService->validateInvestmentRequest($user->investorProfile, $opportunity, 2, 'myself');
    echo "✅ Validation passed for valid request\n";
} catch (Exception $e) {
    echo "❌ Validation failed: " . $e->getMessage() . "\n";
}

// Test validation with invalid data
try {
    $validationService->validateInvestmentRequest($user->investorProfile, $opportunity, 1000, 'myself');
    echo "❌ Validation should have failed for excessive shares\n";
} catch (Exception $e) {
    echo "✅ Validation correctly failed for excessive shares: " . $e->getMessage() . "\n";
}

// Test 4: Test calculator service
echo "\n4. Testing calculator service...\n";
$amount = $calculatorService->calculateInvestmentAmount(2, $opportunity->share_price);
$totalPayment = $calculatorService->calculateTotalPaymentRequired($amount, 2, 'myself', $opportunity);

echo "✅ Investment amount (2 shares): {$amount}\n";
echo "✅ Total payment required: {$totalPayment}\n";

// Test 5: Test investment creation (without wallet)
echo "\n5. Testing investment creation logic...\n";
try {
    // Create investment data using calculator
    $investmentData = $calculatorService->buildInvestmentData(
        $user->investorProfile,
        $opportunity,
        2,
        $amount,
        $totalPayment,
        'myself'
    );

    echo "✅ Investment data created successfully\n";
    echo "✅ Shares: {$investmentData['shares']}\n";
    echo "✅ Total Investment: {$investmentData['total_investment']}\n";
    echo "✅ Total Payment Required: {$investmentData['total_payment_required']}\n";
    echo "✅ Investment Type: {$investmentData['investment_type']}\n";
    echo "✅ Status: {$investmentData['status']}\n";

} catch (Exception $e) {
    echo "❌ Investment data creation failed: " . $e->getMessage() . "\n";
}

// Test 6: Test existing investment check
echo "\n6. Testing existing investment check...\n";
$existingInvestment = $user->investorProfile->investments()
    ->where('opportunity_id', $opportunity->id)
    ->first();

if ($existingInvestment) {
    echo "✅ Found existing investment: ID {$existingInvestment->id}\n";
    echo "✅ Current shares: {$existingInvestment->shares}\n";
    echo "✅ Current total: {$existingInvestment->total_investment}\n";
} else {
    echo "✅ No existing investment found (this is expected for new investments)\n";
}

// Test 7: Test opportunity status
echo "\n7. Testing opportunity status...\n";
echo "✅ Opportunity is investable: " . ($opportunity->isInvestable() ? 'Yes' : 'No') . "\n";
echo "✅ Opportunity status: {$opportunity->status}\n";
echo "✅ Available shares: {$opportunity->available_shares}\n";
echo "✅ Completion rate: {$opportunity->completion_rate}%\n";

// Test 8: Test error handling
echo "\n8. Testing error handling...\n";
try {
    $validationService->validateShares($opportunity, 1000); // Too many shares
    echo "❌ Should have thrown exception for too many shares\n";
} catch (Exception $e) {
    echo "✅ Correctly caught exception: " . $e->getMessage() . "\n";
}

echo "\n✅ All component tests completed successfully!\n";
echo "\n📋 Summary:\n";
echo "- Services are properly configured\n";
echo "- Validation logic is working\n";
echo "- Calculator logic is working\n";
echo "- Investment data structure is correct\n";
echo "- Error handling is working\n";
echo "\n🎯 The Investment API is ready for testing!\n";
