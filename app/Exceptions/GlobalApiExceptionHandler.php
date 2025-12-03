<?php

namespace App\Exceptions;

use App\Http\Traits\Helpers\ApiResponseTrait;
use Error;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GlobalApiExceptionHandler
{
    use ApiResponseTrait;
    /**
     * Handle API exceptions using ApiResponseTrait structure
     * معالجة استثناءات API باستخدام هيكل ApiResponseTrait
     */
    public static function handleApiException(Exception $exception, Request $request): JsonResponse|string|null
    {
        // Only handle API requests
        if (!$request->expectsJson() && !$request->is('api/*')) {
            return null;
        }

        // Create instance to use trait methods
        $handler = new self();


        // Handle HTTP exceptions
        if ($exception instanceof HttpException) {
            return $handler->handleHttpException($exception, $request);
        }
        // if code is 401
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return $handler->respondUnAuthorized($exception->getMessage());
        }

        return null;

        // // Handle validation exceptions
        // if ($exception instanceof ValidationException) {
        //     return $handler->handleValidationException($exception);
        // }

        // // Handle other exceptions
        // return $handler->handleGenericException($exception);
    }

    /**
     * Handle HTTP exceptions (403, 404, 401, etc.)
     * معالجة استثناءات HTTP
     */
    protected function handleHttpException(HttpException $exception, Request $request): ?JsonResponse
    {
        $statusCode = $exception->getStatusCode();
        $message = $exception->getMessage() ?: $this->getDefaultMessage($statusCode);

        // Use ApiResponseTrait methods based on status code
        return match ($statusCode) {
            401 => $this->respondUnAuthorized($message),
            403 => $this->respondForbidden($message),
            // 404 => $this->respondNotFound($message),
            // 405 => $this->respondError($message, 405),
            // 408 => $this->respondError($message, 408),
            // 409 => $this->respondConflict($message),
            // 422 => $this->respondError($message, 422),
            // 429 => $this->respondTooManyRequests($message),
            // 500 => $this->respondInternalError($message),
            // 502 => $this->respondError($message, 502),
            // 503 => $this->respondServiceUnavailable($message),
            // 504 => $this->respondError($message, 504),
            // default => $this->respondError($message, $statusCode),
            default => null,
        };
    }

    /**
     * Handle validation exceptions
     * معالجة استثناءات التحقق
     */
    protected function handleValidationException(ValidationException $exception): JsonResponse
    {
        // Use ApiResponseTrait validation error method
        return $this->respondValidationErrors($exception);
    }

    /**
     * Handle generic exceptions
     * معالجة الاستثناءات العامة
     */
    protected function handleGenericException(Exception $exception): JsonResponse
    {
        $message = 'حدث خطأ داخلي، يرجى المحاولة مرة أخرى لاحقاً';

        // Use ApiResponseTrait internal error method
        return $this->respondInternalError($message, $exception);
    }

    /**
     * Get default Arabic messages for HTTP status codes
     * الحصول على رسائل عربية افتراضية لرموز حالة HTTP
     */
    protected function getDefaultMessage(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'طلب غير صحيح',
            401 => 'غير مصرح لك بالوصول',
            403 => 'غير مسموح لك بالوصول إلى هذا المورد',
            404 => 'المورد المطلوب غير موجود',
            405 => 'الطريقة غير مسموحة',
            408 => 'انتهت مهلة الطلب',
            409 => 'تضارب في البيانات',
            422 => 'يرجى تصحيح الأخطاء التالية',
            429 => 'تم تجاوز الحد المسموح من الطلبات',
            500 => 'خطأ داخلي في الخادم',
            502 => 'خطأ في بوابة الخادم',
            503 => 'الخدمة غير متاحة حالياً',
            504 => 'انتهت مهلة بوابة الخادم',
            default => 'حدث خطأ غير متوقع',
        };
    }
}
