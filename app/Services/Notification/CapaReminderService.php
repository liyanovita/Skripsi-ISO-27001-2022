<?php

namespace App\Services\Notification;

use App\Models\AssessmentResult;
use Illuminate\Support\Collection;

class CapaReminderService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function dueTasks(int $daysAhead): Collection
    {
        $limitDate = now()->addDays($daysAhead)->endOfDay();

        return AssessmentResult::with(['standard', 'session'])
            ->whereNotNull('treatment_due_date')
            ->where('treatment_due_date', '<=', $limitDate)
            ->where('status', 'completed')
            ->where('is_applicable', true)
            ->where('maturity_rating', '<', 4)
            ->where(function ($query) {
                $query
                    ->whereNull('treatment_status')
                    ->orWhereIn('treatment_status', ['open', 'in_progress']);
            })
            ->get()
            ->map(fn(AssessmentResult $task) => $this->formatTask($task));
    }

    public function sendDueReminders(?int $daysAhead = null, ?array $channels = null, bool $dryRun = false): array
    {
        $daysAhead ??= (int) config('notifications.capa_reminders.days_ahead', 3);
        $channels ??= config('notifications.capa_reminders.channels', ['telegram']);

        $tasks = $this->dueTasks($daysAhead);
        $results = [];

        foreach ($tasks as $task) {
            $template = $task['is_overdue'] ? 'capa_overdue' : 'capa_upcoming';

            $results[$task['id']] = [
                'task' => $task,
                'template' => $template,
                'results' => $dryRun
                    ? array_fill_keys($channels, ['success' => true, 'reason' => 'Dry run'])
                    : $this->notificationService->send($channels, $template, $task),
            ];
        }

        return [
            'total' => $tasks->count(),
            'sent' => $dryRun ? 0 : $this->countSuccessfulTasks($results),
            'dry_run' => $dryRun,
            'tasks' => $tasks,
            'results' => $results,
        ];
    }

    protected function formatTask(AssessmentResult $task): array
    {
        $dueDate = $task->treatment_due_date->copy()->startOfDay();
        $today = now()->startOfDay();
        $daysDifference = (int) $today->diffInDays($dueDate, false);

        return [
            'id' => $task->id,
            'pic' => $task->treatment_pic ?? 'Unassigned',
            'due_date' => $task->treatment_due_date->format('Y-m-d'),
            'is_overdue' => $daysDifference < 0,
            'days_left' => $daysDifference,
            'days_overdue' => abs(min($daysDifference, 0)),
            'control_code' => $task->standard?->code ?? 'N/A',
            'control_title' => $task->standard?->title ?? 'Untitled control',
            'session_name' => $task->session?->name ?? 'Untitled session',
        ];
    }

    protected function countSuccessfulTasks(array $results): int
    {
        return collect($results)
            ->filter(fn(array $result) => collect($result['results'])->contains('success', true))
            ->count();
    }
}
