<?php

namespace App;

enum RiskLevelEnum: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function text($value): string
    {
        return match ($value) {
            self::LOW->value => 'منخفض 10%',
            self::MEDIUM->value => 'متوسط 20%',
            self::HIGH->value => 'عالي 30%',
        };
    }

    public static function color($value): string
    {
        return match ($value) {
            self::LOW->value => 'success',
            self::MEDIUM->value => 'warning',
            self::HIGH->value => 'danger',
        };
    }
}
