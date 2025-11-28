<?php

/**
 * Test script for wallet intention creation
 *
 * This script tests the wallet charging intention API endpoint
 */

// Configuration
$baseUrl = 'http://localhost:8000'; // Adjust this to your actual URL
$token = 'YOUR_AUTH_TOKEN_HERE'; // Replace with actual auth token

// Test data
$testData = [
    'amount' => 100.50
];

// Headers
$headers = [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
];

// Make the request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/payments/wallet-intentions');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Display results
echo "=== Wallet Intention Creation Test ===\n";
echo "URL: {$baseUrl}/api/payments/wallet-intentions\n";
echo "HTTP Code: {$httpCode}\n";
echo "Request Data: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n";

if ($error) {
    echo "cURL Error: {$error}\n";
} else {
    echo "Response: " . json_encode(json_decode($response), JSON_PRETTY_PRINT) . "\n";
}

echo "\n=== Test Complete ===\n";

