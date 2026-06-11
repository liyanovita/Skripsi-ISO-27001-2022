<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification System
    |--------------------------------------------------------------------------
    |
    | Master switch for the entire notification system. When disabled, no
    | notifications will be sent through any channel.
    |
    */
    'enabled' => env('NOTIFICATIONS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    |
    | Configure available notification channels. Each channel can be
    | enabled/disabled independently via environment variables.
    |
    */
    'channels' => [
        'telegram' => [
            'enabled' => env('TELEGRAM_ENABLED', true),
            'bot_token' => env('TELEGRAM_BOT_TOKEN'),
            'chat_id' => env('TELEGRAM_CHAT_ID'),
            'parse_mode' => 'Markdown',
            'retry_attempts' => 3,
            'retry_delay' => 5, // seconds
        ],

        // Future channels: Email, SMS, Slack, WhatsApp, etc.
    ],

    /*
    |--------------------------------------------------------------------------
    | CAPA Reminder Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for Corrective Action Plan (CAPA) reminder notifications.
    | These reminders alert PICs about upcoming or overdue tasks.
    |
    */
    'capa_reminders' => [
        'enabled' => env('CAPA_REMINDERS_ENABLED', true),
        'channels' => ['telegram'], // Currently only Telegram
        'days_ahead' => env('CAPA_DAYS_AHEAD', 3),
        'schedule' => env('CAPA_REMINDER_SCHEDULE', '0 8 * * 1-5'), // Weekdays 8AM
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for notification templates. Templates are stored as PHP
    | files and can be cached for better performance.
    |
    */
    'templates' => [
        'path' => app_path('Services/Notification/Templates'),
        'cache' => true,
        'cache_ttl' => 3600, // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Log all notification attempts for debugging and monitoring purposes.
    | Logs include success/failure status and error messages.
    |
    */
    'logging' => [
        'enabled' => true,
        'channel' => env('NOTIFICATION_LOG_CHANNEL', 'stack'),
        'level' => env('NOTIFICATION_LOG_LEVEL', 'info'),
    ],
];
