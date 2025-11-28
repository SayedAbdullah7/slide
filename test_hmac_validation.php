<?php

/**
 * Standalone HMAC Validation Test Script
 * Run with: php test_hmac_validation.php
 */

// TOKEN webhook from Paymob
$tokenPayload = [
    "type" => "TOKEN",
    "obj" => [
        "id" => 27911,
        "token" => "5fe444640033d1c5696ac76f2360af7f2c38f6c72fd18c0f5c644ac0",
        "masked_pan" => "xxxx-xxxx-xxxx-0008",
        "merchant_id" => 11883,
        "card_subtype" => "MasterCard",
        "created_at" => "2025-10-14T22:46:57.977092+03:00",
        "email" => "sayed@gmail.com",
        "order_id" => "1037965",
        "user_added" => false,
        "next_payment_intention" => "pi_test_4c022580ecca4f1f9ae38f6d9778c835",
    ]
];

$receivedHmac = "2c89c91fad5cb95b6f399536284155339b931e42998123ee59e967ebcb4e8f0f7f81aa93ffab06d372e4b67b05c04e29f965cd3be8ef94fbe77158daf4440eb3";

echo "=== HMAC Validation Debug ===\n\n";
echo "Received HMAC: {$receivedHmac}\n";
echo "HMAC Length: " . strlen($receivedHmac) . " (SHA-512 = 128 chars)\n\n";

// Ask for HMAC secret
echo "Enter your Paymob HMAC secret (or press Enter to use default from .env): ";
$hmacSecret = trim(fgets(STDIN));

if (empty($hmacSecret)) {
    // Load from .env
    if (file_exists(__DIR__ . '/.env')) {
        $env = file_get_contents(__DIR__ . '/.env');
        if (preg_match('/PAYMOB_HMAC_SECRET=(.+)/m', $env, $matches)) {
            $hmacSecret = trim($matches[1]);
            $hmacSecret = trim($hmacSecret, '"\''); // Remove quotes
        }
    }
}

if (empty($hmacSecret)) {
    die("❌ HMAC secret not found!\n");
}

echo "\nUsing HMAC Secret: " . substr($hmacSecret, 0, 10) . "...\n";
echo "Secret Length: " . strlen($hmacSecret) . "\n\n";

$methods = [];

// Method 1: SHA-512 without hmac field (most likely)
$json1 = json_encode($tokenPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$hmac1 = hash_hmac('sha512', $json1, $hmacSecret);
$methods['SHA512 without hmac'] = $hmac1;

// Method 2: SHA-256 without hmac field
$hmac2 = hash_hmac('sha256', $json1, $hmacSecret);
$methods['SHA256 without hmac'] = $hmac2;

// Method 3: SHA-512 with full payload (including hmac)
$fullPayload = $tokenPayload;
$fullPayload['hmac'] = $receivedHmac;
$json3 = json_encode($fullPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$hmac3 = hash_hmac('sha512', $json3, $hmacSecret);
$methods['SHA512 with hmac'] = $hmac3;

// Method 4: SHA-512 with obj only
$objJson = json_encode($tokenPayload['obj'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$hmac4 = hash_hmac('sha512', $objJson, $hmacSecret);
$methods['SHA512 obj only'] = $hmac4;

// Method 5: SHA-512 with sorted keys
function sortArrayRecursive($array) {
    ksort($array);
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = sortArrayRecursive($value);
        }
    }
    return $array;
}

$sorted = sortArrayRecursive($tokenPayload);
$json5 = json_encode($sorted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$hmac5 = hash_hmac('sha512', $json5, $hmacSecret);
$methods['SHA512 sorted keys'] = $hmac5;

// Method 6: SHA-512 with compact JSON (no spaces)
$json6 = json_encode($tokenPayload);
$hmac6 = hash_hmac('sha512', $json6, $hmacSecret);
$methods['SHA512 compact JSON'] = $hmac6;

// Method 7: SHA-512 with raw string (no JSON encoding)
$rawString = serialize($tokenPayload);
$hmac7 = hash_hmac('sha512', $rawString, $hmacSecret);
$methods['SHA512 serialized'] = $hmac7;

echo "Testing " . count($methods) . " HMAC calculation methods:\n";
echo str_repeat("=", 100) . "\n\n";

$matchFound = false;
foreach ($methods as $methodName => $calculatedHmac) {
    $matches = hash_equals($calculatedHmac, $receivedHmac);
    $status = $matches ? '✅ MATCH!' : '❌ no match';

    echo "{$methodName}:\n";
    echo "  Calculated: {$calculatedHmac}\n";
    echo "  Status: {$status}\n\n";

    if ($matches) {
        $matchFound = true;
        echo "  🎉🎉🎉 FOUND THE CORRECT METHOD! 🎉🎉🎉\n\n";
    }
}

echo str_repeat("=", 100) . "\n";

if ($matchFound) {
    echo "✅ SUCCESS! HMAC validation method found!\n";
} else {
    echo "❌ NO MATCH FOUND!\n\n";
    echo "Debug Information:\n";
    echo "- Received HMAC might be calculated with a different secret\n";
    echo "- Or Paymob uses a custom HMAC calculation method\n\n";
    echo "Payload (no hmac):\n";
    echo json_encode($tokenPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
}


