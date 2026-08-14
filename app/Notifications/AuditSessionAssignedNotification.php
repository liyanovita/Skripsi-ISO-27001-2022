<?php

namespace App\Notifications;

use App\Models\AssessmentSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuditSessionAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected AssessmentSession $session,
        protected User $assignedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('workspace.index', ['session_id' => $this->session->id]);
        $deadlineFormatted = $this->session->deadline ? $this->session->deadline->format('d M Y') : '-';

        return (new MailMessage)
            ->subject('[AuditGuard] New Audit Session Assignment: ' . $this->session->name)
            ->view('emails.audit-session-assigned', [
                'user' => $notifiable,
                'session' => $this->session,
                'assignedBy' => $this->assignedBy,
                'deadline' => $deadlineFormatted,
                'url' => $url,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'audit_session',
            'session_id' => $this->session->id,
            'session_name' => $this->session->name,
            'assigned_by' => $this->assignedBy->name,
            'message' => 'You have been assigned to a new audit session: ' . $this->session->name,
        ];
    }
}
