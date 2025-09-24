<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use App\Models\InvestorProfile;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InvestmentException;
use App\Events\InvestmentCreated;
use Exception;

class InvestmentService
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Execute an investment with wallet integration.
     * تنفيذ استثمار مع دمج المحفظة
     *
     * @param InvestorProfile $investor
     * @param InvestmentOpportunity $opportunity
     * @param int $shares
     * @param string $investmentType
     * @return Investment
     * @throws InvestmentException
     */
    public function invest(InvestorProfile $investor, InvestmentOpportunity $opportunity, int $shares, string $investmentType = 'myself'): Investment
    {
        // Validate investment opportunity
        $this->validateInvestmentOpportunity($opportunity);

        // Validate investor eligibility
        $this->validateInvestorEligibility($investor, $opportunity);

        // Check if user already has an investment in this opportunity
        $existingInvestment = $this->getExistingInvestment($investor, $opportunity);

        if ($existingInvestment) {
            // Update existing investment
            return $this->updateExistingInvestment($existingInvestment, $shares, $opportunity);
        } else {
            // Create new investment
            return $this->createNewInvestment($investor, $opportunity, $shares, $investmentType);
        }
    }

    /**
     * Get existing investment for user in this opportunity
     * الحصول على الاستثمار الموجود للمستخدم في هذه الفرصة
     */
    protected function getExistingInvestment(InvestorProfile $investor, InvestmentOpportunity $opportunity): ?Investment
    {
        return $investor->investments()
            ->where('opportunity_id', $opportunity->id)
            ->first();
    }

    /**
     * Update existing investment by adding more shares
     * تحديث الاستثمار الموجود بإضافة المزيد من الأسهم
     */
    protected function updateExistingInvestment(Investment $existingInvestment, int $additionalShares, InvestmentOpportunity $opportunity): Investment
    {
        // Validate additional shares
        $this->validateShares($opportunity, $additionalShares, $existingInvestment);

        $additionalAmount = $additionalShares * $opportunity->price_per_share;

        return DB::transaction(function () use ($existingInvestment, $additionalShares, $additionalAmount, $opportunity) {
            // Process wallet payment for additional shares
            $this->processWalletPayment($existingInvestment->investor, $additionalAmount, $opportunity);

            // Update existing investment
            $existingInvestment->shares += $additionalShares;
            $existingInvestment->amount += $additionalAmount;
            $existingInvestment->save();

            // Reserve additional shares
            $opportunity->reserveShares($additionalShares);

            // Check if opportunity is fully funded
            $this->checkAndUpdateOpportunityStatus($opportunity);

            return $existingInvestment;
        });
    }

    /**
     * Create new investment
     * إنشاء استثمار جديد
     */
    protected function createNewInvestment(InvestorProfile $investor, InvestmentOpportunity $opportunity, int $shares, string $investmentType): Investment
    {
        // Validate shares
        $this->validateShares($opportunity, $shares);

        $amount = $shares * $opportunity->price_per_share;

        return DB::transaction(function () use ($investor, $opportunity, $shares, $amount, $investmentType) {
            // Process wallet payment
            $this->processWalletPayment($investor, $amount, $opportunity);

            // Create investment record
            $investment = $this->createInvestmentRecord($investor, $opportunity, $shares, $amount, $investmentType);

            // Reserve shares
            $opportunity->reserveShares($shares);

            // Check if opportunity is fully funded
            $this->checkAndUpdateOpportunityStatus($opportunity);

            // Dispatch event for notification or analytics
            event(new InvestmentCreated($investment));

            return $investment;
        });
    }

    /**
     * Validate investment opportunity eligibility
     * التحقق من أهلية فرصة الاستثمار
     */
    protected function validateInvestmentOpportunity(InvestmentOpportunity $opportunity): void
    {
        if (!$opportunity->isInvestable()) {
            throw InvestmentException::opportunityNotAvailable();
        }

        if ($opportunity->status !== 'open') {
            throw new InvestmentException('الفرصة الاستثمارية غير مفتوحة للاستثمار', 400, 'OPPORTUNITY_NOT_OPEN');
        }
    }

    /**
     * Validate investor eligibility
     * التحقق من أهلية المستثمر
     */
    protected function validateInvestorEligibility(InvestorProfile $investor, InvestmentOpportunity $opportunity): void
    {
        // Prevent investing in own opportunity
        if ($investor->user_id === optional($opportunity->ownerProfile)->user_id) {
            throw InvestmentException::ownOpportunityInvestment();
        }

        // Check if investor has sufficient wallet balance
        try {
            $walletBalance = $this->walletService->getWalletBalance($investor);
        } catch (Exception $e) {
            throw InvestmentException::walletAccessFailed();
        }
    }

    /**
     * Validate shares count
     * التحقق من عدد الأسهم
     * @throws InvestmentException
     */
    protected function validateShares(InvestmentOpportunity $opportunity, int $shares, ?Investment $existingInvestment = null): void
    {
        $minShares = $this->calculateMinShares($opportunity);
        $maxShares = $this->calculateMaxShares($opportunity);

        if ($shares < $minShares) {
            throw InvestmentException::invalidShares($minShares, $maxShares);
        }

        // For existing investments, check total shares (existing + new)
        if ($existingInvestment) {
            $totalShares = $existingInvestment->shares + $shares;
            if ($maxShares !== null && $totalShares > $maxShares) {
                throw new InvestmentException(
                    "الحد الأقصى للأسهم المسموح بها هو {$maxShares} سهم. لديك حالياً {$existingInvestment->shares} سهم",
                    400,
                    'EXCEEDS_MAX_SHARES'
                );
            }
        } else {
            if ($maxShares !== null && $shares > $maxShares) {
                throw InvestmentException::invalidShares($minShares, $maxShares);
            }
        }

        if ($shares > $opportunity->available_shares) {
            throw new InvestmentException('الأسهم المتاحة غير كافية', 400, 'INSUFFICIENT_SHARES_AVAILABLE');
        }
    }

    /**
     * Process wallet payment
     * معالجة الدفع من المحفظة
     */
    protected function processWalletPayment(InvestorProfile $investor, float $amount, InvestmentOpportunity $opportunity): void
    {
        try {
            // Check if investor has sufficient balance
            $balance = $this->walletService->getWalletBalance($investor);

            if ($balance < $amount) {
                // Use the specific insufficient balance exception with 402 status code
                throw InvestmentException::insufficientBalance(
                    number_format($balance, 2),
                    number_format($amount, 2)
                );
            }

            // Withdraw amount from investor's wallet
            $this->walletService->withdrawFromWallet($investor, $amount, [
                'type' => 'investment',
                'opportunity_id' => $opportunity->id,
                'opportunity_name' => $opportunity->name,
                'description' => "استثمار في فرصة: {$opportunity->name}"
            ]);

        } catch (Exception $e) {
            if ($e instanceof InvestmentException) {
                throw $e;
            }
            throw InvestmentException::paymentProcessingFailed($e->getMessage());
        }
    }

    /**
     * Create investment record
     * إنشاء سجل الاستثمار
     */
    protected function createInvestmentRecord(InvestorProfile $investor, InvestmentOpportunity $opportunity, int $shares, float $amount, string $investmentType): Investment
    {
        $investmentData = [
            'investor_id' => $investor->id,
            'opportunity_id' => $opportunity->id,
            'shares' => $shares,
            'amount' => $amount,
            'user_id' => $investor->user_id,
            'investment_type' => $investmentType,
            'status' => 'active',
            'investment_date' => now(),
            'merchandise_status' => 'pending',
            'distribution_status' => 'pending',
        ];

        // Set expected delivery date for myself investments
        if ($investmentType === 'myself' && $opportunity->investment_duration) {
            $investmentData['expected_delivery_date'] = now()->addDays($opportunity->investment_duration);
        }

        return Investment::create($investmentData);
    }

    /**
     * Check and update opportunity status if fully funded
     * التحقق وتحديث حالة الفرصة إذا تم تمويلها بالكامل
     */
    protected function checkAndUpdateOpportunityStatus(InvestmentOpportunity $opportunity): void
    {
        // Refresh the opportunity to get updated reserved_shares
        $opportunity->refresh();

        // Update status dynamically based on current conditions
        $opportunity->updateDynamicStatus();
    }

    /**
     * Calculate the minimum number of shares allowed.
     * حساب الحد الأدنى من الأسهم المسموح بها
     */
    protected function calculateMinShares(InvestmentOpportunity $opportunity): int
    {
        if ($opportunity->price_per_share <= 0) {
            throw new InvestmentException('سعر السهم غير صالح'); // Invalid share price
        }
        // min_investment is now stored as number of shares, not currency
        return max(1, (int) $opportunity->min_investment);
    }

    /**
     * Calculate the maximum number of shares allowed (if set).
     * حساب الحد الأقصى من الأسهم المسموح بها (إذا تم تعيينها)
     */
    protected function calculateMaxShares(InvestmentOpportunity $opportunity): ?int
    {
        if (!$opportunity->max_investment) {
            return null;
        }

        // max_investment is now stored as number of shares, not currency
        return (int) $opportunity->max_investment;
    }

    /**
     * Get investment history for an investor
     * الحصول على تاريخ الاستثمارات لمستثمر
     */
    public function getInvestmentHistory(InvestorProfile $investor, int $perPage = 15)
    {
        return $investor->investments()
            ->with(['investmentOpportunity.category', 'investmentOpportunity.ownerProfile'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get investment statistics for an investor
     * الحصول على إحصائيات الاستثمار لمستثمر
     */
    public function getInvestmentStatistics(InvestorProfile $investor): array
    {
        $investments = $investor->investments();

        return [
            'total_investments' => $investments->count(),
            'total_amount_invested' => $investments->sum('amount'),
            'total_shares' => $investments->sum('shares'),
            'active_investments' => $investments->where('status', 'active')->count(),
            'completed_investments' => $investments->where('status', 'completed')->count(),
        ];
    }
}
