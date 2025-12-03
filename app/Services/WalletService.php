<?php

namespace App\Services;

use App\Support\CurrentProfile;
use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Illuminate\Support\Facades\DB;
use Exception;

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
     * إيداع مبلغ إلى محفظة محددة
     */
    public function depositToWallet($wallet, float $amount, array $meta = []): bool
    {
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
     * سحب مبلغ من محفظة محددة
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
     * الحصول على رصيد محفظة محددة
     * Use this method consistently across all APIs to ensure the same balance value
     * This method uses the same logic as InvestorProfile::getWalletBalance() for consistency
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
     * الحصول على معاملات محفظة محددة
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
