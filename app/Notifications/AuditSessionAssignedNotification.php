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

        return (new MailMessage)
            ->subject('Penugasan Sesi Audit Baru: ' . $this->session->name)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Anda telah ditugaskan ke sesi audit baru.')
            ->line('Nama Sesi: ' . $this->session->name)
            ->line('Ditugaskan oleh: ' . $this->assignedBy->name)
            ->line('Batas Waktu (Deadline): ' . ($this->session->deadline ? $this->session->deadline->format('d M Y') : '-'))
            ->action('Buka Sesi Audit', $url)
            ->line('Terima kasih telah menggunakan AuditGuard!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'audit_session',
            'session_id' => $this->session->id,
            'session_name' => $this->session->name,
            'assigned_by' => $this->assignedBy->name,
            'message' => 'Anda telah ditugaskan ke sesi audit baru: ' . $this->session->name,
        ];
    }
}
