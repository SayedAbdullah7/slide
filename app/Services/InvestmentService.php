<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use App\Models\InvestorProfile;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\InvestmentException;
use App\Events\InvestmentCreated;
use App\Events\InvestmentUpdated;
use Exception;

class InvestmentService
{
    protected $walletService;
    protected $validationService;
    protected $calculatorService;

    public function __construct(
        WalletService $walletService,
        InvestmentValidationService $validationService,
        InvestmentCalculatorService $calculatorService
    ) {
        $this->walletService = $walletService;
        $this->validationService = $validationService;
        $this->calculatorService = $calculatorService;
    }

    /**
     * Validate investment without processing (for online payment)
     * التحقق من الاستثمار بدون معالجة (للدفع الإلكتروني)
     *
     * @param InvestorProfile $investor
     * @param InvestmentOpportunity $opportunity
     * @param int $shares
     * @param string $investmentType
     * @return void
     * @throws InvestmentException
     */
    public function validateInvestment(InvestorProfile $investor, InvestmentOpportunity $opportunity, int $shares, string $investmentType = 'myself'): void
    {
        // Comprehensive validation (without wallet balance check for online payment)
        $this->validationService->validateInvestmentRequest($investor, $opportunity, $shares, $investmentType, $skipBalanceCheck = true);
    }

    /**
     * Execute an investment with wallet integration.
     * تنفيذ استثمار مع دمج المحفظة
     *
     * @param InvestorProfile $investor
     * @param InvestmentOpportunity $opportunity
     * @param int $shares
     * @param string $investmentType
     * @param bool $skipWalletPayment Skip wallet payment (for online payments)
     * @return Investment
     * @throws InvestmentException
     */
    public function invest(InvestorProfile $investor, InvestmentOpportunity $opportunity, int $shares, string $investmentType = 'myself', bool $skipWalletPayment = false): Investment
    {
        $startTime = microtime(true);

        Log::info('Starting investment process', [
            'investor_id' => $investor->id,
            'opportunity_id' => $opportunity->id,
            'shares' => $shares,
            'investment_type' => $investmentType
        ]);

        try {

            // Comprehensive validation
            $this->validationService->validateInvestmentRequest($investor, $opportunity, $shares, $investmentType);

            // Check for existing investment with the same investment_type (myself or authorize)
            $existingInvestment = $this->getExistingInvestment($investor, $opportunity, $investmentType);

            $investment = $existingInvestment
                ? $this->updateExistingInvestment($existingInvestment, $shares, $opportunity, $skipWalletPayment)
                : $this->createNewInvestment($investor, $opportunity, $shares, $investmentType, $skipWalletPayment);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('Investment process completed successfully', [
                'investment_id' => $investment->id,
                'duration_ms' => $duration,
                'total_amount' => $investment->total_investment,
                'total_payment_required' => $investment->total_payment_required
            ]);

            return $investment;

        } catch (InvestmentException $e) {
            Log::warning('Investment process failed with business logic error', [
                'investor_id' => $investor->id,
                'opportunity_id' => $opportunity->id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage()
            ]);
            throw $e;
        } catch (Exception $e) {
            Log::error('Investment process failed with unexpected error', [
                'investor_id' => $investor->id,
                'opportunity_id' => $opportunity->id,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw InvestmentException::processingFailed($e->getMessage());
        }
    }

    /**
     * Get existing investment for user in this opportunity with the same investment_type
     * الحصول على الاستثمار الموجود للمستخدم في هذه الفرصة بنفس نوع الاستثمار
     *
     * @param InvestorProfile $investor
     * @param InvestmentOpportunity $opportunity
     * @param string $investmentType The investment type (myself or authorize)
     * @return Investment|null
     */
    protected function getExistingInvestment(InvestorProfile $investor, InvestmentOpportunity $opportunity, string $investmentType): ?Investment
    {
        return $investor->investments()
            ->where('opportunity_id', $opportunity->id)
            ->where('investment_type', $investmentType)
            ->first();
    }

    /**
     * Update existing investment by adding more shares
     * تحديث الاستثمار الموجود بإضافة المزيد من الأسهم
     * This method is called when the investor invests again in the same opportunity with the same investment_type
     * يتم استدعاء هذه الطريقة عندما يستثمر المستثمر مرة أخرى في نفس الفرصة بنفس نوع الاستثمار
     */
    protected function updateExistingInvestment(Investment $existingInvestment, int $additionalShares, InvestmentOpportunity $opportunity, bool $skipWalletPayment = false): Investment
    {
        Log::info('Updating existing investment with same investment_type', [
            'investment_id' => $existingInvestment->id,
            'investment_type' => $existingInvestment->investment_type,
            'additional_shares' => $additionalShares,
            'opportunity_id' => $opportunity->id
        ]);

        $additionalAmount = $this->calculatorService->calculateInvestmentAmount($additionalShares, $opportunity->share_price);
        $additionalPaymentRequired = $this->calculatorService->calculateTotalPaymentRequired(
            $additionalAmount,
            $additionalShares,
            $existingInvestment->investment_type,
            $opportunity
        );

        return DB::transaction(function () use ($existingInvestment, $additionalShares, $additionalAmount, $additionalPaymentRequired, $opportunity, $skipWalletPayment) {
            // Process wallet payment for additional shares
            if (!$skipWalletPayment) {
                $this->processWalletPayment($existingInvestment->investor, $additionalPaymentRequired, $opportunity);
            }

            // Update investment record
            $this->updateInvestmentRecord($existingInvestment, $additionalShares, $additionalAmount, $additionalPaymentRequired);

            // Reserve additional shares
            $opportunity->reserveShares($additionalShares);

            // Check if opportunity is fully funded
            $this->checkAndUpdateOpportunityStatus($opportunity);

            // Dispatch update event
            event(new InvestmentUpdated($existingInvestment, 'shares_added', [
                'additional_shares' => $additionalShares,
                'additional_amount' => $additionalAmount
            ]));

            return $existingInvestment;
        });
    }

    /**
     * Create new investment
     * إنشاء استثمار جديد
     * This method is called when:
     * - Investor invests for the first time in this opportunity, OR
     * - Investor invests with a different investment_type (myself vs authorize)
     * يتم استدعاء هذه الطريقة عندما:
     * - يستثمر المستثمر لأول مرة في هذه الفرصة، أو
     * - يستثمر المستثمر بنوع مختلف من الاستثمار (myself مقابل authorize)
     */
    protected function createNewInvestment(InvestorProfile $investor, InvestmentOpportunity $opportunity, int $shares, string $investmentType, bool $skipWalletPayment = false): Investment
    {
        Log::info('Creating new investment record', [
            'investor_id' => $investor->id,
            'opportunity_id' => $opportunity->id,
            'investment_type' => $investmentType,
            'shares' => $shares
        ]);

        $amount = $this->calculatorService->calculateInvestmentAmount($shares, $opportunity->share_price);
        $totalPaymentRequired = $this->calculatorService->calculateTotalPaymentRequired($amount, $shares, $investmentType, $opportunity);

        return DB::transaction(function () use ($investor, $opportunity, $shares, $amount, $totalPaymentRequired, $investmentType, $skipWalletPayment) {
            // Process wallet payment
            if (!$skipWalletPayment) {
                $this->processWalletPayment($investor, $totalPaymentRequired, $opportunity);
            }

            // Create investment record using factory pattern
            $investment = $this->createInvestmentRecord($investor, $opportunity, $shares, $amount, $totalPaymentRequired, $investmentType);

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

            // Withdraw amount from investor's wallet using WalletService
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
     * Create investment record using factory pattern
     * إنشاء سجل الاستثمار باستخدام نمط المصنع
     */
    protected function createInvestmentRecord(InvestorProfile $investor, InvestmentOpportunity $opportunity, int $shares, float $amount, float $totalPaymentRequired, string $investmentType): Investment
    {
        $investmentData = $this->calculatorService->buildInvestmentData(
            $investor,
            $opportunity,
            $shares,
            $amount,
            $totalPaymentRequired,
            $investmentType
        );

        return Investment::create($investmentData);
    }

    /**
     * Update investment record with additional shares
     * تحديث سجل الاستثمار بأسهم إضافية
     */
    protected function updateInvestmentRecord(Investment $investment, int $additionalShares, float $additionalAmount, float $additionalPaymentRequired): void
    {
        $investment->shares += $additionalShares;
        $investment->total_investment += $additionalAmount;
        $investment->total_payment_required += $additionalPaymentRequired;
        $investment->save();
    }

    /**
     * Calculate total payment required using the same logic as the Investment model
     * حساب إجمالي المبلغ المطلوب باستخدام نفس منطق نموذج الاستثمار
     */
    protected function calculateTotalPaymentRequired(float $totalInvestment, int $shares, string $investmentType, InvestmentOpportunity $opportunity): float
    {
        if ($investmentType === 'myself') {
            // For myself: total_investment + shipping fee
            $shippingFee = $shares * ($opportunity->shipping_fee_per_share ?? 0);
            return $totalInvestment + $shippingFee;
        } else {
            // For authorize: only total_investment (no shipping fee)
            return $totalInvestment;
        }
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
        if ($opportunity->share_price <= 0) {
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
     * Get investment history for an investor with caching
     * الحصول على تاريخ الاستثمارات لمستثمر مع التخزين المؤقت
     */
    public function getInvestmentHistory(InvestorProfile $investor, int $perPage = 15)
    {
        $cacheKey = "investor_history_{$investor->id}_{$perPage}";

        return Cache::remember($cacheKey, 300, function () use ($investor, $perPage) { // 5 minutes
            return $investor->investments()
                ->with(['investmentOpportunity.category', 'investmentOpportunity.ownerProfile'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        });
    }

    /**
     * Get investment statistics for an investor with caching
     * الحصول على إحصائيات الاستثمار لمستثمر مع التخزين المؤقت
     */
    public function getInvestmentStatistics(InvestorProfile $investor): array
    {
        $cacheKey = "investor_statistics_{$investor->id}";

        return Cache::remember($cacheKey, 600, function () use ($investor) { // 10 minutes
            $investments = $investor->investments();

            return [
                'total_investments' => $investments->count(),
                'total_amount_invested' => $investments->sum('total_investment'),
                'total_shares' => $investments->sum('shares'),
                'active_investments' => $investments->where('status', 'active')->count(),
                'completed_investments' => $investments->where('status', 'completed')->count(),
                'pending_investments' => $investments->where('status', 'pending')->count(),
                'cancelled_investments' => $investments->where('status', 'cancelled')->count(),
                'total_payment_required' => $investments->sum('total_payment_required'),
                'average_investment_amount' => $investments->avg('total_investment'),
                'last_investment_date' => $investments->max('created_at'),
            ];
        });
    }

    /**
     * Clear investment cache for an investor
     * مسح ذاكرة التخزين المؤقت للاستثمارات لمستثمر
     */
    public function clearInvestorCache(InvestorProfile $investor): bool
    {
        try {
            $keys = [
                "investor_history_{$investor->id}_*",
                "investor_statistics_{$investor->id}"
            ];

            $cleared = 0;
            foreach ($keys as $pattern) {
                if (Cache::forget($pattern)) {
                    $cleared++;
                }
            }

            Log::info('Investment cache cleared for investor', [
                'investor_id' => $investor->id,
                'keys_cleared' => $cleared
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to clear investment cache', [
                'investor_id' => $investor->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
