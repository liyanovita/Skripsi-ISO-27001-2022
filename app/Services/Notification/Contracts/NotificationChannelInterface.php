<?php

namespace App\Services\Notification\Contracts;

/**
 * Interface for notification channels
 * 
 * All notification channels (Telegram, Email, SMS, etc.) must implement this interface
 * to ensure consistent behavior across different delivery methods.
 */
interface NotificationChannelInterface
{
    /**
     * Send notification through this channel
     *
     * @param array $data Notification data including message, recipient, etc.
     * @return bool True if sent successfully, false otherwise
     */
    public function send(array $data): bool;

    /**
     * Check if this channel is enabled in configuration
     *
     * @return bool True if channel is enabled, false otherwise
     */
    public function isEnabled(): bool;

    /**
     * Get the channel name
     *
     * @return string Channel name (e.g., 'telegram', 'email', 'sms')
     */
    public function getName(): string;

    /**
     * Validate notification data before sending
     *
     * @param array $data Notification data to validate
     * @return bool True if data is valid, false otherwise
     */
    public function validate(array $data): bool;
}
