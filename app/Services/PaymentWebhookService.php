<?php

namespace App\Services;

use App\DataTransferObjects\PaymobWebhookData;
use App\Enums\PaymentIntentionStatusEnum;
use App\Models\PaymentLog;
use App\Models\User;
use App\Models\InvestmentOpportunity;
use App\Repositories\PaymentRepository;
use App\WalletDepositSourceEnum;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentWebhookService
{
    protected $walletService;

    public function __construct(
        private PaymentRepository $paymentRepository,
        WalletService $walletService
    ) {
        $this->walletService = $walletService;
    }

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

                $saveResponse = $verification['save_response'] ?? true;

                // Save paymob_response even if verification failed (if saveResponse is true)
                if ($saveResponse) {
                    $this->saveResponseEvenIfFailed($webhook);
                }

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
     * Save paymob_response even if verification failed
     * Uses same logic as updateIntentionWithTransaction but without executing transaction
     * Only saves if is_executed is false and no previous successful status exists
     */
    private function saveResponseEvenIfFailed(PaymobWebhookData $webhook): void
    {
        $intention = $webhook->getPaymentIntention();

        if ($intention && !$intention->isExecuted() && $intention->status !== PaymentIntentionStatusEnum::COMPLETED) {
            $this->paymentRepository->updateIntention($intention, [
                'status' => $webhook->getIntentionStatus(),
                'transaction_id' => $webhook->getTransactionId(),
                'merchant_order_id' => $webhook->getMerchantOrderId(),
                'payment_method' => $webhook->getPaymentMethod(),
                'paymob_response' => $webhook->getRawData(),
                'processed_at' => now(),
            ]);
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
        if ($webhook->isSuccessful() && !$intention->isExecuted()) {
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
            $this->walletService->depositToWallet($wallet, $amountSar, [
                'source' => WalletDepositSourceEnum::PAYMENT_GATEWAY,
                'type' => 'payment_gateway',
                'payment_id' => $intention->id,
                // 'transaction_id' => $intention->transaction_id,
                'description' => 'Wallet charge from payment gateway'
            ]);

            // Get balance using WalletService
            $newBalance = $this->walletService->getWalletBalance($wallet);

            // Send notification
            $user->notify(new \App\Notifications\WalletRechargedNotification(
                $amountSar,
                $newBalance,
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
                'new_balance' => $newBalance
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
     * تنفيذ استثمار: شحن الرصيد في المحفظة أولاً ثم سحبه لشراء الفرصة
     *
     * ⚠️ IMPORTANT FOR AI ASSISTANTS AND DEVELOPERS ⚠️
     *
     * This method MUST use TWO SEPARATE database transactions:
     *
     * 1. FIRST TRANSACTION: Wallet deposit + Intention update
     *    - Deposit the payment amount to investor's wallet
     *    - Mark the intention as executed (is_executed = true)
     *    - If this succeeds, the money is in the wallet and intention is marked as executed
     *    - This transaction MUST be committed independently
     *
     * 2. SECOND TRANSACTION: Investment execution
     *    - Execute the investment using InvestmentService::invest()
     *    - This will withdraw from wallet and purchase shares
     *    - This transaction is separate and independent
     *
     * WHY THIS DESIGN?
     * - If investment fails (e.g., shares sold out), the money remains in the wallet
     * - The intention is already marked as executed, so we know payment was received
     * - The wallet balance can be refunded or used for other purposes
     * - This prevents rollback of the wallet deposit if investment fails
     *
     * DO NOT combine these into a single transaction!
     * DO NOT rollback the first transaction if the second fails!
     *
     * العملية منفصلة إلى خطوتين:
     * 1. شحن الرصيد + تحديث Intention (transaction منفصلة)
     * 2. الاستثمار (سحب الرصيد وشراء الأسهم) - transaction منفصلة
     *
     * إذا فشل الاستثمار (مثلاً الأسهم خلصت)، الرصيد يبقى في المحفظة
     */
    private function executeInvestment($intention): void
    {
        $extras = $intention->extras ?? [];
        $opportunityId = $extras['opportunity_id'] ?? null;
        $shares = $extras['shares'] ?? null;
        $amountSar = $intention->amount_cents / 100;

        try {
            if (!$opportunityId || !$shares) {
                PaymentLog::error('Missing investment data', [
                    'extras' => $extras,
                    'payment_id' => $intention->id
                ], $intention->user_id, $intention->id, null, 'investment_missing_data');
                return;
            }

            // Get investor profile
            $investor = \App\Models\InvestorProfile::where('user_id', $intention->user_id)->first();

            if (!$investor) {
                PaymentLog::error('Investor profile not found', [
                    'user_id' => $intention->user_id,
                    'payment_id' => $intention->id
                ], $intention->user_id, $intention->id, null, 'investor_not_found');
                return;
            }

            // Get opportunity
            $opportunity = InvestmentOpportunity::findOrFail($opportunityId);
            $opportunityName = $opportunity->name ?? "الفرصة رقم {$opportunityId}";

            // Step 1: شحن الرصيد في المحفظة + تحديث Intention
            // هذه خطوة منفصلة - إذا نجحت، الرصيد موجود والـ intention محدث
            DB::transaction(function () use ($intention, $opportunityId, $shares, $amountSar, $investor, $opportunityName) {
                // شحن الرصيد في المحفظة
                $this->walletService->depositToWallet($investor, $amountSar, [
                    'source' => WalletDepositSourceEnum::PAYMENT_GATEWAY,
                    'payment_id' => $intention->id,
                    'opportunity_id' => $opportunityId,
                    'shares' => $shares,
                    'description' => "شحن رصيد من بوابة الدفع لشراء فرصة: {$opportunityName}",
                ]);

                // تحديث Intention - العملية تم تنفيذها (الرصيد تم شحنه)
                $this->paymentRepository->updateIntention($intention, [
                    'is_executed' => true
                ]);

                // Get balance using WalletService
                $newBalance = $this->walletService->getWalletBalance($investor);

                PaymentLog::info('Wallet credited for investment', [
                    'user_id' => $intention->user_id,
                    'amount_sar' => $amountSar,
                    'opportunity_id' => $opportunityId,
                    'payment_id' => $intention->id,
                    'new_balance' => $newBalance
                ], $intention->user_id, $intention->id, null, 'wallet_credited_for_investment');
            });

            // Step 2: الاستثمار (سحب الرصيد وشراء الأسهم)
            // هذه خطوة منفصلة - إذا فشلت (مثلاً الأسهم خلصت)، الرصيد يبقى في المحفظة
            try {
                $investmentService = app(InvestmentService::class);
                $investment = $investmentService->invest(
                    investor: $investor,
                    opportunity: $opportunity,
                    shares: $shares,
                    investmentType: $extras['investment_type'] ?? 'myself',
                    skipWalletPayment: false // سحب من المحفظة (التي تم شحنها للتو)
                );

                PaymentLog::info('Investment executed successfully via wallet', [
                    'investment_id' => $investment->id,
                    'user_id' => $intention->user_id,
                    'opportunity_id' => $opportunityId,
                    'shares' => $shares,
                    'amount_sar' => $amountSar,
                    'payment_id' => $intention->id
                ], $intention->user_id, $intention->id, null, 'investment_executed_via_wallet');

            } catch (Exception $investmentException) {
                // في حالة فشل الاستثمار (مثلاً الأسهم خلصت)، الرصيد موجود في المحفظة
                // لا نحتاج لإرجاع المال لأن الرصيد موجود في المحفظة بالفعل
                PaymentLog::error('Investment failed after wallet deposit', [
                    'opportunity_id' => $opportunityId,
                    'shares' => $shares,
                    'amount_sar' => $amountSar,
                    'exception' => PaymentLog::formatException($investmentException, 3000),
                    'note' => 'الرصيد موجود في المحفظة ويمكن استرجاعه'
                ], $intention->user_id, $intention->id, null, 'investment_failed_after_deposit');

                // إعادة رمي الاستثناء للتعامل معه في catch الخارجي
                throw $investmentException;
            }

        } catch (Exception $e) {
            PaymentLog::error('Investment execution failed', [
                'opportunity_id' => $opportunityId,
                'shares' => $shares,
                'amount_sar' => $amountSar,
                'exception' => PaymentLog::formatException($e, 3000)
            ], $intention->user_id, $intention->id, null, 'investment_execution_failed');

            throw $e;
        }
    }

}

