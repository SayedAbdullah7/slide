<?php

namespace App\Services;

use App\DataTransferObjects\PaymobWebhookData;
use App\Models\PaymentLog;
use App\Models\User;
use App\Models\InvestmentOpportunity;
use App\Repositories\PaymentRepository;
use Exception;

class PaymentWebhookService
{
    public function __construct(
        private PaymentRepository $paymentRepository
    ) {}

    // // Extract transaction details
    // $transactionId = $obj['id'] ?? null; /// this is transaction id in paymob and i dont recive it in intention request
    // $orderId = $obj['order']['id'] ?? null; /// this is order id in paymob and i  recive it in intention request
    // $merchantOrderId = $obj['order']['merchant_order_id'] ?? null; // this my cutom order id that i create in and send in intention request
    // $intentionId = $obj['integration_id'] ?? null; // this is intention id in paymob and i recive it in intention request
    // $profileId = $obj['profile_id'] ?? null; // this is profile id in for our account in paymob



    /**
     * Handle webhook callback and update transaction/intention
     */
    public function handleWebhook(array $data): array
    {
        try {
            // Wrap webhook data in DTO for easier access
            $webhook = new PaymobWebhookData($data);

            // Get HMAC secret from config
            $hmacSecret = config('services.paymob.hmac_secret');

            // Verify webhook authenticity and data integrity
            $verification = $webhook->verify($hmacSecret);
            $isValid = $verification['valid'];

            if (!$isValid) {
                PaymentLog::error('Webhook verification failed', [
                    'errors' => $verification['errors'],
                    'warnings' => $verification['warnings'],
                    'transaction_id' => $webhook->getTransactionId(),
                    'order_id' => $webhook->getOrderId()
                ], null, null, null, 'webhook_verification_failed');

                return [
                    'success' => false,
                    'message' => 'Webhook verification failed',
                    'errors' => $verification['errors'],
                    'warnings' => $verification['warnings']
                ];
            }

            // Log webhook details with verification result
            $logData = [
                'transaction_id' => $webhook->getTransactionId(),
                'order_id' => $webhook->getOrderId(),
                'merchant_order_id' => $webhook->getMerchantOrderId(),
                'status' => $webhook->getStatus(),
                'amount_sar' => $webhook->getAmountSar(),
                'payment_method' => $webhook->getPaymentMethod(),
                'verified' => true
            ];

            if (!empty($verification['warnings'])) {
                $logData['warnings'] = $verification['warnings'];
            }

            PaymentLog::info('Webhook received and verified', $logData, $webhook->getUserId(), null, null, 'webhook_received');

            // Find payment using webhook method (cleaner API)
            $intention = $webhook->getPaymentIntention();

            if ($intention) {
                $this->updateIntentionWithTransaction($intention, $webhook);
            } else {
                PaymentLog::warning('Payment not found', [
                    'order_id' => $webhook->getOrderId(),
                    'merchant_order_id' => $webhook->getMerchantOrderId()
                ], null, null, null, 'payment_not_found');
            }

            return [
                'success' => true,
                'message' => 'Webhook processed successfully',
                'transaction_id' => $webhook->getTransactionId()
            ];

        } catch (Exception $e) {
            PaymentLog::error('Webhook exception', [
                'exception' => PaymentLog::formatException($e)
            ], null, null, null, 'webhook_exception');

            return [
                'success' => false,
                'error' => 'An error occurred while processing webhook',
                'details' => PaymentLog::formatException($e)
            ];
        }
    }


    /**
     * Update intention with transaction data
     */
    private function updateIntentionWithTransaction($intention, PaymobWebhookData $webhook): void
    {
        // Update intention with transaction data
        $this->paymentRepository->updateIntention($intention, [
            'status' => $webhook->getIntentionStatus(),
            'transaction_id' => $webhook->getTransactionId(),
            'merchant_order_id' => $webhook->getMerchantOrderId(),
            'payment_method' => $webhook->getPaymentMethod(),
            'paymob_response' => $webhook->getRawData(),
            'processed_at' => now(),
        ]);

        // Execute transaction ONLY if successful AND not already executed
        if ($webhook->isSuccessful() && !$intention->is_executed) {
            $this->executeTransaction($intention);

            PaymentLog::info('Payment completed and executed', [
                'payment_id' => $intention->id,
                'type' => $intention->type,
                'amount_sar' => $webhook->getAmountSar()
            ], $intention->user_id, $intention->id, null, 'payment_completed');
        }
    }

    /**
     * Execute the actual transaction based on intention type
     */
    private function executeTransaction($intention): void
    {
        try {
            match($intention->type) {
                'wallet_charge' => $this->executeWalletCharge($intention),
                'investment' => $this->executeInvestment($intention),
                default => PaymentLog::error('Unknown type', ['type' => $intention->type], $intention->user_id, $intention->id, null, 'unknown_type')
            };
        } catch (Exception $e) {
            PaymentLog::error('Execution failed', [
                'type' => $intention->type,
                'exception' => PaymentLog::formatException($e, 3000)
            ], $intention->user_id, $intention->id, null, 'execution_failed');
        }
    }

    /**
     * Execute wallet charge transaction
     */
    private function executeWalletCharge($intention): void
    {
        try {
            $user = User::findOrFail($intention->user_id);
            $amountSar = $intention->amount_cents / 100;

            // Get user's wallet (InvestorProfile or OwnerProfile)
            $wallet = $user->investorProfile ?? $user->ownerProfile;

            if (!$wallet) {
                throw new Exception('No wallet found for user');
            }

            // Use existing depositToWallet method from WalletService
            $walletService = app(WalletService::class);
            $walletService->depositToWallet($wallet, $amountSar, [
                'type' => 'payment_gateway',
                'source' => 'paymob',
                'payment_id' => $intention->id,
                // 'transaction_id' => $intention->transaction_id,
                'description' => 'Wallet charge from payment gateway'
            ]);

            // Send notification
            $user->notify(new \App\Notifications\WalletRechargedNotification(
                $amountSar,
                $wallet->fresh()->balance,
                'payment_gateway'
            ));

            // Mark as executed
            $this->paymentRepository->updateIntention($intention, [
                'is_executed' => true
            ]);

            PaymentLog::info('Wallet charged successfully', [
                'user_id' => $user->id,
                'amount_sar' => $amountSar,
                'wallet_type' => $wallet instanceof \App\Models\InvestorProfile ? 'investor' : 'owner',
                'new_balance' => $wallet->fresh()->balance
            ], $user->id, $intention->id, null, 'wallet_charged');

        } catch (Exception $e) {
            PaymentLog::error('Wallet charge failed', [
                'amount_sar' => $intention->amount_cents / 100,
                'exception' => PaymentLog::formatException($e, 3000)
            ], $intention->user_id, $intention->id, null, 'wallet_failed');
            throw $e;
        }
    }

    /**
     * Execute investment transaction
     */
    private function executeInvestment($intention): void
    {
        try {
            $extras = $intention->extras ?? [];
            $opportunityId = $extras['opportunity_id'] ?? null;
            $shares = $extras['shares'] ?? null;

            if (!$opportunityId || !$shares) {
                PaymentLog::error('Missing investment data', [
                    'extras' => $extras,
                    'payment_id' => $intention->id
                ], $intention->user_id, $intention->id, null, 'investment_missing_data');
                return;
            }

            $opportunity = InvestmentOpportunity::find($opportunityId);

            if (!$opportunity) {
                PaymentLog::error('Investment opportunity not found', [
                    'opportunity_id' => $opportunityId,
                    'payment_id' => $intention->id
                ], $intention->user_id, $intention->id, null, 'investment_opportunity_not_found');
                return;
            }

            // Use existing invest method from InvestmentService
            $investmentService = app(InvestmentService::class);

            // Get investor profile
            $investor = \App\Models\InvestorProfile::where('user_id', $intention->user_id)->first();

            if (!$investor) {
                PaymentLog::error('Investor profile not found', [
                    'user_id' => $intention->user_id,
                    'payment_id' => $intention->id
                ], $intention->user_id, $intention->id, null, 'investor_not_found');
                return;
            }

            // Create investment (skip wallet payment since payment was already processed via Paymob)
            $investment = $investmentService->invest(
                investor: $investor,
                opportunity: $opportunity,
                shares: $shares,
                investmentType: $extras['investment_type'] ?? 'myself',
                skipWalletPayment: true // Online payment already processed, don't deduct from wallet
            );

            // Mark as executed
            $this->paymentRepository->updateIntention($intention, [
                'is_executed' => true
            ]);

        } catch (Exception $e) {
            PaymentLog::error('Investment failed', [
                'opportunity_id' => $extras['opportunity_id'] ?? null,
                'shares' => $extras['shares'] ?? null,
                'exception' => PaymentLog::formatException($e, 3000)
            ], $intention->user_id, $intention->id, null, 'investment_failed');
            throw $e;
        }
    }

}

