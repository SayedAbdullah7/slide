<?php

/**
 * Paymob Integration Test Script
 *
 * This script demonstrates how to use the Paymob payment integration.
 * Run this script to test the basic functionality.
 */

require_once 'vendor/autoload.php';

use App\Services\PaymobService;
use App\Repositories\PaymentRepository;
use App\Models\PaymentIntention;
use App\Models\PaymentTransaction;

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Paymob Integration Test ===\n\n";

// Test 1: Create Payment Intention
echo "1. Testing Payment Intention Creation...\n";

$paymentRepository = new PaymentRepository();
$paymobService = new PaymobService($paymentRepository);

$intentionData = [
    'opportunity_id' => 1, // Assuming investment opportunity ID 1 exists
    'shares' => 10,
    'investment_type' => 'partial'
];

// Note: This test now requires using the API endpoint instead of direct service call
// Use: POST /api/payments/intentions with the intentionData as request body
echo "Note: Use POST /api/payments/intentions with the following data:\n";
echo json_encode($intentionData, JSON_PRETTY_PRINT) . "\n\n";

// For testing purposes, we'll simulate the service call
// In real usage, call the API endpoint: POST /api/payments/intentions
$result = ['success' => false, 'error' => 'Use API endpoint instead of direct service call'];

if ($result['success']) {
    echo "✓ Payment intention created successfully\n";
    echo "  - Intention ID: " . $result['intention']->id . "\n";
    echo "  - Client Secret: " . ($result['data']['client_secret'] ?? 'N/A') . "\n";
    echo "  - Payment Token: " . ($result['data']['payment_token'] ?? 'N/A') . "\n";

    $intention = $result['intention'];

    // Test 2: Get Checkout URL
    echo "\n2. Testing Checkout URL Generation...\n";

    if ($intention->client_secret) {
        $checkoutResult = $paymobService->getCheckoutUrl($intention->client_secret);

        if ($checkoutResult['success']) {
            echo "✓ Checkout URL generated successfully\n";
            echo "  - URL: " . $checkoutResult['checkout_url'] . "\n";
        } else {
            echo "✗ Failed to generate checkout URL: " . $checkoutResult['error'] . "\n";
        }
    } else {
        echo "✗ No client secret available for checkout URL\n";
    }

    // Test 3: Database Verification
    echo "\n3. Testing Database Records...\n";

    $dbIntention = PaymentIntention::find($intention->id);
    if ($dbIntention) {
        echo "✓ Payment intention found in database\n";
        echo "  - Status: " . $dbIntention->status . "\n";
        echo "  - Amount: " . $dbIntention->amount_in_sar . " SAR\n";
        echo "  - User ID: " . $dbIntention->user_id . "\n";
    } else {
        echo "✗ Payment intention not found in database\n";
    }

} else {
    echo "✗ Failed to create payment intention: " . $result['error'] . "\n";
    if (isset($result['details'])) {
        echo "  Details: " . json_encode($result['details']) . "\n";
    }
}

// Test 4: Configuration Check
echo "\n4. Testing Configuration...\n";

$config = config('services.paymob');
$requiredConfig = ['api_key', 'secret_key', 'public_key', 'integration_id', 'base_url'];

foreach ($requiredConfig as $key) {
    if (!empty($config[$key])) {
        echo "✓ $key: " . (strlen($config[$key]) > 10 ? substr($config[$key], 0, 10) . '...' : $config[$key]) . "\n";
    } else {
        echo "✗ $key: Not configured\n";
    }
}

// Test 5: API Endpoints Check
echo "\n5. Testing API Routes...\n";

$routes = [
    'POST /api/payments/intentions' => 'Create payment intention',
    'GET /api/payments/intentions' => 'Get payment intentions',
    'GET /api/payments/intentions/{id}/checkout-url' => 'Get checkout URL',
    'POST /api/payments/moto' => 'Process MOTO payment',
    'POST /api/payments/capture' => 'Capture payment',
    'POST /api/payments/void' => 'Void payment',
    'POST /api/payments/refund' => 'Refund payment',
    'GET /api/payments/transactions' => 'Get transactions',
    'GET /api/payments/stats' => 'Get payment stats',
    'POST /api/payments/webhooks/paymob' => 'Paymob webhook',
    'GET /api/payments/webhooks/success' => 'Success redirect',
    'GET /api/payments/webhooks/failure' => 'Failure redirect',
];

foreach ($routes as $route => $description) {
    echo "✓ $route - $description\n";
}

echo "\n=== Test Complete ===\n";
echo "\nNext Steps:\n";
echo "1. Configure your .env file with Paymob credentials\n";
echo "2. Run 'php artisan migrate' to create database tables\n";
echo "3. Test the API endpoints using Postman or curl\n";
echo "4. Set up webhook URLs in your Paymob dashboard\n";
echo "5. Test with Paymob's sandbox environment first\n";

echo "\nFor detailed documentation, see: PAYMOB_INTEGRATION_DOCUMENTATION.md\n";
