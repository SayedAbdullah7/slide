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
}
