<?php

namespace App;

enum WalletDepositSourceEnum: string
{
    case PAYMENT_GATEWAY = 'payment_gateway';
    case DASHBOARD = 'dashboard';
    case BANK_TRANSFER = 'bank_transfer';
    case WITHDRAWAL_REFUND = 'withdrawal_refund';
    case PROFIT_DISTRIBUTION = 'profit_distribution';
    case API = 'api';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::PAYMENT_GATEWAY->value => 'بوابة الدفع',
            self::DASHBOARD->value => 'لوحة التحكم / إداري',
            self::BANK_TRANSFER->value => 'تحويل بنكي',
            self::WITHDRAWAL_REFUND->value => 'استرجاع سحب',
            self::PROFIT_DISTRIBUTION->value => 'توزيع أرباح',
            self::API->value => 'واجهة برمجية',
        ];
    }

    public static function label(string $value): string
    {
        return self::labels()[$value] ?? $value;
    }

    /**
     * Check if a value is valid
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }
}

