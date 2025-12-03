<?php

/**
 * Taqnyat SMS Integration Test Script
 *
 * This script tests the Taqnyat SMS integration without requiring Laravel bootstrap.
 * Run this from the command line: php test_taqnyat_sms.php
 */

require_once __DIR__ . '/app/Services/TaqnyatSms.php';

use App\Services\TaqnyatSms;

echo "=================================\n";
echo "Taqnyat SMS Integration Test\n";
echo "=================================\n\n";

// Configuration
$authToken = 'YOUR_AUTH_TOKEN_HERE'; // Replace with your actual token
$testPhone = '966XXXXXXXXX'; // Replace with your test phone number
$senderName = 'YourApp'; // Replace with your approved sender name

// Initialize Taqnyat SMS
$taqnyat = new TaqnyatSms($authToken);

// Test 1: Check Account Balance
echo "Test 1: Checking Account Balance...\n";
$balanceResponse = $taqnyat->balance();
$balanceResult = json_decode($balanceResponse, true);
echo "Response: " . print_r($balanceResult, true) . "\n";
echo "-----------------------------------\n\n";

// Test 2: Get Available Senders
echo "Test 2: Getting Available Senders...\n";
$sendersResponse = $taqnyat->senders();
$sendersResult = json_decode($sendersResponse, true);
echo "Response: " . print_r($sendersResult, true) . "\n";
echo "-----------------------------------\n\n";

// Test 3: Check Service Status
echo "Test 3: Checking Service Status...\n";
$statusResponse = $taqnyat->sendStatus();
$statusResult = json_decode($statusResponse, true);
echo "Response: " . print_r($statusResult, true) . "\n";
echo "-----------------------------------\n\n";

// Test 4: Send Test SMS (UNCOMMENT TO SEND ACTUAL SMS)
/*
echo "Test 4: Sending Test SMS...\n";
$message = "Test message from Taqnyat Integration";
$smsResponse = $taqnyat->sendMsg($message, $testPhone, $senderName);
$smsResult = json_decode($smsResponse, true);
echo "Response: " . print_r($smsResult, true) . "\n";
echo "-----------------------------------\n\n";
*/

echo "=================================\n";
echo "Testing Complete\n";
echo "=================================\n";

// Usage Instructions
echo "\nUSAGE INSTRUCTIONS:\n";
echo "1. Replace 'YOUR_AUTH_TOKEN_HERE' with your actual Taqnyat API token\n";
echo "2. Replace '966XXXXXXXXX' with your test phone number\n";
echo "3. Replace 'YourApp' with your approved sender name\n";
echo "4. Uncomment Test 4 to send an actual SMS\n";
echo "5. Run: php test_taqnyat_sms.php\n";

