<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Company Bank Account Details
    |--------------------------------------------------------------------------
    |
    | Bank account details for the company that investors use to transfer
    | money to add funds to their wallet.
    |
    */

    'company_bank_account' => [
        'bank_name' => env('COMPANY_BANK_NAME', 'البنك الاهلي السعودي'),
        'bank_name_en' => env('COMPANY_BANK_NAME_EN', 'Saudi National Bank'),
        'bank_code' => env('COMPANY_BANK_CODE', 'SNB'),
        'account_number' => env('COMPANY_BANK_ACCOUNT_NUMBER', '73000000395008'),
        'iban' => env('COMPANY_BANK_IBAN', 'SA6910000073000000395008'),
        'company_name' => env('COMPANY_NAME', 'شركة سلايد تك القابضة'),
        'company_name_en' => env('COMPANY_NAME_EN', 'SLID TECH COMPANY HOLDING'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Receipt Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for uploading bank transfer receipts
    |
    */

    'receipt' => [
        'max_size' => env('BANK_TRANSFER_RECEIPT_MAX_SIZE', 5120), // KB (5 MB default)
        'allowed_mimes' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
        'storage_path' => 'bank_transfer_receipts',
    ],
];











