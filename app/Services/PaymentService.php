<?php

namespace App\Services;

use App\Models\InvestmentOpportunity;
use App\Models\PaymentLog;
use App\Repositories\PaymentRepository;
use App\Services\InvestmentCalculatorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private PaymentRepository $paymentRepository,
        private PaymobService $paymobService,
        private InvestmentCalculatorService $calculatorService
    ) {}

    /**
     * Create payment intention (unified method)
     */
    public function createIntention(array $data, int $userId, string $type): array
    {
        // Validate pay_by parameter
        $payBy = $data['pay_by'] ?? 'card';
        if (!in_array($payBy, ['card', 'apple_pay'])) {
            throw new \Exception('Invalid pay_by parameter. Must be "card" or "apple_pay"', 400);
        }

        return match($type) {
            'investment' => $this->createInvestmentIntention($data, $userId, $payBy),
            'wallet_charge' => $this->createWalletIntention($data, $userId, $payBy),
            default => throw new \Exception('Invalid intention type', 400)
        };
    }

    /**
     * Create investment payment intention
     *
     * Note: Validation should be done by InvestmentService before calling this
     */
    public function createInvestmentIntention(array $data, int $userId, string $payBy = 'card'): array
    {
        // Add user_id to data array
        $data['user_id'] = $userId;

        // Get opportunity (should already be validated by InvestmentService)
        $opportunity = InvestmentOpportunity::findOrFail($data['opportunity_id']);
        // Create intention
        return $this->processInvestmentIntention($data, $opportunity, $payBy);
    }

    /**
     * Validate and create wallet payment intention
     */
    public function createWalletIntention(array $data, int $userId, string $payBy = 'card'): array
    {
        // Validate request
        $validatedData = $this->validateWalletIntention($data, $userId);

        // Create intention
        return $this->processWalletIntention($validatedData, $payBy);
    }

    /**
     * Validate investment intention request
     */
    private function validateInvestmentIntention(array $data, int $userId): array
    {
        $validator = Validator::make($data, [
            'opportunity_id' => 'required|exists:investment_opportunities,id',
            'shares' => 'required|integer|min:1',
            'investment_type' => 'required|string|in:myself,authorize',
            'pay_by' => 'required|string|in:card,apple_pay',
        ]);

        if ($validator->fails()) {
            PaymentLog::error('Investment intention validation failed', [
                'errors' => $validator->errors()->toArray(),
                'data' => $data
            ], $userId, null, null, 'validation_failed');

            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return [
            'user_id' => $userId,
            'opportunity_id' => $data['opportunity_id'],
            'shares' => $data['shares'],
            'investment_type' => $data['investment_type'],
            'pay_by' => $data['pay_by'],
        ];
    }

    /**
     * Validate wallet intention request
     */
    private function validateWalletIntention(array $data, int $userId): array
    {
        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:0.01',
            'pay_by' => 'required|string|in:card,apple_pay',
        ]);

        if ($validator->fails()) {
            PaymentLog::error('Wallet intention validation failed', [
                'errors' => $validator->errors()->toArray(),
                'data' => $data
            ], $userId, null, null, 'validation_failed');

            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return [
            'user_id' => $userId,
            'amount_cents' => (int) round($data['amount'] * 100),
            'pay_by' => $data['pay_by'],
        ];
    }


    /**
     * Process investment payment intention
     */
    private function processInvestmentIntention(array $data, InvestmentOpportunity $opportunity, string $payBy): array
    {
        // Use calculator service to avoid code duplication (same calculation as InvestmentService)
        $amountSar = $this->calculatorService->calculateInvestmentAmount($data['shares'], $opportunity->share_price);
        $totalPaymentRequired = $this->calculatorService->calculateTotalPaymentRequired($amountSar, $data['shares'], $data['investment_type'], $opportunity);
        $amountCents = (int) ($totalPaymentRequired * 100); // Use totalPaymentRequired for actual payment
        $user = Auth::user();

        $paymobData = [
            'user_id' => $data['user_id'],
            'amount_cents' => $amountCents,
            'currency' => 'SAR',
            'type' => 'investment',
            'pay_by' => $payBy,
            'billing_data' => $this->prepareBillingData($user),
            'items' => [[
                'name' => $opportunity->name,
                'amount' => $amountCents,
                'description' => "Investment in {$opportunity->name} ID {$opportunity->id} - {$data['shares']} shares",
                'quantity' => 1
            ]],
            'special_reference' => "INV-{$data['opportunity_id']}-{$data['user_id']}-" . time(),
            'extras' => [
                'opportunity_id' => $data['opportunity_id'],
                'shares' => $data['shares'],
                'investment_type' => $data['investment_type'],
                'share_price' => $opportunity->share_price,
                'opportunity_name' => $opportunity->name,
                'user_id' => $data['user_id'],
            ],
            'card_tokens' => $this->getUserCardTokens($data['user_id']),
        ];


        PaymentLog::info('Creating investment payment intention', [
            'opportunity_id' => $data['opportunity_id'],
            'shares' => $data['shares'],
            'amount_cents' => $amountCents
        ], $data['user_id'], null, null, 'create_investment_intention');

        $result = $this->paymobService->createIntention($paymobData);

        if ($result['success'] && isset($result['data'])) {
            $publicKey = config('services.paymob.public_key');

            // Return only the required fields
            $result['data'] = [
                'client_secret' => $result['data']['client_secret'] ?? null,
                'public_key' => $publicKey,
            ];
        }


        return $result;
    }

    /**
     * Process wallet payment intention
     */
    private function processWalletIntention(array $data, string $payBy): array
    {
        $user = Auth::user();
        $amountSar = $data['amount_cents'] / 100;

        $paymobData = [
            'user_id' => $data['user_id'],
            'amount_cents' => $data['amount_cents'],
            'currency' => 'SAR',
            'type' => 'wallet_charge',
            'pay_by' => $payBy,
            'billing_data' => $this->prepareBillingData($user),
            'items' => [[
                'name' => 'Wallet Charge',
                'amount' => $data['amount_cents'],
                'description' => "Wallet charging - {$amountSar} SAR",
                'quantity' => 1
            ]],
            'special_reference' => "WALLET-CHARGE-{$data['user_id']}-" . time(),
            'extras' => [
                'operation_type' => 'wallet_charge',
                'amount_sar' => $amountSar,
                'user_id' => $data['user_id'],
            ],
            'card_tokens' => $this->getUserCardTokens($data['user_id']),
        ];

        PaymentLog::info('Creating wallet charge intention', [
            'amount_sar' => $amountSar,
            'amount_cents' => $data['amount_cents']
        ], $data['user_id'], null, null, 'create_wallet_intention');

        $result = $this->paymobService->createIntention($paymobData);

        if ($result['success'] && isset($result['data'])) {
            $publicKey = config('services.paymob.public_key');

            // Return only the required fields
            $result['data'] = [
                'client_secret' => $result['data']['client_secret'] ?? null,
                'public_key' => $publicKey,
            ];
        }

        return $result;
    }

    /**
     * Prepare billing data from user
     */
    private function prepareBillingData($user): array
    {
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();
        if(empty($lastName)) {
            $lastName = $firstName;
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone_number' => $user->phone,
            'email' => $user->email,
            // 'apartment' => 'N/A',
            // 'street' => 'N/A',
            // 'building' => 'N/A',
            // 'city' => 'Riyadh',
            // 'country' => 'Saudi Arabia',
            // 'floor' => 'N/A',
            // 'state' => 'Riyadh'
        ];
    }

    /**
     * Get user's saved card tokens
     */
    private function getUserCardTokens(int $userId): array
    {
        return \App\Models\UserCard::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('last_used_at', 'desc')
            ->pluck('card_token')
            ->toArray();
    }

    /**
     * Extract intention key from payment_keys based on pay_by
     */
    private function extractIntentionKey(array $data, string $payBy): ?string
    {
        $integrationIds = config('services.paymob.integration_id');
        $targetIntegrationId = match($payBy) {
            'apple_pay' => $integrationIds['apple_pay'] ?? null,
            'card' => $integrationIds['card'] ?? null,
            default => $integrationIds['card'] ?? null
        };

        if (!$targetIntegrationId || !isset($data['payment_keys']) || !is_array($data['payment_keys'])) {
            return null;
        }

        // Convert to integer for comparison (config might be string, response is integer)
        $targetIntegrationId = (int) $targetIntegrationId;

        // Find the payment key that matches the target integration ID
        foreach ($data['payment_keys'] as $paymentKey) {
            if (isset($paymentKey['integration']) && (int) $paymentKey['integration'] === $targetIntegrationId) {
                return $paymentKey['key'] ?? null;
            }
        }

        return null;
    }
}

