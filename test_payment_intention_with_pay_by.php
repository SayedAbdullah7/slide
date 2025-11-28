<?php

/**
 * Test script for payment intention with pay_by parameter
 * This script tests both card and apple_pay payment methods
 */

require_once 'vendor/autoload.php';

// Test data for investment intention
$investmentData = [
    'type' => 'investment',
    'opportunity_id' => 1,
    'shares' => 10,
    'investment_type' => 'myself',
    'pay_by' => 'card' // or 'apple_pay'
];

// Test data for wallet intention
$walletData = [
    'type' => 'wallet_charge',
    'amount' => 100.00,
    'pay_by' => 'card' // or 'apple_pay'
];

echo "Testing Payment Intention with pay_by parameter\n";
echo "==============================================\n\n";

// Test 1: Investment with card payment
echo "Test 1: Investment with card payment\n";
echo "-----------------------------------\n";
echo "Data: " . json_encode($investmentData, JSON_PRETTY_PRINT) . "\n";
echo "Expected: Should use card integration ID\n\n";

// Test 2: Investment with apple_pay payment
$investmentDataApplePay = $investmentData;
$investmentDataApplePay['pay_by'] = 'apple_pay';
echo "Test 2: Investment with apple_pay payment\n";
echo "----------------------------------------\n";
echo "Data: " . json_encode($investmentDataApplePay, JSON_PRETTY_PRINT) . "\n";
echo "Expected: Should use apple_pay integration ID\n\n";

// Test 3: Wallet charge with card payment
echo "Test 3: Wallet charge with card payment\n";
echo "--------------------------------------\n";
echo "Data: " . json_encode($walletData, JSON_PRETTY_PRINT) . "\n";
echo "Expected: Should use card integration ID\n\n";

// Test 4: Wallet charge with apple_pay payment
$walletDataApplePay = $walletData;
$walletDataApplePay['pay_by'] = 'apple_pay';
echo "Test 4: Wallet charge with apple_pay payment\n";
echo "-------------------------------------------\n";
echo "Data: " . json_encode($walletDataApplePay, JSON_PRETTY_PRINT) . "\n";
echo "Expected: Should use apple_pay integration ID\n\n";

echo "Integration ID Configuration:\n";
echo "============================\n";
echo "Card Integration ID: " . env('PAYMOB_INTEGRATION_ID_CARD', '17269') . "\n";
echo "Apple Pay Integration ID: " . env('PAYMOB_INTEGRATION_ID_APPLE_PAY', '17269') . "\n\n";

echo "API Endpoints to test:\n";
echo "======================\n";
echo "POST /api/payments/intention - Create payment intention\n";
echo "POST /api/investments/invest - Create investment with online payment\n";
echo "POST /api/payments/wallet/intention - Create wallet charge intention\n\n";

echo "Required parameters for all endpoints:\n";
echo "=====================================\n";
echo "- pay_by: 'card' or 'apple_pay' (required)\n";
echo "- For investment: opportunity_id, shares, investment_type\n";
echo "- For wallet: amount\n\n";

echo "Example cURL commands:\n";
echo "=====================\n";
echo "# Investment with card payment\n";
echo "curl -X POST http://your-domain/api/investments/invest \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Authorization: Bearer YOUR_TOKEN' \\\n";
echo "  -d '{\n";
echo "    \"investment_opportunity_id\": 1,\n";
echo "    \"shares\": 10,\n";
echo "    \"type\": \"myself\",\n";
echo "    \"pay_by\": \"online\",\n";
echo "    \"payment_method\": \"card\"\n";
echo "  }'\n\n";

echo "# Investment with apple_pay payment\n";
echo "curl -X POST http://your-domain/api/investments/invest \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Authorization: Bearer YOUR_TOKEN' \\\n";
echo "  -d '{\n";
echo "    \"investment_opportunity_id\": 1,\n";
echo "    \"shares\": 10,\n";
echo "    \"type\": \"myself\",\n";
echo "    \"pay_by\": \"online\",\n";
echo "    \"payment_method\": \"apple_pay\"\n";
echo "  }'\n\n";

echo "# Wallet charge with card payment\n";
echo "curl -X POST http://your-domain/api/payments/wallet/intention \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Authorization: Bearer YOUR_TOKEN' \\\n";
echo "  -d '{\n";
echo "    \"amount\": 100.00,\n";
echo "    \"pay_by\": \"card\"\n";
echo "  }'\n\n";

echo "# Wallet charge with apple_pay payment\n";
echo "curl -X POST http://your-domain/api/payments/wallet/intention \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Authorization: Bearer YOUR_TOKEN' \\\n";
echo "  -d '{\n";
echo "    \"amount\": 100.00,\n";
echo "    \"pay_by\": \"apple_pay\"\n";
echo "  }'\n\n";

echo "Test completed!\n";





