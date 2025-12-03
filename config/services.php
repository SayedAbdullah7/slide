<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'paymob' => [
        // test
        // 'api_key' => env('PAYMOB_API_KEY','ZXlKaGJHY2lPaUpJVXpVeE1pSXNJblI1Y0NJNklrcFhWQ0o5LmV5SmpiR0Z6Y3lJNklrMWxjbU5vWVc1MElpd2ljSEp2Wm1sc1pWOXdheUk2TVRFNE9ETXNJbTVoYldVaU9pSnBibWwwYVdGc0luMC4zR1NPNFdJYkczN0J4MTc4OG5lc09pMTdWenpZZ2tCQ240bm1Sb2hUel9ZbnFJaGMwZ05YVXh4NjFpWnhXRUJuNGFLOWZpUk9uaDg3VEtjbVAtNDZNZw=='),
        // 'secret_key' => env('PAYMOB_SECRET_KEY','sau_sk_test_404992e061ce6c7de437b816bb36fecf9862ba634088f9c3cd8c4af24feaeefd'),
        // 'public_key' => env('PAYMOB_PUBLIC_KEY', 'sau_pk_test_U9jgmtYr1CAdhAaok0kWT3cDM1JRPeE9'),
        // // 'integration_id' => env('PAYMOB_INTEGRATION_ID','16105'), // test online
        // 'integration_id' => [    
        //     'apple_pay' => env('PAYMOB_INTEGRATION_ID_APPLE_PAY', '16105'),
        //     'card' => env('PAYMOB_INTEGRATION_ID_CARD', '16105'),
        // ],
        // 'hmac_secret' => env('PAYMOB_HMAC_SECRET', 'E8862BCABDEFFEABC7C2C23A62ACEFAD'), // HMAC secret for webhook signature validation
        // 'base_url' => env('PAYMOB_BASE_URL', 'https://ksa.paymob.com'),
        // 'webhook_url' => env('PAYMOB_WEBHOOK_URL', config('app.url') . '/api/paymob/webhook'),
        // 'redirect_url' => env('PAYMOB_REDIRECT_URL', config('app.url') . '/api/paymob/redirection'),

        // live
        'api_key' => env('PAYMOB_API_KEY','ZXlKaGJHY2lPaUpJVXpVeE1pSXNJblI1Y0NJNklrcFhWQ0o5LmV5SmpiR0Z6Y3lJNklrMWxjbU5vWVc1MElpd2ljSEp2Wm1sc1pWOXdheUk2TVRFNE9ETXNJbTVoYldVaU9pSnBibWwwYVdGc0luMC4zR1NPNFdJYkczN0J4MTc4OG5lc09pMTdWenpZZ2tCQ240bm1Sb2hUel9ZbnFJaGMwZ05YVXh4NjFpWnhXRUJuNGFLOWZpUk9uaDg3VEtjbVAtNDZNZw=='),
        'secret_key' => env('PAYMOB_SECRET_KEY','sau_sk_live_d847d0088ff78474207f9bea75744a416092e6599b8a7b10130291bbb2249937'),
        'public_key' => env('PAYMOB_PUBLIC_KEY', 'sau_pk_live_YuDXS4ROGLj1z61OporRaW70r0Q1hBQ0'),
        // 'integration_id' => env('PAYMOB_INTEGRATION_ID','17269'),
        // 'integration_id_for_apple_pay' => env('17269'),
        // 'integration_id_for_card' => env('17269'),
        'integration_id' => [
            'apple_pay' => env('PAYMOB_INTEGRATION_ID_APPLE_PAY', '17269'),
            'card' => env('PAYMOB_INTEGRATION_ID_CARD', '17268'),
        ],
        'hmac_secret' => env('PAYMOB_HMAC_SECRET', 'E8862BCABDEFFEABC7C2C23A62ACEFAD'), // HMAC secret for webhook signature validation
        'base_url' => env('PAYMOB_BASE_URL', 'https://ksa.paymob.com'),
        'webhook_url' => env('PAYMOB_WEBHOOK_URL', config('app.url') . '/api/paymob/webhook'),
        'redirect_url' => env('PAYMOB_REDIRECT_URL', config('app.url') . '/api/paymob/redirection'),
    ],

];
