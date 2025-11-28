<?php

namespace App;

enum FundGoalEnum: string
{
    case GROWTH = 'growth';
    case STABILITY = 'stability';
    case INCOME = 'income';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::GROWTH->value => 'نمو',
            self::STABILITY->value => 'استقرار',
            self::INCOME->value => 'دخل',
        ];
    }

    public static function label(string $value): string
    {
        return self::labels()[$value] ?? $value;
    }
}
