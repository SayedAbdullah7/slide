<?php

/**
 * Wallet API Test Script
 * Tests all wallet API endpoints
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🚀 Testing Wallet API Endpoints\n";
echo "================================\n\n";

// Test data
$testData = [
    'user_id' => 1, // Assuming user ID 1 exists
    'amount' => 1000,
    'description' => 'Test transaction',
];

// Helper function to make HTTP requests
function makeRequest($method, $url, $data = null, $headers = []) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
        'Content-Type: application/json',
        'Accept: application/json',
    ], $headers));

    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'body' => json_decode($response, true),
        'raw' => $response
    ];
}

// Test endpoints
$baseUrl = 'http://localhost:8000/api';
$token = 'your-test-token-here'; // You'll need to get a real token

$headers = [
    "Authorization: Bearer {$token}",
];

echo "📱 Testing Wallet Screen (Main Index)\n";
echo "-------------------------------------\n";
$response = makeRequest('GET', "{$baseUrl}/wallet", null, $headers);
echo "Status: " . $response['status'] . "\n";
if ($response['body']) {
    echo "Success: " . ($response['body']['success'] ? 'Yes' : 'No') . "\n";
    echo "Message: " . ($response['body']['message'] ?? 'N/A') . "\n";
    if (isset($response['body']['data']['total_balance'])) {
        echo "Total Balance: " . $response['body']['data']['total_balance']['formatted_amount'] . "\n";
    }
}
echo "\n";

echo "💰 Testing Wallet Balance\n";
echo "-------------------------\n";
$response = makeRequest('GET', "{$baseUrl}/wallet/balance", null, $headers);
echo "Status: " . $response['status'] . "\n";
if ($response['body']) {
    echo "Success: " . ($response['body']['success'] ? 'Yes' : 'No') . "\n";
    echo "Balance: " . ($response['body']['data']['formatted_balance'] ?? 'N/A') . "\n";
}
echo "\n";

echo "⚡ Testing Quick Actions\n";
echo "------------------------\n";
$response = makeRequest('GET', "{$baseUrl}/wallet/quick-actions", null, $headers);
echo "Status: " . $response['status'] . "\n";
if ($response['body']) {
    echo "Success: " . ($response['body']['success'] ? 'Yes' : 'No') . "\n";
    if (isset($response['body']['data']['actions'])) {
        echo "Available Actions: " . count($response['body']['data']['actions']) . "\n";
        foreach ($response['body']['data']['actions'] as $action) {
            echo "- " . $action['title'] . " (" . $action['id'] . ")\n";
        }
    }
}
echo "\n";

echo "👁️ Testing Balance Visibility Toggle\n";
echo "------------------------------------\n";
$response = makeRequest('POST', "{$baseUrl}/wallet/toggle-visibility", ['is_visible' => false], $headers);
echo "Status: " . $response['status'] . "\n";
if ($response['body']) {
    echo "Success: " . ($response['body']['success'] ? 'Yes' : 'No') . "\n";
    echo "Message: " . ($response['body']['message'] ?? 'N/A') . "\n";
}
echo "\n";

echo "💳 Testing Wallet Deposit\n";
echo "-------------------------\n";
$depositData = [
    'amount' => 500,
    'description' => 'Test deposit',
    'reference' => 'TEST_DEP_001',
];
$response = makeRequest('POST', "{$baseUrl}/wallet/deposit", $depositData, $headers);
echo "Status: " . $response['status'] . "\n";
if ($response['body']) {
    echo "Success: " . ($response['body']['success'] ? 'Yes' : 'No') . "\n";
    echo "Message: " . ($response['body']['message'] ?? 'N/A') . "\n";
    if (isset($response['body']['data']['new_balance'])) {
        echo "New Balance: " . $response['body']['data']['formatted_balance'] . "\n";
    }
}
echo "\n";

echo "📤 Testing Wallet Withdrawal\n";
echo "-----------------------------\n";
$withdrawData = [
    'amount' => 100,
    'description' => 'Test withdrawal',
    'reference' => 'TEST_WTH_001',
];
$response = makeRequest('POST', "{$baseUrl}/wallet/withdraw", $withdrawData, $headers);
echo "Status: " . $response['status'] . "\n";
if ($response['body']) {
    echo "Success: " . ($response['body']['success'] ? 'Yes' : 'No') . "\n";
    echo "Message: " . ($response['body']['message'] ?? 'N/A') . "\n";
    if (isset($response['body']['data']['new_balance'])) {
        echo "New Balance: " . $response['body']['data']['formatted_balance'] . "\n";
    }
}
echo "\n";

echo "📋 Testing Transaction History\n";
echo "------------------------------\n";
$response = makeRequest('GET', "{$baseUrl}/wallet/transactions?per_page=5", null, $headers);
echo "Status: " . $response['status'] . "\n";
if ($response['body']) {
    echo "Success: " . ($response['body']['success'] ? 'Yes' : 'No') . "\n";
    echo "Total Transactions: " . ($response['body']['data']['total_count'] ?? 'N/A') . "\n";
}
echo "\n";

echo "🏦 Testing Wallet Creation\n";
echo "---------------------------\n";
$createData = [
    'name' => 'Test Wallet',
    'description' => 'Test wallet creation',
    'meta' => ['test' => true],
];
$response = makeRequest('POST', "{$baseUrl}/wallet/create", $createData, $headers);
echo "Status: " . $response['status'] . "\n";
if ($response['body']) {
    echo "Success: " . ($response['body']['success'] ? 'Yes' : 'No') . "\n";
    echo "Message: " . ($response['body']['message'] ?? 'N/A') . "\n";
}
echo "\n";

echo "✅ Wallet API Testing Complete!\n";
echo "===============================\n";
echo "\n";
echo "📝 Notes:\n";
echo "- Make sure to replace 'your-test-token-here' with a real authentication token\n";
echo "- Ensure the Laravel development server is running: php artisan serve\n";
echo "- Check that you have test users and profiles in your database\n";
echo "- Some tests may fail if the user doesn't have sufficient balance or proper setup\n";
