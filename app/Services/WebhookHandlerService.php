<?php

namespace App\Services;

use App\Models\PaymentLog;
use App\Repositories\PaymentRepository;
use Exception;

class WebhookHandlerService
{
    public function __construct(
        private PaymentRepository $paymentRepository
    ) {}

    /**
     * Handle webhook callback and update transaction/intention
     */
    public function handleWebhook(array $data): array
    {
        try {
            PaymentLog::info('Processing webhook', [
                'webhook_type' => $data['type'] ?? 'unknown'
            ], null, null, null, 'webhook_processing');

            $transactionData = $this->extractTransactionData($data);

            if (!$transactionData['transaction_id']) {
                PaymentLog::warning('No transaction ID in webhook', [
                    'webhook_data' => $data
                ], null, null, null, 'webhook_no_transaction_id');

                return [
                    'success' => false,
                    'message' => 'No transaction ID provided'
                ];
            }

            $transaction = $this->paymentRepository->findTransactionByTransactionId($transactionData['transaction_id']);

            if ($transaction) {
                $this->updateExistingTransaction($transaction, $transactionData);
            } else {
                $this->logMissingTransaction($transactionData);
            }

            return [
                'success' => true,
                'message' => 'Webhook processed successfully'
            ];

        } catch (Exception $e) {
            PaymentLog::error('Webhook processing exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'webhook_data' => $data
            ], null, null, null, 'webhook_exception');

            return [
                'success' => false,
                'error' => 'An error occurred while processing webhook',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract transaction data from webhook
     */
    private function extractTransactionData(array $data): array
    {
        $obj = $data['obj'] ?? [];

        return [
            'transaction_id' => $obj['id'] ?? null,
            'status' => ($obj['success'] ?? false) ? 'successful' : 'failed',
            'merchant_order_id' => $obj['merchant_order_id'] ?? null,
            'paymob_response' => $data,
        ];
    }

    /**
     * Update existing transaction
     */
    private function updateExistingTransaction($transaction, array $data): void
    {
        $this->paymentRepository->updateTransaction($transaction, [
            'status' => $data['status'],
            'merchant_order_id' => $data['merchant_order_id'],
            'paymob_response' => $data['paymob_response'],
            'processed_at' => now(),
        ]);

        PaymentLog::info('Transaction updated from webhook', [
            'transaction_id' => $data['transaction_id'],
            'status' => $data['status'],
            'user_id' => $transaction->user_id
        ], $transaction->user_id, $transaction->payment_intention_id, $transaction->id, 'webhook_transaction_update');

        // Update intention status
        $this->updateIntentionStatus($transaction, $data['status']);
    }

    /**
     * Update intention status based on transaction
     */
    private function updateIntentionStatus($transaction, string $status): void
    {
        $intention = $transaction->paymentIntention;

        if (!$intention) {
            return;
        }

        $intentionStatus = $status === 'successful' ? 'completed' : 'failed';

        $this->paymentRepository->updateIntention($intention, [
            'status' => $intentionStatus
        ]);

        PaymentLog::info('Payment intention updated from webhook', [
            'intention_id' => $intention->id,
            'status' => $intentionStatus,
            'user_id' => $intention->user_id
        ], $intention->user_id, $intention->id, $transaction->id, 'webhook_intention_update');
    }

    /**
     * Log missing transaction
     */
    private function logMissingTransaction(array $data): void
    {
        PaymentLog::warning('Transaction not found for webhook', [
            'transaction_id' => $data['transaction_id'],
            'status' => $data['status']
        ], null, null, null, 'webhook_transaction_not_found');
    }
}

