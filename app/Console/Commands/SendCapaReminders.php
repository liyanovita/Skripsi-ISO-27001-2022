<?php

namespace App\Console\Commands;

use App\Services\Notification\CapaReminderService;
use Illuminate\Console\Command;

class SendCapaReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'capa:send-reminders
                            {--days= : Number of days ahead to include}
                            {--dry-run : Preview reminder tasks without sending notifications}';

    /**
     * The console command description.
     */
    protected $description = 'Send CAPA due-date reminders through configured notification channels';

    public function __construct(protected CapaReminderService $capaReminderService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = $this->option('days') !== null
            ? (int) $this->option('days')
            : (int) config('notifications.capa_reminders.days_ahead', 3);

        $summary = $this->capaReminderService->sendDueReminders(
            $days,
            dryRun: (bool) $this->option('dry-run')
        );

        $this->info("CAPA reminder scan complete: {$summary['total']} task(s) found.");

        if ($summary['dry_run']) {
            $this->warn('Dry run enabled. No notifications were sent.');
        } else {
            $this->info("Notifications sent for {$summary['sent']} task(s).");
        }

        foreach ($summary['tasks'] as $task) {
            $status = $task['is_overdue'] ? 'OVERDUE' : 'UPCOMING';
            $this->line("[{$status}] {$task['control_code']} - {$task['pic']} - due {$task['due_date']}");
        }

        return self::SUCCESS;
    }
}
