<?php

namespace Tests\Feature\Compliance;

use App\Models\User;
use App\Models\Organization;
use App\Models\AssessmentSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertRedirect(route('dashboard'));
    }

    public function test_admin_can_access_admin_dashboard_with_correct_stats()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['role' => 'user']);
        
        $org = Organization::create([
            'name' => 'Client Corp',
            'code' => 'CLCORP',
        ]);

        // 1. Overdue Session (deadline in past, not completed)
        AssessmentSession::create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'name' => 'Overdue Audit',
            'status' => 'in_progress',
            'deadline' => now()->subDays(5)->toDateString(),
        ]);

        // 2. Upcoming Session (deadline in next 3 days, not completed)
        AssessmentSession::create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'name' => 'Upcoming Audit',
            'status' => 'in_progress',
            'deadline' => now()->addDays(3)->toDateString(),
        ]);

        // 3. Completed Session (deadline in past but completed, should not count as overdue)
        AssessmentSession::create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'name' => 'Completed Audit',
            'status' => 'completed',
            'deadline' => now()->subDays(10)->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        
        $response->assertOk();
        $response->assertViewHas('overdueSessions', 1);
        $response->assertViewHas('upcomingSessions', 1);
        $response->assertSee('overdue audit sessions');
        $response->assertSee('sessions due within the next 7 days');
    }
}
