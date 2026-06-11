<?php

namespace App\Services\Notification;

use App\Services\Notification\Contracts\NotificationChannelInterface;
use App\Services\Notification\Templates\TemplateRenderer;
use Illuminate\Support\Facades\Log;

/**
 * Notification Service
 * 
 * Main orchestrator for sending notifications through multiple channels.
 * Handles template rendering, channel selection, error handling, and retry logic.
 */
class NotificationService
{
    protected TemplateRenderer $templateRenderer;
    protected array $channels = [];

    public function __construct()
    {
        $this->templateRenderer = new TemplateRenderer();
        $this->initializeChannels();
    }

    /**
     * Initialize available notification channels
     *
     * @return void
     */
    protected function initializeChannels(): void
    {
        // Register Telegram channel
        if (config('notifications.channels.telegram.enabled')) {
            $this->channels['telegram'] = new Channels\TelegramChannel();
        }

        // Future: Register Email, SMS, Slack, etc.
    }

    /**
     * Send notification to multiple channels
     *
     * @param array $channels Channel names to send to
     * @param string $template Template name
     * @param array $data Data for template variables
     * @return array Results for each channel ['channel' => ['success' => bool, 'reason' => string|null]]
     */
    public function send(array $channels, string $template, array $data): array
    {
        $results = [];

        // Check if notification system is enabled
        if (!config('notifications.enabled', true)) {
            Log::info('Notification system is disabled');
            return array_fill_keys($channels, [
                'success' => false,
                'reason' => 'Notification system disabled'
            ]);
        }

        foreach ($channels as $channelName) {
            try {
                $results[$channelName] = [
                    'success' => $this->sendToChannel($channelName, $template, $data),
                    'reason' => null
                ];
            } catch (\Exception $e) {
                $results[$channelName] = [
                    'success' => false,
                    'reason' => $e->getMessage()
                ];

                $this->logError($channelName, $template, $data, $e);
            }
        }

        return $results;
    }

    /**
     * Send notification to a specific channel
     *
     * @param string $channelName Channel name
     * @param string $template Template name
     * @param array $data Data for template variables
     * @return bool True if sent successfully
     * @throws \Exception If channel not found or disabled
     */
    public function sendToChannel(string $channelName, string $template, array $data): bool
    {
        // Check if channel exists
        if (!isset($this->channels[$channelName])) {
            throw new \Exception("Channel '{$channelName}' not found or disabled");
        }

        $channel = $this->channels[$channelName];

        // Check if channel is enabled
        if (!$channel->isEnabled()) {
            throw new \Exception("Channel '{$channelName}' is disabled");
        }

        // Render template for this channel
        $rendered = $this->templateRenderer->render($template, $channelName, $data);

        // Prepare notification data
        $notificationData = array_merge($data, [
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
        ]);

        // Validate data
        if (!$channel->validate($notificationData)) {
            throw new \Exception("Invalid data for channel '{$channelName}'");
        }

        // Send with retry logic
        $success = $this->sendWithRetry($channel, $notificationData);

        // Log result
        if ($success) {
            $this->logSuccess($channelName, $template, $data);
        }

        return $success;
    }

    /**
     * Send notification with retry logic
     *
     * @param NotificationChannelInterface $channel Channel instance
     * @param array $data Notification data
     * @return bool True if sent successfully
     */
    protected function sendWithRetry(NotificationChannelInterface $channel, array $data): bool
    {
        $channelName = $channel->getName();
        $maxAttempts = config("notifications.channels.{$channelName}.retry_attempts", 3);
        $retryDelay = config("notifications.channels.{$channelName}.retry_delay", 5);
        
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            try {
                if ($channel->send($data)) {
                    if ($attempt > 0) {
                        Log::info("Notification sent successfully on attempt " . ($attempt + 1), [
                            'channel' => $channelName,
                            'attempts' => $attempt + 1,
                        ]);
                    }
                    return true;
                }
            } catch (\Exception $e) {
                $attempt++;
                
                Log::warning("Notification attempt {$attempt} failed for {$channelName}", [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                ]);

                if ($attempt < $maxAttempts) {
                    // Exponential backoff: 5s, 10s, 20s
                    $delay = $retryDelay * pow(2, $attempt - 1);
                    sleep($delay);
                }
            }
        }

        return false;
    }

    /**
     * Get all enabled channels
     *
     * @return array Array of channel names
     */
    public function getEnabledChannels(): array
    {
        return array_keys($this->channels);
    }

    /**
     * Check if a specific channel is enabled
     *
     * @param string $channelName Channel name
     * @return bool True if enabled
     */
    public function isChannelEnabled(string $channelName): bool
    {
        return isset($this->channels[$channelName]) && $this->channels[$channelName]->isEnabled();
    }

    /**
     * Render template without sending
     *
     * @param string $template Template name
     * @param string $channel Channel name
     * @param array $data Data for template variables
     * @return array Rendered template with 'subject' and 'body'
     */
    public function renderTemplate(string $template, string $channel, array $data): array
    {
        return $this->templateRenderer->render($template, $channel, $data);
    }

    /**
     * Log successful notification
     *
     * @param string $channel Channel name
     * @param string $template Template name
     * @param array $data Notification data
     * @return void
     */
    protected function logSuccess(string $channel, string $template, array $data): void
    {
        if (!config('notifications.logging.enabled', true)) {
            return;
        }

        Log::channel(config('notifications.logging.channel', 'stack'))
            ->log(config('notifications.logging.level', 'info'), "Notification sent successfully", [
                'channel' => $channel,
                'template' => $template,
                'recipient' => $data['pic'] ?? $data['email'] ?? 'unknown',
            ]);
    }

    /**
     * Log notification error
     *
     * @param string $channel Channel name
     * @param string $template Template name
     * @param array $data Notification data
     * @param \Exception $exception Exception that occurred
     * @return void
     */
    protected function logError(string $channel, string $template, array $data, \Exception $exception): void
    {
        if (!config('notifications.logging.enabled', true)) {
            return;
        }

        Log::channel(config('notifications.logging.channel', 'stack'))
            ->error("Notification failed", [
                'channel' => $channel,
                'template' => $template,
                'recipient' => $data['pic'] ?? $data['email'] ?? 'unknown',
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
    }
}
