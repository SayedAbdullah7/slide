<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Taqnyat API Authentication
    |--------------------------------------------------------------------------
    |
    | Your Taqnyat API authentication token. You can get this from your
    | Taqnyat account dashboard.
    |
    */
    'auth_token' => env('TAQNYAT_AUTH_TOKEN', 'd1afc623f4ae6ed1f12bba1d1b94e549'),

    /*
    |--------------------------------------------------------------------------
    | Default Sender Name
    |--------------------------------------------------------------------------
    |
    | The default sender name that will appear in SMS messages.
    | You can override this when sending individual messages.
    |
    */
    'default_sender' => env('TAQNYAT_DEFAULT_SENDER', 'slide tech'),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for Taqnyat API. Usually you don't need to change this.
    |
    */
    'base_url' => env('TAQNYAT_BASE_URL', 'https://api.taqnyat.sa'),
];

