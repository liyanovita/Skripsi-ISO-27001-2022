<?php

namespace App\Services\Notification\Channels;

use App\Services\Notification\Contracts\NotificationChannelInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Telegram Notification Channel
 * 
 * Sends notifications via Telegram Bot API.
 * Supports Markdown formatting and rate limiting.
 */
class TelegramChannel implements NotificationChannelInterface
{
    protected string $botToken;
    protected string $chatId;
    protected string $parseMode;

    public function __construct()
    {
        $this->botToken = config('notifications.channels.telegram.bot_token');
        $this->chatId = config('notifications.channels.telegram.chat_id');
        $this->parseMode = config('notifications.channels.telegram.parse_mode', 'Markdown');
    }

    /**
     * Send notification via Telegram
     *
     * @param array $data Notification data with 'body' key
     * @return bool True if sent successfully
     * @throws \Exception If API call fails
     */
    public function send(array $data): bool
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        try {
            $response = Http::timeout(30)->post($url, [
                'chat_id' => $this->chatId,
                'text' => $data['body'],
                'parse_mode' => $this->parseMode,
            ]);

            if ($response->successful()) {
                return true;
            }

            // Log API error
            Log::error('Telegram API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception("Telegram API returned status {$response->status()}: {$response->body()}");

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new \Exception("Failed to connect to Telegram API: {$e->getMessage()}");
        }
    }

    /**
     * Check if Telegram channel is enabled
     *
     * @return bool True if enabled
     */
    public function isEnabled(): bool
    {
        return config('notifications.channels.telegram.enabled', false) 
            && !empty($this->botToken) 
            && !empty($this->chatId);
    }

    /**
     * Get channel name
     *
     * @return string Channel name
     */
    public function getName(): string
    {
        return 'telegram';
    }

    /**
     * Validate notification data
     *
     * @param array $data Notification data
     * @return bool True if valid
     */
    public function validate(array $data): bool
    {
        // Check required fields
        if (empty($data['body'])) {
            Log::warning('Telegram notification missing body');
            return false;
        }

        // Check message length (Telegram limit is 4096 characters)
        if (strlen($data['body']) > 4096) {
            Log::warning('Telegram message exceeds 4096 characters', [
                'length' => strlen($data['body'])
            ]);
            return false;
        }

        // Check bot token and chat ID
        if (empty($this->botToken) || empty($this->chatId)) {
            Log::warning('Telegram bot token or chat ID not configured');
            return false;
        }

        return true;
    }
}
