<?php

namespace Tests\Feature\Compliance;

use App\Models\User;
use App\Models\Organization;
use App\Models\AssessmentSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSessionsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_create_session_page()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $response = $this->actingAs($admin)->get(route('admin.sessions.create'));
        $response->assertOk();
    }

    public function test_admin_can_launch_session_for_user_and_organization()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['role' => 'user']);
        $collaborator = User::factory()->create(['role' => 'user']);
        $org = Organization::create([
            'name' => 'Assessed Corp',
            'code' => 'ASCORP',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.sessions.store'), [
            'name' => 'Q4 External Audit 2026',
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'status' => 'in_progress',
            'deadline' => '2026-12-31',
            'invited_users' => [$collaborator->id]
        ]);

        $response->assertRedirect(route('admin.sessions.index'));
        $this->assertDatabaseHas('assessment_sessions', [
            'name' => 'Q4 External Audit 2026',
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'status' => 'in_progress',
            'deadline' => '2026-12-31',
        ]);

        $session = AssessmentSession::where('name', 'Q4 External Audit 2026')->first();
        $this->assertDatabaseHas('assessment_session_users', [
            'session_id' => $session->id,
            'user_id' => $user->id,
            'role' => 'lead'
        ]);
        $this->assertDatabaseHas('assessment_session_users', [
            'session_id' => $session->id,
            'user_id' => $collaborator->id,
            'role' => 'auditor'
        ]);
    }

    public function test_admin_can_edit_session_and_change_assigned_details()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);
        $collaborator = User::factory()->create(['role' => 'user']);
        $org1 = Organization::create(['name' => 'Org One', 'code' => 'O1']);
        $org2 = Organization::create(['name' => 'Org Two', 'code' => 'O2']);

        $session = AssessmentSession::create([
            'user_id' => $user1->id,
            'organization_id' => $org1->id,
            'name' => 'Initial Audit',
            'status' => 'in_progress',
            'deadline' => '2026-06-30',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.sessions.update', $session->id), [
            'name' => 'Updated Audit Title',
            'organization_id' => $org2->id,
            'user_id' => $user2->id,
            'status' => 'in_progress',
            'deadline' => '2026-12-31',
            'invited_users' => [$collaborator->id]
        ]);

        $response->assertRedirect(route('admin.sessions.index'));
        $this->assertDatabaseHas('assessment_sessions', [
            'id' => $session->id,
            'name' => 'Updated Audit Title',
            'organization_id' => $org2->id,
            'user_id' => $user2->id,
            'deadline' => '2026-12-31',
        ]);

        $this->assertDatabaseHas('assessment_session_users', [
            'session_id' => $session->id,
            'user_id' => $user2->id,
            'role' => 'lead'
        ]);
        $this->assertDatabaseHas('assessment_session_users', [
            'session_id' => $session->id,
            'user_id' => $collaborator->id,
            'role' => 'auditor'
        ]);
    }

    public function test_admin_can_archive_session_via_update()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['role' => 'user']);
        $session = AssessmentSession::create([
            'user_id' => $user->id,
            'name' => 'Session to Archive',
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.sessions.update', $session->id), [
            'name' => 'Session to Archive',
            'user_id' => $user->id,
            'status' => 'archive',
        ]);

        $response->assertRedirect(route('admin.sessions.index'));
        $this->assertSoftDeleted('assessment_sessions', [
            'id' => $session->id,
        ]);
    }

    public function test_admin_can_restore_session_via_update()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['role' => 'user']);
        $session = AssessmentSession::create([
            'user_id' => $user->id,
            'name' => 'Session to Restore',
            'status' => 'in_progress',
        ]);
        $session->delete(); // Soft delete it first

        $response = $this->actingAs($admin)->put(route('admin.sessions.update', $session->id), [
            'name' => 'Restored Session Title',
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $response->assertRedirect(route('admin.sessions.index'));
        $this->assertDatabaseHas('assessment_sessions', [
            'id' => $session->id,
            'name' => 'Restored Session Title',
            'status' => 'in_progress',
            'deleted_at' => null,
        ]);
    }
}
