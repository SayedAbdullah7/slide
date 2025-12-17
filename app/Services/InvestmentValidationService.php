<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use App\Models\InvestorProfile;
use App\Exceptions\InvestmentException;
use Exception;

class InvestmentValidationService
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Validate complete investment request
     * التحقق من طلب الاستثمار الكامل
     */
    public function validateInvestmentRequest(InvestorProfile $investor, InvestmentOpportunity $opportunity, int $shares, string $investmentType, bool $skipBalanceCheck = false): void
    {
        $this->validateInvestmentOpportunity($opportunity);
        $this->validateInvestorEligibility($investor, $opportunity, $skipBalanceCheck);
        $this->validateShares($opportunity, $shares);
        $this->validateInvestmentType($investmentType, $opportunity);
    }

    /**
     * Validate investment opportunity eligibility
     * التحقق من أهلية فرصة الاستثمار
     */
    public function validateInvestmentOpportunity(InvestmentOpportunity $opportunity): void
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
    public function validateInvestorEligibility(InvestorProfile $investor, InvestmentOpportunity $opportunity, bool $skipBalanceCheck = false): void
    {
        // Prevent investing in own opportunity
        if ($investor->user_id === optional($opportunity->ownerProfile)->user_id) {
            throw InvestmentException::ownOpportunityInvestment();
        }

        // Check if investor has sufficient wallet balance (skip for online payment)
        if (!$skipBalanceCheck) {
            try {
                $walletBalance = $this->walletService->getWalletBalance($investor);
            } catch (Exception $e) {
                throw InvestmentException::walletAccessFailed();
            }
        }
    }

    /**
     * Validate shares count
     * التحقق من عدد الأسهم
     */
    public function validateShares(InvestmentOpportunity $opportunity, int $shares, ?Investment $existingInvestment = null): void
    {
        $minShares = $this->calculateMinShares($opportunity);
        $maxShares = $this->calculateMaxShares($opportunity);

        if ($shares < $minShares) {
            throw InvestmentException::invalidShares($minShares, $maxShares);
        }

        // For existing investments, check total shares (existing + new)
        if ($existingInvestment) {
            $totalShares = $existingInvestment->shares + $shares;
            if ($totalShares > $maxShares) {
                throw new InvestmentException(
                    "الحد الأقصى للأسهم المسموح بها هو {$maxShares} سهم. لديك حالياً {$existingInvestment->shares} سهم",
                    400,
                    'EXCEEDS_MAX_SHARES'
                );
            }
        } else {
            if ($shares > $maxShares) {
                throw InvestmentException::invalidShares($minShares, $maxShares);
            }
        }
    }

    /**
     * Validate investment type
     * التحقق من نوع الاستثمار
     */
    public function validateInvestmentType(string $investmentType, InvestmentOpportunity $opportunity): void
    {
        // if (!in_array($investmentType, ['myself', 'authorize'])) {
        //     throw new InvestmentException('نوع الاستثمار غير صالح', 400, 'INVALID_INVESTMENT_TYPE');
        // }

        // // Check if the investment type is allowed for this opportunity
        // if (!$opportunity->allowsInvestmentType($investmentType) ) {
        //     $allowedTypes = $opportunity->getAllowedInvestmentTypesArray();
        //     $allowedTypesArabic = implode(' أو ', array_map(function($type) {
        //         return $type === 'myself' ? 'بيع بنفسي' : 'تفويض بالبيع';
        //     }, $allowedTypes));
        //     throw new InvestmentException(
        //         "نوع الاستثمار المحدد غير مسموح بهذه الفرصة. الأنواع المسموحة: {$allowedTypesArabic}",
        //         400,
        //         'INVESTMENT_TYPE_NOT_ALLOWED'
        //     );
        // }
    }

    /**
     * Calculate the minimum number of shares allowed
     * حساب الحد الأدنى من الأسهم المسموح بها (مع مراعاة الأسهم المتاحة)
     */
    protected function calculateMinShares(InvestmentOpportunity $opportunity): int
    {
        if ($opportunity->share_price <= 0) {
            throw new InvestmentException('سعر السهم غير صالح');
        }
        return $opportunity->effectiveMinInvestment();
    }

    /**
     * Calculate the maximum number of shares allowed
     * حساب الحد الأقصى من الأسهم المسموح بها (مع مراعاة الأسهم المتاحة)
     */
    protected function calculateMaxShares(InvestmentOpportunity $opportunity): int
    {
        return $opportunity->effectiveMaxInvestment();
    }
}
