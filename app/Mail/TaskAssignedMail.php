<?php

namespace App\Mail;

use App\Models\AssessmentResult;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public AssessmentResult $result;
    public User $user;

    /**
     * Create a new message instance.
     */
    public function __construct(AssessmentResult $result, User $user)
    {
        $this->result = $result;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . config('app.name', 'AuditGuard') . '] ' . __('New Compliance Task Assigned') . ': ' . ($this->result->standard->code ?? ''),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.task-assigned',
        );
    }
}
