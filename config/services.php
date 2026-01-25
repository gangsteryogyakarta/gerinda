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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Geocoding Service
    |--------------------------------------------------------------------------
    */
    'geocoding' => [
        'provider' => env('GEOCODING_PROVIDER', 'nominatim'), // nominatim or google
    ],

    'google' => [
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Gateway Service
    |--------------------------------------------------------------------------
    */
    'wa_gateway' => [
        'url' => env('WA_GATEWAY_URL'),
        'token' => env('WA_GATEWAY_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WAHA (WhatsApp HTTP API) - Self-hosted
    |--------------------------------------------------------------------------
    */
    'waha' => [
        'url' => env('WAHA_URL', 'http://localhost:3000'),
        'api_key' => env('WAHA_API_KEY', 'gerindra-secret-key-2026'),
        'session' => env('WAHA_SESSION', 'gerindra'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Unified Configuration
    |--------------------------------------------------------------------------
    | Providers: baileys, fonnte, waha, generic
    */
    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER', 'baileys'),
        'baileys_url' => env('BAILEYS_URL', 'http://localhost:3001'),
        'fonnte_token' => env('FONNTE_TOKEN', ''),
    ],

];
