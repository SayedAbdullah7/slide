<?php

namespace App;

enum InvestmentStatusEnum: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case COMING = 'coming';
    case OPEN = 'open';
    case COMPLETED = 'completed';
    case SUSPENDED = 'suspended';
    case EXPIRED = 'expired';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::DRAFT->value => 'مسودة',
            self::PENDING->value => 'معلق',
            self::COMING->value => 'قادم',
            self::OPEN->value => 'مفتوح',
            self::COMPLETED->value => 'مكتمل',
            self::SUSPENDED->value => 'معلق',
            self::EXPIRED->value => 'منتهي',
        ];
    }

    public static function label(string $value): string
    {
        return self::labels()[$value] ?? $value;
    }

    public static function colors(): array
    {
        return [
            self::DRAFT->value => 'gray',
            self::PENDING->value => 'yellow',
            self::COMING->value => 'blue',
            self::OPEN->value => 'green',
            self::COMPLETED->value => 'purple',
            self::SUSPENDED->value => 'red',
            self::EXPIRED->value => 'gray',
        ];
    }

    public static function color(string $value): string
    {
        return self::colors()[$value] ?? 'gray';
    }
}
