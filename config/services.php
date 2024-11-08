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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'ton' => [
        'is_main' => env('IS_MAIN'),
        'api_key_test' => env('TON_API_KEY_TEST'),
        'api_key_main' => env('TON_API_KEY_MAIN'),
        'root_wallet' => env('TON_ROOT_WALLET'),
        'master_jetton_usdt' => env('IS_MAIN') ? env('TON_MASTER_USDT_MAIN') : env('TON_MASTER_USDT_TEST'),
        'master_jetton_not' => env('IS_MAIN') ? env('TON_MASTER_NOT_MAIN') : env('TON_MASTER_NOT_TEST'),
        'mnemonic' => env('TON_MNEMONIC'),
        'fixed_fee' => env('FIXED_FEE')
    ]
];
