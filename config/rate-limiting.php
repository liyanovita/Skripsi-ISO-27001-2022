<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for different endpoints and actions
    |
    */

    'enabled' => env('RATE_LIMITING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | API Rate Limits
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for API endpoints
    |
    */

    'api' => [
        'limit' => env('API_RATE_LIMIT', 60),
        'decay' => env('API_RATE_DECAY', 1), // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Rate Limits
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for authentication endpoints
    |
    */

    'auth' => [
        'login' => [
            'limit' => env('LOGIN_RATE_LIMIT', 5),
            'decay' => env('LOGIN_RATE_DECAY', 15), // minutes
        ],
        'register' => [
            'limit' => env('REGISTER_RATE_LIMIT', 3),
            'decay' => env('REGISTER_RATE_DECAY', 60), // minutes
        ],
        'password_reset' => [
            'limit' => env('PASSWORD_RESET_RATE_LIMIT', 3),
            'decay' => env('PASSWORD_RESET_RATE_DECAY', 60), // minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Rate Limits
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for file uploads
    |
    */

    'uploads' => [
        'max_file_size_mb' => env('MAX_FILE_SIZE_MB', 50),
        'max_uploads_per_hour' => env('MAX_UPLOADS_PER_HOUR', 20),
        'max_daily_upload_mb' => env('MAX_DAILY_UPLOAD_MB', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Rate Limits
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for webhook endpoints
    |
    */

    'webhooks' => [
        'limit' => env('WEBHOOK_RATE_LIMIT', 100),
        'decay' => env('WEBHOOK_RATE_DECAY', 1), // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Rate Limits
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for export endpoints
    |
    */

    'exports' => [
        'limit' => env('EXPORT_RATE_LIMIT', 5),
        'decay' => env('EXPORT_RATE_DECAY', 5), // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Brute Force Protection
    |--------------------------------------------------------------------------
    |
    | Configure brute force protection settings
    |
    */

    'brute_force' => [
        'enabled' => env('BRUTE_FORCE_PROTECTION_ENABLED', true),
        'max_attempts' => env('BRUTE_FORCE_MAX_ATTEMPTS', 5),
        'decay' => env('BRUTE_FORCE_DECAY', 15), // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Whitelist
    |--------------------------------------------------------------------------
    |
    | IP addresses or user IDs to exclude from rate limiting
    |
    */

    'whitelist' => [
        'ips' => explode(',', env('RATE_LIMIT_WHITELIST_IPS', '')),
        'users' => explode(',', env('RATE_LIMIT_WHITELIST_USERS', '')),
    ],

];
