<?php

namespace App;

enum InvestmentOpportunityRequestStatusEnum: string
{
    case PENDING = 'pending'; // في انتظار المراجعة
    case APPROVED = 'approved'; // تم قبول الطلب
    case REJECTED = 'rejected'; // تم رفض الطلب
    case UNDER_REVIEW = 'under_review'; // قيد المراجعة
    case CANCELLED = 'cancelled'; // تم إلغاء الطلب

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::PENDING->value => 'في انتظار المراجعة',
            self::APPROVED->value => 'تم قبول الطلب',
            self::REJECTED->value => 'تم رفض الطلب',
            self::UNDER_REVIEW->value => 'قيد المراجعة',
            self::CANCELLED->value => 'تم إلغاء الطلب',
        ];
    }

    public static function label(string $value): string
    {
        return self::labels()[$value] ?? $value;
    }

    public static function colors(): array
    {
        return [
            self::PENDING->value => 'yellow',
            self::APPROVED->value => 'green',
            self::REJECTED->value => 'red',
            self::UNDER_REVIEW->value => 'blue',
            self::CANCELLED->value => 'gray',
        ];
    }

    public static function color(string $value): string
    {
        return self::colors()[$value] ?? 'gray';
    }

    /**
     * Get statuses that allow editing
     */
    public static function editableStatuses(): array
    {
        return [
            self::PENDING->value,
            self::UNDER_REVIEW->value,
        ];
    }

    /**
     * Get statuses that allow deletion
     */
    public static function deletableStatuses(): array
    {
        return [
            self::PENDING->value,
        ];
    }

    /**
     * Get final statuses (cannot be changed)
     */
    public static function finalStatuses(): array
    {
        return [
            self::APPROVED->value,
            self::REJECTED->value,
            self::CANCELLED->value,
        ];
    }

    /**
     * Check if status is editable
     */
    public static function isEditable(string $status): bool
    {
        return in_array($status, self::editableStatuses());
    }

    /**
     * Check if status is deletable
     */
    public static function isDeletable(string $status): bool
    {
        return in_array($status, self::deletableStatuses());
    }

    /**
     * Check if status is final
     */
    public static function isFinal(string $status): bool
    {
        return in_array($status, self::finalStatuses());
    }
}

