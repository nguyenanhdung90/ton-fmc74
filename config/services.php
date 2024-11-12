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
        'is_main' => env('TON_IS_MAIN'),
        'api_key' => env('TON_IS_MAIN') ? env('TON_API_KEY_MAIN') : env('TON_API_KEY_TEST'),
        'root_wallet' => env('TON_ROOT_WALLET'),
        'master_jetton_usdt' => env('TON_IS_MAIN') ? env('TON_MASTER_USDT_MAIN') : env('TON_MASTER_USDT_TEST'),
        'master_jetton_not' => env('TON_IS_MAIN') ? env('TON_MASTER_NOT_MAIN') : env('TON_MASTER_NOT_TEST'),
        'mnemonic' => env('TON_MNEMONIC'),
        'fixed_fee' => env('FIXED_FEE')
    ],
    'coin_infos' => [
        [
            "currency" => "TON",
            "decimals" => 9,
        ],
        [
            "name" => "Tether USD",
            "description" => "Tether Token for Tether USD",
            "image" => "https://tether.to/images/logoCircle.png",
            "currency" => "USDT",
            "decimals" => 6,
            "hex_master_address" => env('TON_IS_MAIN') ? env('TON_MASTER_USDT_MAIN') : env('TON_MASTER_USDT_TEST'),
        ],
        [
            "name" => "Notcoin",
            "image" => "https://cdn.joincommunity.xyz/clicker/not_logo.png",
            "currency" => "NOT",
            "decimals" => 9,
            "hex_master_address" => env('TON_IS_MAIN') ? env('TON_MASTER_NOT_MAIN') : env('TON_MASTER_NOT_TEST'),
        ]
    ]
];
