<?php

namespace App\Services;

use App\Support\CurrentProfile;
use App\WalletDepositSourceEnum;
use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * WalletService - Centralized Wallet Operations Service
 *
 * ⚠️ CRITICAL RULE FOR ALL AI ASSISTANTS AND DEVELOPERS ⚠️
 *
 * ALL wallet operations MUST go through this service. DO NOT call wallet methods directly.
 *
 * ✅ CORRECT USAGE:
 *   - Use WalletService::depositToWallet() instead of $wallet->deposit()
 *   - Use WalletService::withdrawFromWallet() instead of $wallet->withdraw()
 *   - Use WalletService::getWalletBalance() instead of $wallet->balance or $wallet->getWalletBalance()
 *   - Use WalletService::getWalletTransactions() instead of $wallet->transactions()
 *
 * ❌ WRONG USAGE (DO NOT DO THIS):
 *   - $wallet->deposit($amount) ❌
 *   - $wallet->withdraw($amount) ❌
 *   - $wallet->balance ❌
 *   - $wallet->getWalletBalance() ❌
 *   - $wallet->transactions() ❌
 *
 * REASON:
 * This service provides:
 * - Centralized transaction management (DB transactions)
 * - Consistent error handling
 * - Unified balance calculation logic
 * - Easy maintenance (change once, affects everywhere)
 * - Better testing and debugging
 *
 * ⚠️ IMPORTANT: depositToWallet() REQUIRES 'source' field in $meta array
 * Use WalletDepositSourceEnum for type-safe source values
 *
 * If you need to modify wallet behavior, modify THIS service only.
 * All changes will automatically apply to the entire codebase.
 *
 * @package App\Services
 */
class WalletService
{
    protected $currentProfile;

    public function __construct(CurrentProfile $currentProfile)
    {
        $this->currentProfile = $currentProfile;
    }

    /**
     * الحصول على المحفظة النشطة من البروفايل الحالي
     */
    public function getActiveWallet()
    {
        if (!$this->currentProfile->model) {
            throw new Exception('لا يوجد بروفايل نشط');
        }

        return $this->currentProfile->model;
    }

    /**
     * إيداع مبلغ إلى المحفظة النشطة
     */
    public function deposit(float $amount, array $meta = []): bool
    {
        $wallet = $this->getActiveWallet();
        return $this->depositToWallet($wallet, $amount, $meta);
    }

    /**
     * سحب مبلغ من المحفظة النشطة
     */
    public function withdraw(float $amount, array $meta = []): bool
    {
        $wallet = $this->getActiveWallet();
        return $this->withdrawFromWallet($wallet, $amount, $meta);
    }

    /**
     * Deposit amount to a specific wallet
     * إيداع مبلغ إلى محفظة محددة
     *
     * ⚠️ USE THIS METHOD instead of $wallet->deposit() directly
     * This ensures proper transaction handling and error management.
     *
     * ⚠️ REQUIRED: 'source' field must be provided in $meta array
     *
     * Use WalletDepositSourceEnum for source values:
     *   - WalletDepositSourceEnum::PAYMENT_GATEWAY: For deposits from payment gateways (Paymob, etc.)
     *   - WalletDepositSourceEnum::DASHBOARD: For admin deposits from dashboard/admin operations
     *   - WalletDepositSourceEnum::BANK_TRANSFER: For bank transfer deposits
     *   - WalletDepositSourceEnum::WITHDRAWAL_REFUND: For refunds when withdrawal is rejected
     *   - WalletDepositSourceEnum::PROFIT_DISTRIBUTION: For profit/returns distribution
     *   - WalletDepositSourceEnum::API: For API deposits
     *
     * @param mixed $wallet The wallet instance (InvestorProfile or OwnerProfile)
     * @param float $amount Amount to deposit
     * @param array $meta Metadata for the transaction (MUST include 'source' field)
     * @return bool True on success
     * @throws Exception On failure or if 'source' is missing
     */
    public function depositToWallet($wallet, float $amount, array $meta = []): bool
    {
        // Validate that 'source' is provided
        if (!isset($meta['source']) || empty($meta['source'])) {
            throw new Exception('Source field is required in meta for depositToWallet. Use WalletDepositSourceEnum values.');
        }

        // Handle Enum or string value - convert Enum to string value
        $sourceValue = $meta['source'];
        if ($sourceValue instanceof WalletDepositSourceEnum) {
            $sourceValue = $sourceValue->value;
        }

        if (!WalletDepositSourceEnum::isValid($sourceValue)) {
            throw new Exception('Invalid source value. Must be one of: ' . implode(', ', WalletDepositSourceEnum::values()));
        }

        // Validate required fields based on source type
        $this->validateSourceSpecificFields($sourceValue, $meta);

        // Ensure source is always set as string value
        $meta['source'] = $sourceValue;

        try {
            DB::beginTransaction();

            $transaction = $wallet->deposit($amount, $meta);

            DB::commit();
            return true;
        } catch (AmountInvalid $e) {
            DB::rollBack();
            throw new Exception('المبلغ غير صالح: ' . $e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('فشل في الإيداع: ' . $e->getMessage());
        }
    }

    /**
     * Validate required fields based on source type
     * التحقق من الحقول المطلوبة بناءً على نوع المصدر
     *
     * @param string $source The source value
     * @param array $meta The metadata array
     * @throws Exception If required fields are missing
     */
    protected function validateSourceSpecificFields(string $source, array $meta): void
    {
        $sourceEnum = WalletDepositSourceEnum::from($source);

        switch ($sourceEnum) {
            case WalletDepositSourceEnum::DASHBOARD:
                if (!isset($meta['admin_user_id']) || empty($meta['admin_user_id'])) {
                    throw new Exception('admin_user_id is required when source is DASHBOARD');
                }
                break;

            case WalletDepositSourceEnum::PAYMENT_GATEWAY:
                if (!isset($meta['payment_id']) || empty($meta['payment_id'])) {
                    throw new Exception('payment_id is required when source is PAYMENT_GATEWAY');
                }
                break;

            case WalletDepositSourceEnum::BANK_TRANSFER:
                if (!isset($meta['bank_transfer_request_id']) || empty($meta['bank_transfer_request_id'])) {
                    throw new Exception('bank_transfer_request_id is required when source is BANK_TRANSFER');
                }
                break;

            case WalletDepositSourceEnum::WITHDRAWAL_REFUND:
                if (!isset($meta['withdrawal_request_id']) || empty($meta['withdrawal_request_id'])) {
                    throw new Exception('withdrawal_request_id is required when source is WITHDRAWAL_REFUND');
                }
                break;

            case WalletDepositSourceEnum::PROFIT_DISTRIBUTION:
                if (!isset($meta['investment_id']) || empty($meta['investment_id'])) {
                    throw new Exception('investment_id is required when source is PROFIT_DISTRIBUTION');
                }
                if (!isset($meta['distribution_id']) || empty($meta['distribution_id'])) {
                    throw new Exception('distribution_id is required when source is PROFIT_DISTRIBUTION');
                }
                break;

            case WalletDepositSourceEnum::API:
                // API deposits might not require specific fields, but user_id is recommended
                // No strict validation for API
                break;
        }
    }

    /**
     * Withdraw amount from a specific wallet
     * سحب مبلغ من محفظة محددة
     *
     * ⚠️ USE THIS METHOD instead of $wallet->withdraw() directly
     * This ensures proper transaction handling, balance validation, and error management.
     *
     * @param mixed $wallet The wallet instance (InvestorProfile or OwnerProfile)
     * @param float $amount Amount to withdraw
     * @param array $meta Metadata for the transaction
     * @return bool True on success
     * @throws Exception On failure (InsufficientFunds, BalanceIsEmpty, etc.)
     */
    public function withdrawFromWallet($wallet, float $amount, array $meta = []): bool
    {
        try {
            DB::beginTransaction();

            $transaction = $wallet->withdraw($amount, $meta);

            DB::commit();
            return true;
        } catch (AmountInvalid $e) {
            DB::rollBack();
            throw new Exception('المبلغ غير صالح: ' . $e->getMessage());
        } catch (InsufficientFunds $e) {
            DB::rollBack();
            throw new Exception('الرصيد غير كافي: ' . $e->getMessage());
        } catch (BalanceIsEmpty $e) {
            DB::rollBack();
            throw new Exception('المحفظة فارغة: ' . $e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('فشل في السحب: ' . $e->getMessage());
        }
    }

    /**
     * تحويل مبلغ إلى محفظة أخرى
     */
    public function transfer($toWallet, float $amount, array $meta = []): bool
    {
        $fromWallet = $this->getActiveWallet();

        try {
            DB::beginTransaction();

            $transfer = $fromWallet->transfer($toWallet, $amount, $meta);

            DB::commit();
            return true;
        } catch (AmountInvalid $e) {
            DB::rollBack();
            throw new Exception('المبلغ غير صالح: ' . $e->getMessage());
        } catch (InsufficientFunds $e) {
            DB::rollBack();
            throw new Exception('الرصيد غير كافي: ' . $e->getMessage());
        } catch (BalanceIsEmpty $e) {
            DB::rollBack();
            throw new Exception('المحفظة فارغة: ' . $e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('فشل في التحويل: ' . $e->getMessage());
        }
    }

    /**
     * الحصول على رصيد المحفظة النشطة
     */
    public function getBalance(): float
    {
        $wallet = $this->getActiveWallet();
        return $wallet->balance;
    }

    /**
     * الحصول على معاملات المحفظة النشطة
     */
    public function getTransactions(int $perPage = 15)
    {
        $wallet = $this->getActiveWallet();
        return $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get balance of a specific wallet
     * الحصول على رصيد محفظة محددة
     *
     * ⚠️ USE THIS METHOD instead of $wallet->balance or $wallet->getWalletBalance() directly
     * This ensures consistent balance calculation across the entire application.
     *
     * Use this method consistently across all APIs to ensure the same balance value.
     * This method uses the same logic as InvestorProfile::getWalletBalance() for consistency.
     *
     * @param mixed $wallet The wallet instance (InvestorProfile or OwnerProfile)
     * @return float Wallet balance (0.0 if wallet doesn't exist)
     */
    public function getWalletBalance($wallet): float
    {
        // Check if wallet exists
        if (!$wallet) {
            return 0.0;
        }

        // Use the same logic as InvestorProfile::getWalletBalance() for consistency
        // Check if wallet has the hasWallet method (for InvestorProfile/OwnerProfile)
        if (method_exists($wallet, 'hasWallet')) {
            return $wallet->hasWallet() ? ($wallet->balance ?? 0.0) : 0.0;
        }

        // If wallet doesn't have hasWallet method, try to get balance directly
        // This handles cases where the wallet might be a direct wallet model
        return $wallet->balance ?? 0.0;
    }

    /**
     * Get transactions for a specific wallet
     * الحصول على معاملات محفظة محددة
     *
     * ⚠️ USE THIS METHOD instead of $wallet->transactions() directly
     * This ensures consistent pagination and ordering.
     *
     * @param mixed $wallet The wallet instance (InvestorProfile or OwnerProfile)
     * @param int $perPage Number of transactions per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getWalletTransactions($wallet, int $perPage = 15)
    {
        return $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * التحقق مما إذا كان البروفايل الحالي يمتلك محفظة
     */
    public function hasWallet(): bool
    {
        return $this->currentProfile->model && method_exists($this->currentProfile->model, 'hasWallet');
    }

    /**
     * إنشاء محفظة للبروفايل الحالي (إذا لم تكن موجودة)
     */
    public function createWallet(array $attributes = []): bool
    {
        if (!$this->hasWallet()) {
            throw new Exception('البروفايل الحالي لا يدعم المحافظ');
        }

        try {
            $wallet = $this->currentProfile->model->createWallet($attributes);
            return true;
        } catch (Exception $e) {
            throw new Exception('فشل في إنشاء المحفظة: ' . $e->getMessage());
        }
    }
}
