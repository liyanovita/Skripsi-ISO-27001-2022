<?php

namespace Tests\Feature\Governance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_notification_center(): void
    {
        $response = $this->get(route('notifications.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_users_can_view_notification_center(): void
    {
        $user = User::factory()->create();
        
        // Create dummy database notification
        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\AuditSessionAssignedNotification',
            'data' => [
                'type' => 'audit_session',
                'message' => 'New session assigned test',
                'session_id' => 1,
            ],
        ]);

        $response = $this->actingAs($user)->get(route('notifications.index'));
        $response->assertStatus(200);
        $response->assertSee('New session assigned test');
    }

    public function test_users_can_filter_notifications(): void
    {
        $user = User::factory()->create();

        // Create one unread notification
        $unreadNotif = $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\AuditSessionAssignedNotification',
            'data' => [
                'type' => 'audit_session',
                'message' => 'Unread notification text',
                'session_id' => 1,
            ],
            'read_at' => null,
        ]);

        // Create one read notification
        $readNotif = $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\CorrectiveActionRequiredNotification',
            'data' => [
                'type' => 'corrective_action',
                'message' => 'Read notification text',
                'session_id' => 1,
            ],
            'read_at' => now(),
        ]);

        // Filter unread
        $response = $this->actingAs($user)->get(route('notifications.index', ['filter' => 'unread']));
        $response->assertStatus(200);
        $response->assertViewHas('notifications', function ($notifications) {
            return $notifications->contains('id', $this->activeUnreadNotifId ?? '') || $notifications->contains(fn($n) => ($n->data['message'] ?? '') === 'Unread notification text') &&
                   !$notifications->contains(fn($n) => ($n->data['message'] ?? '') === 'Read notification text');
        });

        // Filter read
        $response = $this->actingAs($user)->get(route('notifications.index', ['filter' => 'read']));
        $response->assertStatus(200);
        $response->assertViewHas('notifications', function ($notifications) {
            return $notifications->contains(fn($n) => ($n->data['message'] ?? '') === 'Read notification text') &&
                   !$notifications->contains(fn($n) => ($n->data['message'] ?? '') === 'Unread notification text');
        });
    }

    public function test_user_can_mark_notification_as_read_and_unread(): void
    {
        $user = User::factory()->create();

        $notif = $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\AuditSessionAssignedNotification',
            'data' => [
                'type' => 'audit_session',
                'message' => 'Audit session assignment',
                'session_id' => 1,
            ],
            'read_at' => null,
        ]);

        $this->assertNull($notif->fresh()->read_at);

        // Mark as read
        $response = $this->actingAs($user)->from(route('notifications.index'))->post(route('notifications.read', $notif->id));
        $response->assertRedirect(route('notifications.index'));
        $this->assertNotNull($notif->fresh()->read_at);

        // Mark as unread
        $response = $this->actingAs($user)->from(route('notifications.index'))->post(route('notifications.unread', $notif->id));
        $response->assertRedirect(route('notifications.index'));
        $this->assertNull($notif->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();

        $notif1 = $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\AuditSessionAssignedNotification',
            'data' => ['message' => 'Notif 1'],
            'read_at' => null,
        ]);

        $notif2 = $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\CorrectiveActionRequiredNotification',
            'data' => ['message' => 'Notif 2'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->from(route('notifications.index'))->post(route('notifications.read-all'));
        $response->assertRedirect(route('notifications.index'));

        $this->assertNotNull($notif1->fresh()->read_at);
        $this->assertNotNull($notif2->fresh()->read_at);
    }

    public function test_admin_can_access_admin_notification_center(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        
        $admin->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\AuditSessionAssignedNotification',
            'data' => [
                'type' => 'audit_session',
                'message' => 'New admin alert',
                'session_id' => 1,
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.notifications.index'));
        $response->assertStatus(200);
        $response->assertSee('New admin alert');
        $response->assertViewIs('admin.notifications.index');
    }

    public function test_non_admin_cannot_access_admin_notification_center(): void
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);

        $response = $this->actingAs($user)->get(route('admin.notifications.index'));
        $response->assertRedirect(route('dashboard'));
    }

    public function test_admin_can_mark_notification_as_read_in_admin_center(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $notif = $admin->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\AuditSessionAssignedNotification',
            'data' => [
                'type' => 'audit_session',
                'message' => 'Admin task assignment',
                'session_id' => 1,
            ],
            'read_at' => null,
        ]);

        $response = $this->actingAs($admin)->from(route('admin.notifications.index'))->post(route('admin.notifications.read', $notif->id));
        $response->assertRedirect(route('admin.notifications.index'));
        $this->assertNotNull($notif->fresh()->read_at);
    }
}
