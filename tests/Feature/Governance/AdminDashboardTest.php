<?php

namespace Tests\Feature\Governance;

use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use App\Models\CommunityTemplate;
use App\Models\IsoStandard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    protected function regularUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge(['role' => 'user', 'status' => 'active'], $attrs));
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('System Overview')
            ->assertSee('Total Users')
            ->assertSee('Active Sessions')
            ->assertSee('Completed Sessions')
            ->assertSee('Avg Maturity')
            ->assertSee('Suspended');
    }

    public function test_non_admin_cannot_access_dashboard(): void
    {
        $user = $this->regularUser();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertRedirect();
    }

    public function test_dashboard_shows_correct_user_counts(): void
    {
        $admin     = $this->adminUser();
        $active    = $this->regularUser(['status' => 'active']);
        $suspended = $this->regularUser(['status' => 'suspended']);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        // Total users should be 3 (admin + active + suspended)
        $response->assertSee('3');
    }

    public function test_dashboard_shows_session_kpis(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();

        AssessmentSession::create([
            'user_id' => $user->id, 'name' => 'Active Session',
            'status'  => 'in_progress',
        ]);
        AssessmentSession::create([
            'user_id' => $user->id, 'name' => 'Done Session',
            'status'  => 'completed', 'overall_maturity_score' => 3.5,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Active Session')
            ->assertSee('Done Session');
    }

    public function test_dashboard_shows_capa_alert_when_overdue_exists(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();

        $session  = AssessmentSession::create([
            'user_id' => $user->id, 'name' => 'CAPA Session', 'status' => 'in_progress',
        ]);
        $standard = IsoStandard::create([
            'type' => 'control', 'level' => '1',
            'code' => 'A.9.1', 'title' => 'Access Control Policy',
        ]);
        AssessmentResult::create([
            'session_id'        => $session->id,
            'iso_standard_id'   => $standard->id,
            'maturity_rating'   => 2,
            'status'            => 'completed',
            'is_applicable'     => true,
            'treatment_status'  => 'open',
            'treatment_due_date'=> now()->subDay()->toDateString(), // overdue
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('CAPA Tasks Require Immediate Attention')
            ->assertSee('overdue');
    }

    public function test_dashboard_shows_governance_indicators(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();

        \App\Models\CommunityTemplate::create([
            'user_id'         => $user->id,
            'title'           => 'Sample Audit Template',
            'description'     => 'A test template for ISO 27001 compliance.',
            'author_name'     => $user->name,
            'base_score'      => 3.5,
            'downloads_count' => 5,
            'upvotes'         => 2,
            'rating_count'    => 1,
            'avg_rating'      => 4.0,
            'tags'            => ['security', 'iso27001'],
            'content_data'    => ['session' => [], 'results' => []],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Governance Indicators')
            ->assertSee('Community Templates')
            ->assertSee('Knowledge Base Articles');
    }

    public function test_dashboard_shows_recent_users_and_sessions(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser(['name' => 'Recent User Alpha']);

        AssessmentSession::create([
            'user_id' => $user->id,
            'name'    => 'Recent Audit Session Beta',
            'status'  => 'in_progress',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Recent User Registrations')
            ->assertSee('Recent User Alpha')
            ->assertSee('Recent Audit Activity')
            ->assertSee('Recent Audit Session Beta');
    }
}
