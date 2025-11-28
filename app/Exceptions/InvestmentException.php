<?php

namespace App\Exceptions;

use Exception;

class InvestmentException extends Exception
{
    protected $statusCode;
    protected $errorCode;

    public function __construct(string $message = "", int $statusCode = 400, string $errorCode = null, Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
    }

    /**
     * Get the HTTP status code
     * الحصول على رمز حالة HTTP
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the error code
     * الحصول على رمز الخطأ
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Create insufficient balance exception
     * إنشاء استثناء الرصيد غير الكافي
     */
    public static function insufficientBalance(string $currentBalance, string $requiredAmount): self
    {
        $message = "عذراً المبلغ الموجود لديك {$currentBalance} ريال وهذا المبلغ لا يكفي لإتمام العملية من فضلك أضف لمحفظتك المبلغ المتبقي وأعد المحاولة أو جرب طريقة دفع اخرى وشكراً";

        return new self($message, 402, 'INSUFFICIENT_BALANCE');
    }

    /**
     * Create investment opportunity not available exception
     * إنشاء استثناء الفرصة الاستثمارية غير متاحة
     */
    public static function opportunityNotAvailable(): self
    {
        return new self('هذه الفرصة الاستثمارية غير متاحة للتمويل حالياً', 400, 'OPPORTUNITY_NOT_AVAILABLE');
    }

    /**
     * Create invalid shares exception
     * إنشاء استثناء الأسهم غير صالحة
     */
    public static function invalidShares(int $minShares, int $maxShares = null): self
    {
        $message = "الحد الأدنى المسموح به من الأسهم: {$minShares}";
        if ($maxShares) {
            $message .= " والحد الأقصى: {$maxShares}";
        }

        return new self($message, 400, 'INVALID_SHARES');
    }

    /**
     * Create own opportunity investment exception
     * إنشاء استثناء الاستثمار في الفرصة الخاصة
     */
    public static function ownOpportunityInvestment(): self
    {
        return new self('لا يمكنك الاستثمار في فرصتك الاستثمارية الخاصة', 400, 'OWN_OPPORTUNITY_INVESTMENT');
    }

    /**
     * Create investor profile not found exception
     * إنشاء استثناء بروفايل المستثمر غير موجود
     */
    public static function investorProfileNotFound(): self
    {
        return new self('لم يتم العثور على بروفايل المستثمر', 404, 'INVESTOR_PROFILE_NOT_FOUND');
    }

    /**
     * Create wallet access failed exception
     * إنشاء استثناء فشل الوصول للمحفظة
     */
    public static function walletAccessFailed(): self
    {
        return new self('فشل في الوصول إلى محفظتك المالية', 500, 'WALLET_ACCESS_FAILED');
    }

    /**
     * Create payment processing failed exception
     * إنشاء استثناء فشل معالجة الدفع
     */
    public static function paymentProcessingFailed(string $reason = ''): self
    {
        $message = 'فشل في معالجة الدفع';
        if ($reason) {
            $message .= ': ' . $reason;
        }

        return new self($message, 500, 'PAYMENT_PROCESSING_FAILED');
    }

    /**
     * Create processing failed exception
     * إنشاء استثناء فشل المعالجة
     */
    public static function processingFailed(string $reason = ''): self
    {
        $message = 'حدث خطأ أثناء معالجة الاستثمار';
        if ($reason) {
            $message .= ': ' . $reason;
        }

        return new self($message, 500, 'PROCESSING_FAILED');
    }

    /**
     * Create insufficient shares exception
     * إنشاء استثناء الأسهم غير كافية
     */
    public static function insufficientShares(int $availableShares): self
    {
        return new self("الأسهم المتاحة غير كافية. المتاح: {$availableShares} سهم", 400, 'INSUFFICIENT_SHARES_AVAILABLE');
    }

    /**
     * Get the response array for API
     * الحصول على مصفوفة الاستجابة للـ API
     */
    public function toArray(): array
    {
        return [
            'error' => true,
            'message' => $this->getMessage(),
            'error_code' => $this->getErrorCode(),
            'status_code' => $this->getStatusCode(),
        ];
    }
}
