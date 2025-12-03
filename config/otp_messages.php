<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OTP Message Templates
    |--------------------------------------------------------------------------
    |
    | These templates follow CITC (Communications, Space and Technology Commission)
    | regulations for OTP messages. Each template includes the entity name
    | and reason for sending the verification code.
    |
    */

    'app_name' => env('APP_NAME', 'Slide Tech'),

    'templates' => [
        'login' => [
            'message' => "رمز التحقق الخاص بك هو: {code}\nلاستخدامه في تسجيل الدخول إلى حسابك في {app_name}\nصالح لمدة 5 دقائق.",
            'description' => 'نموذج تسجيل الدخول'
        ],

        'confirm' => [
            'message' => "رمز التحقق الخاص بك هو: {code}\nلتأكيد {operation_name} في {app_name}\nيرجى عدم مشاركة الرمز مع أي شخص.\nصالح لمدة 5 دقائق.",
            'description' => 'نموذج تأكيد العملية'
        ],

        'register' => [
            'message' => "رمز التحقق: {code}\nلاستخدامه في إنشاء حسابك على {app_name}\nتنبيه: لا تشارك هذا الرمز مع أي جهة.",
            'description' => 'نموذج التسجيل'
        ],

        // 'loginAndRegister' => [
        //     'message' => "رمز التحقق الخاص بك للتسجيل في {app_name} هو: {code}\nيرجي عدم مشاركة هذا الرقم مع أي شخص.",
        //     'description' => 'نموذج التسجيل'
        // ],
        'loginAndRegister' => [
            'message' => "رمز التحقق للتسجيل في {app_name}:     {code}\nيرجى عدم مشاركته مع أي شخص.",
            'description' => 'نموذج التسجيل'
        ],

                // 'loginAndRegister' => [
        //     'message' => "عميل سلايد رمز التحقق للتسجيل الخاص بك: {code}\nلأمان حسابك يرجى منك عدم مشاركة رمز التحقق مع اي شخص.",
        //     'description' => 'نموذج التسجيل'
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */

    'default_type' => 'login',
    'expiry_minutes' => 5,
    'max_attempts' => 3,
];
