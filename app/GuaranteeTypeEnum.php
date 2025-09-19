<?php

namespace App;

enum GuaranteeTypeEnum: string
{
    case REAL_ESTATE_MORTGAGE = 'real_estate_mortgage'; // رهن عقاري
    case BANK_GUARANTEE = 'bank_guarantee'; // كفالة بنكية
    case PERSONAL_GUARANTEE = 'personal_guarantee'; // كفالة شخصية
    case ASSET_PLEDGE = 'asset_pledge'; // رهن أصول
    case INSURANCE_POLICY = 'insurance_policy'; // بوليصة تأمين
    case GOVERNMENT_GUARANTEE = 'government_guarantee'; // ضمان حكومي
    case COLLATERAL = 'collateral'; // ضمانات أخرى

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::REAL_ESTATE_MORTGAGE->value => 'رهن عقاري',
            self::BANK_GUARANTEE->value => 'كفالة بنكية',
            self::PERSONAL_GUARANTEE->value => 'كفالة شخصية',
            self::ASSET_PLEDGE->value => 'رهن أصول',
            self::INSURANCE_POLICY->value => 'بوليصة تأمين',
            self::GOVERNMENT_GUARANTEE->value => 'ضمان حكومي',
            self::COLLATERAL->value => 'ضمانات أخرى',
        ];
    }

    public static function label(string $value): string
    {
        return self::labels()[$value] ?? $value;
    }

    public static function colors(): array
    {
        return [
            self::REAL_ESTATE_MORTGAGE->value => 'green',
            self::BANK_GUARANTEE->value => 'blue',
            self::PERSONAL_GUARANTEE->value => 'yellow',
            self::ASSET_PLEDGE->value => 'purple',
            self::INSURANCE_POLICY->value => 'indigo',
            self::GOVERNMENT_GUARANTEE->value => 'red',
            self::COLLATERAL->value => 'gray',
        ];
    }

    public static function color(string $value): string
    {
        return self::colors()[$value] ?? 'gray';
    }
}
