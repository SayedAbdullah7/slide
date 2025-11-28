<?php

namespace App\Repositories;

use App\Models\PaymentIntention;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository
{
    // ========================================
    // CRUD Operations
    // ========================================

    /**
     * Create a payment intention
     */
    public function createIntention(array $data): PaymentIntention
    {
        return PaymentIntention::create($data);
    }

    /**
     * Update payment intention
     */
    public function updateIntention(PaymentIntention $intention, array $data): bool
    {
        return $intention->update($data);
    }

    // ========================================
    // Find Methods
    // ========================================

    /**
     * Find payment intention by Paymob order ID (order.id from Paymob) and merchant order ID (special_reference)
     */
    public function findByPaymobOrderIdAndMerchantOrderId(string $orderId, string $merchantOrderId): ?PaymentIntention
    {
        return PaymentIntention::where('paymob_order_id', $orderId)->where('special_reference', $merchantOrderId)->first();
    }


    /**
     * Find payment intention by ID and user ID
     */
    public function findIntentionByUser(int $id, int $userId): ?PaymentIntention
    {
        return PaymentIntention::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Find payment intention by Paymob intention ID
     */
    public function findByPaymobIntentionId(string $paymobIntentionId): ?PaymentIntention
    {
        return PaymentIntention::where('paymob_intention_id', $paymobIntentionId)->first();
    }

    /**
     * Find payment intention by Paymob order ID (order.id from Paymob)
     */
    public function findByOrderId($orderId): ?PaymentIntention
    {
        return PaymentIntention::where('paymob_order_id', $orderId)->first();
    }

    /**
     * Find payment intention by merchant order ID (special_reference)
     */
    public function findByMerchantOrderId(string $merchantOrderId): ?PaymentIntention
    {
        return PaymentIntention::where('special_reference', $merchantOrderId)->first();
    }

    /**
     * Find payment by transaction ID (Paymob transaction ID)
     */
    public function findByTransactionId($transactionId): ?PaymentIntention
    {
        return PaymentIntention::where('transaction_id', $transactionId)->first();
    }

    /**
     * Find payment intention from webhook transaction data
     *
     * Uses multiple strategies to match the payment:
     * 1. By paymob_order_id (most reliable - order.id from webhook)
     * 2. By special_reference (merchant_order_id)
     * 3. By transaction_id (for duplicate webhooks)
     *
     * @param array $transactionData Webhook transaction data
     * @return PaymentIntention|null
     */
    public function findIntentionFromWebhook(array $transactionData): ?PaymentIntention
    {
        // Strategy 1: By paymob_order_id (most reliable)
        if (!empty($transactionData['order_id'])) {
            $intention = $this->findByOrderId($transactionData['order_id']);
            if ($intention) return $intention;
        }

        // Strategy 2: By special_reference (merchant_order_id)
        if (!empty($transactionData['merchant_order_id'])) {
            $intention = $this->findByMerchantOrderId($transactionData['merchant_order_id']);
            if ($intention) return $intention;
        }

        // Strategy 3: By transaction_id (if webhook is duplicate/retry)
        if (!empty($transactionData['transaction_id'])) {
            $intention = $this->findByTransactionId($transactionData['transaction_id']);
            if ($intention) return $intention;
        }

        return null;
    }

    // ========================================
    // User Payment Queries
    // ========================================

    /**
     * Get payment intentions for user with pagination
     */
    public function getUserIntentions(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PaymentIntention::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        return $this->applyFilters($query, $filters)->paginate($perPage);
    }

    /**
     * Get recent payments for user
     */
    public function getRecentPayments(int $userId, int $limit = 10): Collection
    {
        return PaymentIntention::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get completed payments (transactions) for user with pagination
     */
    public function getUserTransactions(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PaymentIntention::where('user_id', $userId)
            ->whereNotNull('transaction_id')
            ->orderBy('processed_at', 'desc');

        return $this->applyFilters($query, $filters)->paginate($perPage);
    }

    /**
     * Get payment statistics for user
     */
    public function getUserPaymentStats(int $userId): array
    {
        $query = PaymentIntention::where('user_id', $userId);

        return [
            'total_payments' => $query->count(),
            'successful_payments' => $query->clone()->where('status', 'completed')->where('is_executed', true)->count(),
            'failed_payments' => $query->clone()->where('status', 'failed')->count(),
            'pending_payments' => $query->clone()->whereIn('status', ['created', 'active'])->count(),
            'total_amount_cents' => $query->clone()->where('status', 'completed')->sum('amount_cents'),
            'refunded_amount_cents' => $query->clone()->whereNotNull('refunded_at')->sum('refund_amount_cents'),
            'wallet_charges' => $query->clone()->where('type', 'wallet_charge')->where('is_executed', true)->count(),
            'investments' => $query->clone()->where('type', 'investment')->where('is_executed', true)->count(),
        ];
    }

    // ========================================
    // System Queries
    // ========================================

    /**
     * Get expired intentions that need to be marked as expired
     */
    public function getExpiredIntentions(): Collection
    {
        return PaymentIntention::where('expires_at', '<', now())
            ->where('status', '!=', 'expired')
            ->get();
    }

    /**
     * Mark intentions as expired
     */
    public function markIntentionsAsExpired(array $intentionIds): int
    {
        return PaymentIntention::whereIn('id', $intentionIds)
            ->update(['status' => 'expired']);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Apply filters to query
     */
    private function applyFilters($query, array $filters)
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (isset($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        return $query;
    }
}


