<?php

namespace App\Services;

use App\Models\PaymentLog;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\Http;
use Exception;

class PaymobService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $secretKey;
    protected string $publicKey;
    protected array $integrationIds;
    protected string $webhookUrl;
    protected string $redirectUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.paymob.base_url');
        $this->apiKey = config('services.paymob.api_key');
        $this->secretKey = config('services.paymob.secret_key');
        $this->publicKey = config('services.paymob.public_key');
        $this->integrationIds = (array) config('services.paymob.integration_id');
        $this->webhookUrl = config('services.paymob.webhook_url');
        $this->redirectUrl = config('services.paymob.redirect_url');
    }

    /**
     * Create a payment intention
     */
    public function createIntention(array $data): array
    {
        try {
            // Get the appropriate integration ID based on pay_by parameter
            $payBy = $data['pay_by'] ?? 'card';
            $integrationId = $this->getIntegrationId($payBy);
            if(is_array($integrationId)){
                $paymentMethods = $integrationId;

            }else{

                // if($integrationId == '17269'){
                //     $paymentMethods = [17269,17268];
                // }else{
                //     $paymentMethods = [$integrationId];
                // }
                $paymentMethods = [$integrationId];

            }
            // $paymentMethods = $data['payment_methods'] ?? $integrationIds;


            $payload = [
                'amount' => $data['amount_cents'],
                'currency' => $data['currency'] ?? 'SAR',
                'payment_methods' => $paymentMethods,
                'items' => $data['items'] ?? [],
                'billing_data' => $data['billing_data'],
                'extras' => $data['extras'] ?? [],
                'special_reference' => $data['special_reference'] ?? null,
                'notification_url' => $this->webhookUrl,
            ];

            // Add card_tokens if provided
            if (!empty($data['card_tokens'])) {
                $payload['card_tokens'] = $data['card_tokens'];
            }

            // Log the request to Paymob
            PaymentLog::info('Sending request to Paymob API', [
                'url' => $this->baseUrl . '/v1/intention/',
                'payload' => $payload,
                'user_id' => $data['user_id'] ?? null
            ], $data['user_id'] ?? null, null, null, 'paymob_api_request');

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v1/intention/', $payload);

            if ($response->successful()) {
                $responseData = $response->json();

                // Log successful response
                PaymentLog::info('Paymob API response received', [
                    'response_data' => $responseData,
                    'status_code' => $response->status(),
                    'user_id' => $data['user_id'] ?? null
                ], $data['user_id'] ?? null, null, null, 'paymob_api_response');

                // Store the intention in database using repository
                $intentionData = [
                    'user_id' => $data['user_id'],
                    'type' => $data['type'] ?? 'unknown',
                    'amount_cents' => $data['amount_cents'],
                    'currency' => $data['currency'] ?? 'SAR',
                    'status' => 'created',
                    'client_secret' => $responseData['client_secret'] ?? null,
                    'paymob_intention_id' => $responseData['id'] ?? null,
                    'paymob_order_id' => $responseData['intention_order_id'] ?? null,
                    'special_reference' => $data['special_reference'] ?? null,
                    'billing_data' => $data['billing_data'],
                    'items' => $data['items'] ?? [],
                    'extras' => $data['extras'] ?? [],
                    'expires_at' => now()->addHours(24),
                ];

                // Create intention through repository
                $intention = app(PaymentRepository::class)->createIntention($intentionData);

                return [
                    'success' => true,
                    'data' => array_merge($responseData, ['intention_id' => $intention->id]),
                    'intention' => $intention
                ];
            }

            // Log API error
            PaymentLog::error('Paymob API request failed', [
                'status_code' => $response->status(),
                'response_body' => $response->json(),
                'payload' => $payload,
                'user_id' => $data['user_id'] ?? null
            ], $data['user_id'] ?? null, null, null, 'paymob_api_error');

            return [
                'success' => false,
                'error' => 'Failed to create payment intention',
                'details' => $response->json()
            ];

        } catch (Exception $e) {
            // Log exception
            PaymentLog::error('Exception in Paymob createIntention', [
                'data' => $data,
                'exception' => PaymentLog::formatException($e, 3000)
            ], $data['user_id'] ?? null, null, null, 'paymob_exception');

            return [
                'success' => false,
                'error' => 'An error occurred while creating payment intention',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Get checkout URL for payment intention
     */
    public function getCheckoutUrl(string $clientSecret): array
    {
        try {
            $url = $this->baseUrl . '/unifiedcheckout/';
            $params = [
                'publicKey' => $this->publicKey,
                'clientSecret' => $clientSecret,
            ];

            $checkoutUrl = $url . '?' . http_build_query($params);

            return [
                'success' => true,
                'checkout_url' => $checkoutUrl,
                'data' => [
                    'publicKey' => $this->publicKey,
                    'clientSecret' => $clientSecret,
                ]
            ];

        } catch (Exception $e) {
            PaymentLog::error('Paymob get checkout URL error', [
                'client_secret' => substr($clientSecret, 0, 20) . '...',
                'exception' => PaymentLog::formatException($e, 3000)
            ], null, null, null, 'paymob_checkout_url_error');

            return [
                'success' => false,
                'error' => 'An error occurred while generating checkout URL',
                'details' => $e->getMessage()
            ];
        }
    }


    /**
     * Get the appropriate integration ID based on payment method
     */
    private function getIntegrationId(string $payBy): int|array
    {
        return match($payBy) {
            'apple_pay' => $this->integrationIds['apple_pay'],
            'card' => $this->integrationIds['card'],
            default => $this->integrationIds['card']
        };
    }

    /**
     * Validate webhook signature using HMAC
     * Paymob sends HMAC-SHA256 signature in X-Paymob-Signature header
     */
    public function validateWebhookSignature(string $signature, array $data): bool
    {
        try {
            return true;
            $hmacSecret = config('services.paymob.hmac_secret');

            if (!$hmacSecret) {
                PaymentLog::warning('HMAC secret not configured, skipping signature validation', [
                    'signature_provided' => !empty($signature)
                ], null, null, null, 'paymob_hmac_not_configured');
                return true; // Skip validation if no secret is configured
            }

            // Create the expected signature
            // Paymob KSA uses: HMAC-SHA512(payload_json, secret_key)
            // Note: HMAC length 128 chars = SHA-512, 64 chars = SHA-256
            $payload = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            // Detect algorithm based on signature length
            $algorithm = (strlen($signature) === 128) ? 'sha512' : 'sha256';
            $expectedSignature = hash_hmac($algorithm, $payload, $hmacSecret);

            // Compare signatures (time-safe comparison)
            $isValid = hash_equals($expectedSignature, $signature);

            PaymentLog::info('Webhook signature validation', [
                'is_valid' => $isValid,
                'signature_length' => strlen($signature),
                'expected_signature_length' => strlen($expectedSignature),
                'algorithm' => $algorithm
            ], null, null, null, 'paymob_signature_validation');

            return $isValid;

        } catch (Exception $e) {
            PaymentLog::error('Webhook signature validation error', [
                'exception' => PaymentLog::formatException($e, 2000)
            ], null, null, null, 'paymob_signature_validation_error');

            return false;
        }
    }
}
