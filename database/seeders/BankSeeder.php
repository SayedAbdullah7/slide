<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            [
                'code' => 'RIBL',
                'name_ar' => 'بنك الرياض',
                'name_en' => 'Riyad Bank',
                'icon' => null,
                'is_active' => true,
            ],
            [
                'code' => 'NCBK',
                'name_ar' => 'البنك الأهلي السعودي',
                'name_en' => 'Saudi National Bank',
                'icon' => null,
                'is_active' => true,
            ],
            [
                'code' => 'RJHI',
                'name_ar' => 'مصرف الراجحي',
                'name_en' => 'Al Rajhi Bank',
                'icon' => null,
                'is_active' => true,
            ],
            [
                'code' => 'ALIN',
                'name_ar' => 'مصرف الإنماء',
                'name_en' => 'Alinma Bank',
                'icon' => null,
                'is_active' => true,
            ],
            [
                'code' => 'BIAD',
                'name_ar' => 'بنك البلاد',
                'name_en' => 'Bank Albilad',
                'icon' => null,
                'is_active' => true,
            ],
            [
                'code' => 'BSFR',
                'name_ar' => 'بنك السعودية الفرنسي',
                'name_en' => 'Banque Saudi Fransi',
                'icon' => null,
                'is_active' => true,
            ],
            [
                'code' => 'SABB',
                'name_ar' => 'البنك السعودي البريطاني',
                'name_en' => 'SABB',
                'icon' => null,
                'is_active' => true,
            ],
            [
                'code' => 'BSSC',
                'name_ar' => 'بنك السعودية للاستثمار',
                'name_en' => 'Saudi Investment Bank',
                'icon' => null,
                'is_active' => true,
            ],
            [
                'code' => 'AJRB',
                'name_ar' => 'بنك الجزيرة',
                'name_en' => 'Bank AlJazira',
                'icon' => null,
                'is_active' => true,
            ],
            [
                'code' => 'IBBL',
                'name_ar' => 'بنك إنجاز',
                'name_en' => 'Injaz Bank',
                'icon' => null,
                'is_active' => true,
            ],
        ];

        foreach ($banks as $bank) {
            Bank::firstOrCreate(
                ['code' => $bank['code']],
                $bank
            );
        }
    }
}
