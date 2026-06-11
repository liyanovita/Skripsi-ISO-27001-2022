<?php

namespace Tests\Feature\Notification;

use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use App\Models\IsoStandard;
use App\Models\User;
use App\Services\Notification\CapaReminderService;
use App\Services\Notification\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CapaReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_capa_reminder_service_returns_only_due_open_or_in_progress_gaps(): void
    {
        $openDue = $this->createCapaTask([
            'treatment_due_date' => now()->addDays(2)->toDateString(),
            'treatment_status' => 'open',
        ]);

        $inProgressOverdue = $this->createCapaTask([
            'treatment_due_date' => now()->subDays(3)->toDateString(),
            'treatment_status' => 'in_progress',
        ]);

        $this->createCapaTask([
            'treatment_due_date' => now()->addDays(2)->toDateString(),
            'treatment_status' => 'closed',
        ]);

        $this->createCapaTask([
            'treatment_due_date' => now()->addDays(10)->toDateString(),
            'treatment_status' => 'open',
        ]);

        $tasks = app(CapaReminderService::class)->dueTasks(3);

        $this->assertCount(2, $tasks);
        $this->assertSameCanonicalIds([$openDue->id, $inProgressOverdue->id], $tasks->pluck('id')->all());

        $upcoming = $tasks->firstWhere('id', $openDue->id);
        $overdue = $tasks->firstWhere('id', $inProgressOverdue->id);

        $this->assertFalse($upcoming['is_overdue']);
        $this->assertSame(2, $upcoming['days_left']);
        $this->assertTrue($overdue['is_overdue']);
        $this->assertSame(-3, $overdue['days_left']);
        $this->assertSame(3, $overdue['days_overdue']);
    }

    public function test_capa_reminder_command_dry_run_does_not_send_notifications(): void
    {
        $this->createCapaTask([
            'treatment_due_date' => now()->addDay()->toDateString(),
            'treatment_status' => 'open',
        ]);

        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldNotReceive('send');
        });

        $this
            ->artisan('capa:send-reminders --dry-run')
            ->expectsOutput('CAPA reminder scan complete: 1 task(s) found.')
            ->expectsOutput('Dry run enabled. No notifications were sent.')
            ->assertSuccessful();
    }

    private function createCapaTask(array $overrides = []): AssessmentResult
    {
        $user = User::factory()->create();

        $session = AssessmentSession::create([
            'user_id' => $user->id,
            'name' => 'CAPA Reminder Test Session',
            'status' => 'completed',
        ]);

        $standard = IsoStandard::create([
            'type' => 'control',
            'level' => 'requirement',
            'code' => 'A.5.1',
            'title' => 'Policies for information security',
            'questions' => ['Are policies reviewed?'],
        ]);

        return AssessmentResult::create(array_merge([
            'session_id' => $session->id,
            'iso_standard_id' => $standard->id,
            'answers' => ['partially'],
            'maturity_rating' => 2,
            'status' => 'completed',
            'is_applicable' => true,
            'treatment_pic' => 'Security Lead',
            'treatment_due_date' => now()->addDay()->toDateString(),
            'treatment_status' => 'open',
        ], $overrides));
    }

    private function assertSameCanonicalIds(array $expected, array $actual): void
    {
        sort($expected);
        sort($actual);

        $this->assertSame($expected, $actual);
    }
}
