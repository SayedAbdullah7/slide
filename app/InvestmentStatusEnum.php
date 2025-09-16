<?php

namespace App;

enum InvestmentStatusEnum: string
{
    case OPEN = 'open';
    case COMPLETED = 'completed';
    case SUSPENDED = 'suspended';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
