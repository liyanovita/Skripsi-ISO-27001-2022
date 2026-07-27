<?php

namespace App\Services\Notification\Channels;

use App\Services\Notification\Contracts\NotificationChannelInterface;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Email Notification Channel
 * 
 * Sends notifications via Email.
 */
class EmailChannel implements NotificationChannelInterface
{
    /**
     * Send notification via Email
     *
     * @param array $data Notification data with 'pic_email' or 'email', 'subject', and 'body' key
     * @return bool True if sent successfully
     * @throws \Exception If sending fails
     */
    public function send(array $data): bool
    {
        $recipient = $data['pic_email'] ?? $data['email'] ?? null;
        if (!$recipient) {
            throw new \Exception("No recipient email address specified for EmailChannel");
        }

        try {
            Mail::to($recipient)->send(new NotificationMail($data['subject'], $data['body']));
            return true;
        } catch (\Exception $e) {
            Log::error('Email notification error', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception("Failed to send email notification: {$e->getMessage()}");
        }
    }

    /**
     * Check if Email channel is enabled
     *
     * @return bool True if enabled
     */
    public function isEnabled(): bool
    {
        return config('notifications.channels.email.enabled', true);
    }

    /**
     * Get channel name
     *
     * @return string Channel name
     */
    public function getName(): string
    {
        return 'email';
    }

    /**
     * Validate notification data
     *
     * @param array $data Notification data
     * @return bool True if valid
     */
    public function validate(array $data): bool
    {
        if (empty($data['subject']) || empty($data['body'])) {
            Log::warning('Email notification missing subject or body');
            return false;
        }

        $recipient = $data['pic_email'] ?? $data['email'] ?? null;
        if (empty($recipient)) {
            Log::warning('Email notification missing recipient email address');
            return false;
        }

        return true;
    }
}
