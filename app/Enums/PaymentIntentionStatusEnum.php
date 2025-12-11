<?php

namespace App\Enums;

enum PaymentIntentionStatusEnum: string
{
    case CREATED = 'created';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case EXPIRED = 'expired';

    /**
     * Get all status values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if status is pending
     */
    public function isPending(): bool
    {
        return in_array($this, [self::CREATED, self::ACTIVE]);
    }

    /**
     * Check if status is successful
     */
    public function isSuccessful(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if status is failed
     */
    public function isFailed(): bool
    {
        return $this === self::FAILED;
    }
}

