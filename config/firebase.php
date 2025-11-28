<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Project Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Firebase project settings. You will need
    | to download the service account JSON file from Firebase Console.
    |
    */

    'project_id' => 'osta-a2c09',

    // 'credentials_path' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase-credentials.json')),
    'credentials_path' => storage_path('app/firebase-credentials.json'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Firebase Cloud Messaging (FCM) notifications.
    |
    */

    'messaging' => [
        'default_sound' => 'default',
        'default_priority' => 'high',
        'default_ttl' => 3600, // 1 hour in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for push notifications.
    |
    */

    'notifications' => [
        'android' => [
            'priority' => 'high',
            'sound' => 'default',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ],
        'ios' => [
            'sound' => 'default',
            'badge' => 1,
        ],
    ],
];
