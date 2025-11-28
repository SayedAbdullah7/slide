<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test data
$testData = [
    'user_id' => 1,
    'opportunity_id' => 2,
    'shares' => 2,
    'type' => 'myself'
];

echo "🧪 Testing Investment API\n";
echo "========================\n\n";

// Test 1: Get user token
echo "1. Getting user authentication token...\n";
$user = App\Models\User::find($testData['user_id']);
if (!$user) {
    echo "❌ User not found!\n";
    exit(1);
}

// Create a personal access token
$token = $user->createToken('test-token')->plainTextToken;
echo "✅ Token created: " . substr($token, 0, 20) . "...\n\n";

// Test 2: Test investment API
echo "2. Testing investment API...\n";

$headers = [
    'Authorization' => 'Bearer ' . $token,
    'Content-Type' => 'application/json',
    'Accept' => 'application/json'
];

$payload = [
    'investment_opportunity_id' => $testData['opportunity_id'],
    'shares' => $testData['shares'],
    'type' => $testData['type']
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/investor/invest');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status Code: $httpCode\n";

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    $responseData = json_decode($response, true);

    if ($httpCode === 201) {
        echo "✅ Investment created successfully!\n";
        echo "Investment ID: " . ($responseData['result']['id'] ?? 'N/A') . "\n";
        echo "Total Investment: " . ($responseData['result']['total_investment'] ?? 'N/A') . "\n";
        echo "Total Payment Required: " . ($responseData['result']['total_payment_required'] ?? 'N/A') . "\n";
        echo "Shares: " . ($responseData['result']['shares'] ?? 'N/A') . "\n";
        echo "Status: " . ($responseData['result']['status'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Investment failed!\n";
        echo "Response: " . $response . "\n";
    }
}

echo "\n";

// Test 3: Test validation errors
echo "3. Testing validation errors...\n";

$invalidPayload = [
    'investment_opportunity_id' => 999999, // Non-existent ID
    'shares' => 0, // Invalid shares
    'type' => 'invalid_type' // Invalid type
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/investor/invest');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invalidPayload));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: $httpCode\n";
if ($httpCode === 422) {
    echo "✅ Validation errors working correctly!\n";
} else {
    echo "❌ Expected validation error (422), got $httpCode\n";
    echo "Response: " . $response . "\n";
}

echo "\n";

// Test 4: Test insufficient shares
echo "4. Testing insufficient shares error...\n";

$insufficientSharesPayload = [
    'investment_opportunity_id' => $testData['opportunity_id'],
    'shares' => 1000, // More than available
    'type' => $testData['type']
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/investor/invest');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($insufficientSharesPayload));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: $httpCode\n";
if ($httpCode === 400) {
    echo "✅ Insufficient shares error working correctly!\n";
} else {
    echo "❌ Expected insufficient shares error (400), got $httpCode\n";
    echo "Response: " . $response . "\n";
}

echo "\n";

// Test 5: Check database state
echo "5. Checking database state...\n";
$investmentCount = App\Models\Investment::count();
echo "Total investments in database: $investmentCount\n";

$userInvestments = App\Models\Investment::where('user_id', $testData['user_id'])->count();
echo "User investments: $userInvestments\n";

echo "\n✅ Investment API testing completed!\n";
