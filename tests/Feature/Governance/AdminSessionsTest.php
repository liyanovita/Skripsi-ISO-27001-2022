<?php

namespace Tests\Feature\Governance;

use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\IsoStandard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSessionsTest extends TestCase
{
    use RefreshDatabase;

    protected function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    protected function regularUser(): User
    {
        return User::factory()->create(['role' => 'user', 'status' => 'active']);
    }

    protected function makeSession(User $user, string $status = 'in_progress', float $score = 2.5): AssessmentSession
    {
        return AssessmentSession::create([
            'user_id'               => $user->id,
            'name'                  => 'Test Audit Session',
            'status'                => $status,
            'overall_maturity_score'=> $score,
        ]);
    }

    public function test_admin_can_view_sessions_index_with_kpi_stats(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();

        $this->makeSession($user, 'in_progress');
        $this->makeSession($user, 'completed', 4.0);

        $this->actingAs($admin)
            ->get(route('admin.sessions.index'))
            ->assertOk()
            ->assertSee('Audit Sessions')
            ->assertSee('Total Sessions')
            ->assertSee('In Progress')
            ->assertSee('Completed')
            ->assertSee('Test Audit Session');
    }

    public function test_non_admin_cannot_access_sessions_index(): void
    {
        $user = $this->regularUser();

        $this->actingAs($user)
            ->get(route('admin.sessions.index'))
            ->assertRedirect();
    }

    public function test_admin_can_filter_sessions_by_status(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();

        $this->makeSession($user, 'completed', 4.0);
        AssessmentSession::create([
            'user_id' => $user->id, 'name' => 'Draft Only Session',
            'status' => 'in_progress', 'overall_maturity_score' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.sessions.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('Test Audit Session')
            ->assertDontSee('Draft Only Session');
    }

    public function test_admin_can_search_sessions(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();

        $this->makeSession($user, 'in_progress');
        AssessmentSession::create([
            'user_id' => $user->id, 'name' => 'Unique Sentinel Session XYZ',
            'status' => 'in_progress', 'overall_maturity_score' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.sessions.index', ['search' => 'Unique Sentinel Session XYZ']))
            ->assertOk()
            ->assertSee('Unique Sentinel Session XYZ')
            ->assertDontSee('Test Audit Session');
    }

    public function test_admin_can_view_session_detail(): void
    {
        $admin   = $this->adminUser();
        $user    = $this->regularUser();
        $session = $this->makeSession($user, 'completed', 3.5);

        $standard = IsoStandard::create([
            'type' => 'control', 'level' => 'requirement',
            'code' => 'A.5.1', 'title' => 'Security Policies',
            'questions' => ['Is a policy documented?'],
        ]);

        AssessmentResult::create([
            'session_id'     => $session->id,
            'iso_standard_id'=> $standard->id,
            'maturity_rating'=> 3,
            'status'         => 'completed',
            'is_applicable'  => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.sessions.show', $session))
            ->assertOk()
            ->assertSee($session->name)
            ->assertSee($user->name)
            ->assertSee('Total')
            ->assertSee('Compliant');
    }

    public function test_admin_can_delete_session(): void
    {
        $admin   = $this->adminUser();
        $user    = $this->regularUser();
        $session = $this->makeSession($user);

        $this->actingAs($admin)
            ->delete(route('admin.sessions.destroy', $session))
            ->assertRedirect(route('admin.sessions.index'));

        $this->assertDatabaseMissing('assessment_sessions', ['id' => $session->id]);
    }

    public function test_admin_can_filter_sessions_by_date_range(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();

        // Create session dated last month
        $oldSession = new AssessmentSession([
            'user_id'    => $user->id,
            'name'       => 'Old Month Session',
            'status'     => 'in_progress',
        ]);
        $oldSession->timestamps = false;
        $oldSession->created_at = now()->subMonth();
        $oldSession->updated_at = now()->subMonth();
        $oldSession->save();

        // Create session dated today
        $newSession = AssessmentSession::create([
            'user_id' => $user->id,
            'name'    => 'Today Session',
            'status'  => 'in_progress',
        ]);

        // Filter: only today
        $this->actingAs($admin)
            ->get(route('admin.sessions.index', [
                'date_from' => now()->toDateString(),
                'date_to'   => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Today Session')
            ->assertDontSee('Old Month Session');
    }
}
