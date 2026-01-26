<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Provider Configuration
    |--------------------------------------------------------------------------
    */
    'provider' => env('WHATSAPP_PROVIDER', 'waha'),

    /*
    |--------------------------------------------------------------------------
    | Safety Settings for Bulk Messaging
    |--------------------------------------------------------------------------
    | These settings help prevent your WhatsApp account from being banned
    | or flagged as spam when sending bulk messages.
    */
    'safety' => [
        // Enable safety features (set to false to disable all safety measures)
        'enabled' => env('WHATSAPP_SAFETY_ENABLED', true),

        // Delay between messages (milliseconds)
        // Random delay will be chosen between min and max
        'min_delay_ms' => env('WHATSAPP_MIN_DELAY', 5000),   // 5 seconds
        'max_delay_ms' => env('WHATSAPP_MAX_DELAY', 15000),  // 15 seconds

        // Batch processing settings
        // After sending batch_size messages, pause for batch_pause_seconds
        'batch_size' => env('WHATSAPP_BATCH_SIZE', 25),
        'batch_pause_seconds' => env('WHATSAPP_BATCH_PAUSE', 300), // 5 minutes

        // Daily sending limits
        // Maximum number of messages that can be sent per day
        'daily_limit' => env('WHATSAPP_DAILY_LIMIT', 500),

        // Number validation
        // Check if number exists on WhatsApp before sending
        'validate_numbers' => env('WHATSAPP_VALIDATE_NUMBERS', false),
        'cache_validation_hours' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | WAHA (WhatsApp HTTP API) Configuration
    |--------------------------------------------------------------------------
    */
    'waha' => [
        'url' => env('WAHA_URL', 'http://localhost:3000'),
        'api_key' => env('WAHA_API_KEY', ''),
        'session' => env('WAHA_SESSION', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Baileys Configuration
    |--------------------------------------------------------------------------
    */
    'baileys' => [
        'url' => env('BAILEYS_URL', 'http://localhost:3001'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonnte Configuration
    |--------------------------------------------------------------------------
    */
    'fonnte' => [
        'token' => env('FONNTE_TOKEN', ''),
    ],
];
