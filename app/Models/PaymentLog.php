<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class PaymentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_intention_id',
        'type',
        'action',
        'message',
        'context',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    /**
     * Get the user that owns the log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment that owns the log.
     */
    public function paymentIntention(): BelongsTo
    {
        return $this->belongsTo(PaymentIntention::class);
    }

    /**
     * Create an info log entry.
     */
    public static function info(string $message, array $context = [], ?int $userId = null, ?int $paymentId = null, ?int $deprecated = null, ?string $action = null): self
    {
        $logData = [
            'user_id' => $userId,
            'payment_intention_id' => $paymentId,
            'type' => 'info',
            'action' => $action,
            'message' => $message,
            'context' => $context,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        // Store in database
        $log = self::create($logData);

        // Also log to Laravel's log system for mobile/console access
        Log::info('PaymentLog: ' . $message, array_merge($context, [
            'log_id' => $log->id,
            'user_id' => $userId,
            'action' => $action,
            'type' => 'info'
        ]));

        return $log;
    }

    /**
     * Create an error log entry.
     */
    public static function error(string $message, array $context = [], ?int $userId = null, ?int $paymentId = null, ?int $deprecated = null, ?string $action = null): self
    {
        $logData = [
            'user_id' => $userId,
            'payment_intention_id' => $paymentId,
            'type' => 'error',
            'action' => $action,
            'message' => $message,
            'context' => $context,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        // Store in database
        $log = self::create($logData);

        // Also log to Laravel's log system for mobile/console access
        Log::error('PaymentLog: ' . $message, array_merge($context, [
            'log_id' => $log->id,
            'user_id' => $userId,
            'action' => $action,
            'type' => 'error'
        ]));

        return $log;
    }

    /**
     * Create a warning log entry.
     */
    public static function warning(string $message, array $context = [], ?int $userId = null, ?int $paymentId = null, ?int $deprecated = null, ?string $action = null): self
    {
        $logData = [
            'user_id' => $userId,
            'payment_intention_id' => $paymentId,
            'type' => 'warning',
            'action' => $action,
            'message' => $message,
            'context' => $context,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        // Store in database
        $log = self::create($logData);

        // Also log to Laravel's log system for mobile/console access
        Log::warning('PaymentLog: ' . $message, array_merge($context, [
            'log_id' => $log->id,
            'user_id' => $userId,
            'action' => $action,
            'type' => 'warning'
        ]));

        return $log;
    }

    /**
     * Create a debug log entry.
     */
    public static function debug(string $message, array $context = [], ?int $userId = null, ?int $paymentId = null, ?int $deprecated = null, ?string $action = null): self
    {
        $logData = [
            'user_id' => $userId,
            'payment_intention_id' => $paymentId,
            'type' => 'debug',
            'action' => $action,
            'message' => $message,
            'context' => $context,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        // Store in database
        $log = self::create($logData);

        // Also log to Laravel's log system for mobile/console access
        Log::debug('PaymentLog: ' . $message, array_merge($context, [
            'log_id' => $log->id,
            'user_id' => $userId,
            'action' => $action,
            'type' => 'debug'
        ]));

        return $log;
    }

    /**
     * Log incoming webhook data
     *
     * @param string $webhookType Type of webhook (TRANSACTION, TOKEN, etc.)
     * @param array $webhookData Raw webhook data
     * @param int|null $userId User ID if available
     * @param int|null $paymentId Payment intention ID if available
     * @return self
     */
    public static function webhook(string $webhookType, array $webhookData, ?int $userId = null, ?int $paymentId = null): self
    {
        $logData = [
            'user_id' => $userId,
            'payment_intention_id' => $paymentId,
            'type' => 'webhook',
            'action' => 'webhook_received',
            'message' => "Webhook received: {$webhookType}",
            'context' => [
                'webhook_type' => $webhookType,
                'webhook_data' => $webhookData,
                'transaction_id' => $webhookData['obj']['id'] ?? null,
                'order_id' => $webhookData['obj']['order']['id'] ?? null,
                'merchant_order_id' => $webhookData['obj']['order']['merchant_order_id'] ?? null,
                'status' => $webhookData['obj']['success'] ?? null,
                'amount_cents' => $webhookData['obj']['amount_cents'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        // Store in database
        $log = self::create($logData);

        // Also log to Laravel's log system
        Log::info('PaymentLog: Webhook received', [
            'log_id' => $log->id,
            'webhook_type' => $webhookType,
            'transaction_id' => $webhookData['obj']['id'] ?? null,
            'order_id' => $webhookData['obj']['order']['id'] ?? null,
            'type' => 'webhook'
        ]);

        return $log;
    }

    /**
     * Format exception for logging with character limit
     *
     * Converts exception to string and truncates if exceeds limit
     *
     * @param \Exception|\Throwable $exception
     * @param int $maxLength Maximum length (default 5000 chars)
     * @return string
     */
    public static function formatException($exception, int $maxLength = 5000): string
    {
        // Convert exception to string (includes message, file, line, and full trace)
        $content = (string) $exception;

        // Truncate if exceeds limit
        if (strlen($content) > $maxLength) {
            return substr($content, 0, $maxLength) . "\n... [truncated]";
        }

        return $content;
    }
}
