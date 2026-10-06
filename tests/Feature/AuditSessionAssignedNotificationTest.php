<?php

namespace Tests\Feature;

use App\Models\AssessmentSession;
use App\Models\User;
use App\Notifications\AuditSessionAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditSessionAssignedNotificationTest extends TestCase
{
    public function test_audit_session_assigned_notification_renders_correctly(): void
    {
        $assignedBy = new User(['name' => 'Auditor Admin']);
        $user = new User(['name' => 'Liya Novitasari']);
        
        $session = new AssessmentSession([
            'id' => 2,
            'name' => 'Audit Trial 2',
            'deadline' => \Carbon\Carbon::parse('2026-08-29'),
        ]);

        $notification = new AuditSessionAssignedNotification($session, $assignedBy);
        $mail = $notification->toMail($user);

        $this->assertEquals('[AuditGuard] New Audit Session Assignment: Audit Trial 2', $mail->subject);

        $html = $mail->render();
        $this->assertStringContainsString('Hello Liya Novitasari,', $html);
        $this->assertStringContainsString('You have been assigned to a new audit session.', $html);
        $this->assertStringContainsString('Audit Trial 2', $html);
        $this->assertStringContainsString('Auditor Admin', $html);
        $this->assertStringContainsString('29 Aug 2026', $html);
        $this->assertStringContainsString('Open Audit Session', $html);
        $this->assertStringContainsString('Audit Team', $html);
        $this->assertStringContainsString('AuditGuard', $html);
    }
}
