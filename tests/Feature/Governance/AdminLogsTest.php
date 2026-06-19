<?php

namespace Tests\Feature\Governance;

use App\Models\AuditTrail;
use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use App\Models\IsoStandard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLogsTest extends TestCase
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

    protected function seedLog(User $actor, string $field = 'maturity_rating', string $newVal = '3'): AuditTrail
    {
        return AuditTrail::create([
            'user_id'       => $actor->id,
            'model_type'    => AssessmentResult::class,
            'model_id'      => 1,
            'action'        => 'updated',
            'field_changed' => $field,
            'old_value'     => '1',
            'new_value'     => $newVal,
        ]);
    }

    public function test_admin_can_view_logs_index_with_stats(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();
        $this->seedLog($user, 'treatment_status', 'in_progress');

        $this->actingAs($admin)
            ->get(route('admin.logs.index'))
            ->assertOk()
            ->assertSee('System Logs')
            ->assertSee('Total Events')
            ->assertSee('Changes Today')
            ->assertSee('Unique Actors')
            ->assertSee($user->name)
            ->assertSee('treatment_status');
    }

    public function test_non_admin_cannot_access_logs(): void
    {
        $user = $this->regularUser();

        $this->actingAs($user)
            ->get(route('admin.logs.index'))
            ->assertRedirect();
    }

    public function test_admin_can_filter_logs_by_action(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();

        // Unique sentinel values to avoid collisions with dropdown option text
        AuditTrail::create([
            'user_id' => $user->id, 'model_type' => AssessmentResult::class,
            'model_id' => 1, 'action' => 'created',
            'field_changed' => 'status', 'old_value' => null, 'new_value' => 'sentinel_created_only',
        ]);
        AuditTrail::create([
            'user_id' => $user->id, 'model_type' => AssessmentResult::class,
            'model_id' => 2, 'action' => 'deleted',
            'field_changed' => 'status', 'old_value' => 'sentinel_deleted_only', 'new_value' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.logs.index', ['action' => 'created']));

        $response->assertOk()
            ->assertSee('sentinel_created_only')
            ->assertDontSee('sentinel_deleted_only');
    }

    public function test_admin_can_filter_logs_by_user(): void
    {
        $admin   = $this->adminUser();
        $userA   = $this->regularUser();
        $userB   = User::factory()->create(['role' => 'user', 'status' => 'active']);

        // Use unique new_value sentinels to distinguish rows
        $this->seedLog($userA, 'risk_priority', 'user_a_sentinel_value');
        $this->seedLog($userB, 'risk_priority', 'user_b_sentinel_value');

        $response = $this->actingAs($admin)
            ->get(route('admin.logs.index', ['user_id' => $userA->id]));

        $response->assertOk()
            ->assertSee('user_a_sentinel_value')
            ->assertDontSee('user_b_sentinel_value');
    }

    public function test_admin_can_search_logs(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();
        $this->seedLog($user, 'treatment_pic', 'unique_sentinel_value');

        $this->actingAs($admin)
            ->get(route('admin.logs.index', ['search' => 'unique_sentinel_value']))
            ->assertOk()
            ->assertSee('unique_sentinel_value');
    }

    public function test_admin_can_export_logs_csv(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();
        $this->seedLog($user, 'maturity_rating', 'csv_export_marker');

        $response = $this->actingAs($admin)
            ->get(route('admin.logs.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Timestamp', $content);
        $this->assertStringContainsString('csv_export_marker', $content);
        $this->assertStringContainsString('maturity_rating', $content);
    }
}
