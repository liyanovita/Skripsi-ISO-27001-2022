<?php

namespace App\Notifications;

use App\Models\AssessmentResult;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CorrectiveActionRequiredNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected AssessmentResult $capa,
        protected User $triggeredBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('workspace.index', ['session_id' => $this->capa->session_id, 'focus' => $this->capa->id]);
        $plan = $this->capa->corrective_action_plan;
        $actionText = is_array($plan) ? ($plan['action'] ?? '-') : ($plan ?? '-');

        return (new MailMessage)
            ->subject('Tindakan Perbaikan Diperlukan: ' . $this->capa->standard->code)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Terdapat tindakan perbaikan (CAPA) yang perlu ditindaklanjuti.')
            ->line('Kontrol ISO: ' . $this->capa->standard->code . ' - ' . $this->capa->standard->title)
            ->line('PIC: ' . ($this->capa->treatment_pic ?? 'Belum Ditentukan'))
            ->line('Batas Waktu (Due Date): ' . ($this->capa->treatment_due_date ? $this->capa->treatment_due_date->format('d M Y') : '-'))
            ->line('Rencana Tindakan: ' . $actionText)
            ->action('Buka Tindakan Perbaikan', $url)
            ->line('Terima kasih telah menggunakan AuditGuard!');
    }

    public function toArray(object $notifiable): array
    {
        $plan = $this->capa->corrective_action_plan;
        $actionText = is_array($plan) ? ($plan['action'] ?? '-') : ($plan ?? '-');

        return [
            'type' => 'corrective_action',
            'result_id' => $this->capa->id,
            'session_id' => $this->capa->session_id,
            'control_code' => $this->capa->standard->code,
            'control_title' => $this->capa->standard->title,
            'pic' => $this->capa->treatment_pic,
            'due_date' => $this->capa->treatment_due_date ? $this->capa->treatment_due_date->format('Y-m-d') : null,
            'message' => 'Tindakan perbaikan diperlukan untuk kontrol ' . $this->capa->standard->code . ': ' . $actionText,
        ];
    }
}
