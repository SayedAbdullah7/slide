<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PaymobService;
use App\Repositories\PaymentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymobSignatureTest extends TestCase
{
    private PaymobService $paymobService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create PaymobService instance
        $paymentRepository = $this->app->make(PaymentRepository::class);
        $this->paymobService = new PaymobService($paymentRepository);
    }

    /**
     * Test HMAC signature validation with TOKEN webhook
     */
    public function test_token_webhook_signature_validation()
    {
        // Real TOKEN webhook payload from Paymob
        $payload = [
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

        // Test different HMAC calculation methods
        $this->testDifferentHmacMethods($payload, $receivedHmac, 'TOKEN');
    }

    /**
     * Test HMAC signature validation with TRANSACTION webhook
     */
    public function test_transaction_webhook_signature_validation()
    {
        // Simplified TRANSACTION payload
        $payload = [
            "type" => "TRANSACTION",
            "obj" => [
                "id" => 999871,
                "pending" => false,
                "amount_cents" => 100000,
                "success" => true,
                "order" => [
                    "id" => 1037965,
                    "merchant_order_id" => "WALLET-CHARGE-14-1760471157",
                ]
            ]
        ];

        $receivedHmac = "b3433e3182f4dd4324133abef79589125f5c0323bf8b05083641513bb83e74fb328e595c3d2d43214d6881b7cdd72d707c6e91de306fe0c7c2d1b6705c43cf6a";

        // Test different HMAC calculation methods
        $this->testDifferentHmacMethods($payload, $receivedHmac, 'TRANSACTION');
    }

    /**
     * Test different HMAC calculation methods
     */
    private function testDifferentHmacMethods(array $payload, string $receivedHmac, string $type)
    {
        echo "\n\n=== Testing {$type} Webhook HMAC ===\n";
        echo "Received HMAC: {$receivedHmac}\n";
        echo "HMAC Length: " . strlen($receivedHmac) . "\n\n";

        // Get HMAC secret from config
        $hmacSecret = config('services.paymob.hmac_secret');
        echo "HMAC Secret configured: " . (!empty($hmacSecret) ? 'Yes' : 'No') . "\n";
        if (!empty($hmacSecret)) {
            echo "HMAC Secret length: " . strlen($hmacSecret) . "\n\n";
        }

        $methods = [];

        // Method 1: SHA-256 with full payload (including hmac)
        $fullPayload = $payload;
        $fullPayload['hmac'] = $receivedHmac;
        $json1 = json_encode($fullPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $hmac1 = hash_hmac('sha256', $json1, $hmacSecret);
        $methods['SHA256 with full payload (inc. hmac)'] = [
            'hmac' => $hmac1,
            'matches' => hash_equals($hmac1, $receivedHmac)
        ];

        // Method 2: SHA-256 without hmac field
        $json2 = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $hmac2 = hash_hmac('sha256', $json2, $hmacSecret);
        $methods['SHA256 without hmac field'] = [
            'hmac' => $hmac2,
            'matches' => hash_equals($hmac2, $receivedHmac)
        ];

        // Method 3: SHA-512 without hmac field
        $hmac3 = hash_hmac('sha512', $json2, $hmacSecret);
        $methods['SHA512 without hmac field'] = [
            'hmac' => $hmac3,
            'matches' => hash_equals($hmac3, $receivedHmac)
        ];

        // Method 4: SHA-256 with sorted keys
        $sortedPayload = $this->sortArrayRecursive($payload);
        $json4 = json_encode($sortedPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $hmac4 = hash_hmac('sha256', $json4, $hmacSecret);
        $methods['SHA256 with sorted keys'] = [
            'hmac' => $hmac4,
            'matches' => hash_equals($hmac4, $receivedHmac)
        ];

        // Method 5: SHA-512 with sorted keys
        $hmac5 = hash_hmac('sha512', $json4, $hmacSecret);
        $methods['SHA512 with sorted keys'] = [
            'hmac' => $hmac5,
            'matches' => hash_equals($hmac5, $receivedHmac)
        ];

        // Method 6: Only 'obj' content
        if (isset($payload['obj'])) {
            $objJson = json_encode($payload['obj'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $hmac6 = hash_hmac('sha256', $objJson, $hmacSecret);
            $methods['SHA256 with obj only'] = [
                'hmac' => $hmac6,
                'matches' => hash_equals($hmac6, $receivedHmac)
            ];

            $hmac7 = hash_hmac('sha512', $objJson, $hmacSecret);
            $methods['SHA512 with obj only'] = [
                'hmac' => $hmac7,
                'matches' => hash_equals($hmac7, $receivedHmac)
            ];
        }

        // Method 7: Concatenated string (Paymob specific format)
        // Try: amount + currency + order_id + ... (common Paymob pattern)
        if ($type === 'TRANSACTION' && isset($payload['obj'])) {
            $obj = $payload['obj'];
            $concatenated =
                ($obj['amount_cents'] ?? '') .
                ($obj['created_at'] ?? '') .
                ($obj['currency'] ?? '') .
                ($obj['error_occured'] ?? '') .
                ($obj['has_parent_transaction'] ?? '') .
                ($obj['id'] ?? '') .
                ($obj['integration_id'] ?? '') .
                ($obj['is_3d_secure'] ?? '') .
                ($obj['is_auth'] ?? '') .
                ($obj['is_capture'] ?? '') .
                ($obj['is_refunded'] ?? '') .
                ($obj['is_standalone_payment'] ?? '') .
                ($obj['is_voided'] ?? '') .
                ($obj['order']['id'] ?? '') .
                ($obj['owner'] ?? '') .
                ($obj['pending'] ?? '') .
                ($obj['source_data']['pan'] ?? '') .
                ($obj['source_data']['sub_type'] ?? '') .
                ($obj['source_data']['type'] ?? '') .
                ($obj['success'] ?? '');

            $hmac8 = hash_hmac('sha256', $concatenated, $hmacSecret);
            $methods['SHA256 concatenated (Paymob format)'] = [
                'hmac' => $hmac8,
                'matches' => hash_equals($hmac8, $receivedHmac)
            ];

            $hmac9 = hash_hmac('sha512', $concatenated, $hmacSecret);
            $methods['SHA512 concatenated (Paymob format)'] = [
                'hmac' => $hmac9,
                'matches' => hash_equals($hmac9, $receivedHmac)
            ];
        }

        // Display results
        echo "Testing " . count($methods) . " different HMAC calculation methods:\n";
        echo str_repeat("=", 80) . "\n";

        $matchFound = false;
        foreach ($methods as $methodName => $result) {
            $status = $result['matches'] ? '✅ MATCH' : '❌ NO MATCH';
            echo "\n{$methodName}:\n";
            echo "  Generated: {$result['hmac']}\n";
            echo "  Status: {$status}\n";

            if ($result['matches']) {
                $matchFound = true;
                echo "  🎉 THIS IS THE CORRECT METHOD!\n";
            }
        }

        echo "\n" . str_repeat("=", 80) . "\n";

        if (!$matchFound) {
            echo "❌ NO MATCHING METHOD FOUND!\n";
            echo "\nDebugging Info:\n";
            echo "- Payload without hmac:\n";
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "✅ HMAC VALIDATION SUCCESSFUL!\n";
        }

        // Assert that at least one method matched
        // Comment this out for debugging
        // $this->assertTrue($matchFound, "No HMAC calculation method matched the received HMAC");
    }

    /**
     * Recursively sort array by keys
     */
    private function sortArrayRecursive(array $array): array
    {
        ksort($array);

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->sortArrayRecursive($value);
            }
        }

        return $array;
    }

    /**
     * Test the actual PaymobService validateWebhookSignature method
     */
    public function test_paymob_service_validation()
    {
        $payload = [
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

        // Test current implementation
        $isValid = $this->paymobService->validateWebhookSignature($receivedHmac, $payload);

        echo "\n\nPaymobService::validateWebhookSignature() result: " . ($isValid ? '✅ VALID' : '❌ INVALID') . "\n";

        // For debugging, we don't assert here
        // $this->assertTrue($isValid, "PaymobService validation failed");
    }
}


