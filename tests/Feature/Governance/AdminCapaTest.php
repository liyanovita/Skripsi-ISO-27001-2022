<?php

namespace Tests\Feature\Governance;

use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use App\Models\IsoStandard;
use App\Models\User;
use App\Models\AuditTrail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCapaTest extends TestCase
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

    protected function setupCapaData(User $user, string $code, string $title, array $resultAttrs = []): AssessmentResult
    {
        $session = AssessmentSession::create([
            'user_id' => $user->id,
            'name' => 'IT Department Audit ' . $code,
            'status' => 'in_progress',
        ]);

        $standard = IsoStandard::create([
            'type' => 'clause',
            'level' => '1',
            'code' => $code,
            'title' => $title,
        ]);

        return $standard->results()->create(array_merge([
            'session_id' => $session->id,
            'status' => 'in_progress',
            'is_applicable' => true,
            'maturity_rating' => 2, // Non-compliant (<4) -> triggers CAPA
            'notes' => 'Some findings here.',
            'risk_priority' => 'High',
            'treatment_status' => 'open',
            'treatment_due_date' => now()->addDays(5)->toDateString(),
            'treatment_pic' => 'Admin User',
            'corrective_action_plan' => ['action' => 'Implement policy'],
        ], $resultAttrs));
    }

    public function test_admin_can_view_capa_index(): void
    {
        $admin = $this->adminUser();
        $user = $this->regularUser();
        $capa = $this->setupCapaData($user, '4.1', 'Context of the organization');

        $this->actingAs($admin)
            ->get(route('admin.capa.index'))
            ->assertOk()
            ->assertSee('Corrective')
            ->assertSee('Preventive Actions')
            ->assertSee($user->name)
            ->assertSee('Context of the organization')
            ->assertSee('4.1');
    }

    public function test_non_admin_cannot_access_capa(): void
    {
        $user = $this->regularUser();

        $this->actingAs($user)
            ->get(route('admin.capa.index'))
            ->assertRedirect();
    }

    public function test_admin_can_filter_capa_by_status(): void
    {
        $admin = $this->adminUser();
        $user = $this->regularUser();
        
        $openCapa = $this->setupCapaData($user, 'A.5.1', 'Open Standard', ['treatment_status' => 'open']);
        $completedCapa = $this->setupCapaData($user, 'A.5.2', 'Completed Standard', [
            'treatment_status' => 'completed', 
            'maturity_rating' => 1 // Keep CAPA eligible via rating
        ]);

        // Filter Open
        $this->actingAs($admin)
            ->get(route('admin.capa.index', ['status' => 'open']))
            ->assertOk()
            ->assertSee('Open Standard')
            ->assertDontSee('Completed Standard');

        // Filter Completed
        $this->actingAs($admin)
            ->get(route('admin.capa.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('Completed Standard')
            ->assertDontSee('Open Standard');
    }

    public function test_admin_can_filter_capa_by_risk(): void
    {
        $admin = $this->adminUser();
        $user = $this->regularUser();

        $highCapa = $this->setupCapaData($user, 'A.6.1', 'High Risk Standard', ['risk_priority' => 'High']);
        $lowCapa = $this->setupCapaData($user, 'A.6.2', 'Low Risk Standard', ['risk_priority' => 'Low']);

        $this->actingAs($admin)
            ->get(route('admin.capa.index', ['risk' => 'High']))
            ->assertOk()
            ->assertSee('High Risk Standard')
            ->assertDontSee('Low Risk Standard');
    }

    public function test_admin_can_view_edit_capa_form_with_history(): void
    {
        $admin = $this->adminUser();
        $user = $this->regularUser();
        $capa = $this->setupCapaData($user, '4.2', 'Interested Parties');

        // Add dummy audit trail history
        AuditTrail::create([
            'user_id' => $admin->id,
            'model_type' => get_class($capa),
            'model_id' => $capa->id,
            'action' => 'updated',
            'field_changed' => 'treatment_status',
            'old_value' => 'open',
            'new_value' => 'in_progress',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.capa.edit', $capa))
            ->assertOk()
            ->assertSee('Manage Corrective Action')
            ->assertSee('CAPA Modification Timeline')
            ->assertSee('treatment status')
            ->assertSee('in_progress');
    }

    public function test_admin_can_update_capa_and_trigger_audit_trail(): void
    {
        $admin = $this->adminUser();
        $user = $this->regularUser();
        $capa = $this->setupCapaData($user, '4.3', 'Scope', [
            'treatment_status' => 'open',
            'risk_priority' => 'High',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.capa.update', $capa), [
                'treatment_status' => 'in_progress',
                'risk_priority' => 'Critical',
                'treatment_pic' => 'Jane Doe',
                'treatment_due_date' => now()->addDays(10)->toDateString(),
                'corrective_action_plan_text' => 'New Action Description',
            ])
            ->assertRedirect(route('admin.capa.index'))
            ->assertSessionHas('success');

        $capa->refresh();
        $this->assertEquals('in_progress', $capa->treatment_status);
        $this->assertEquals('Critical', $capa->risk_priority);
        $this->assertEquals('Jane Doe', $capa->treatment_pic);
        $this->assertEquals('New Action Description', $capa->corrective_action_plan['action']);

        // Assert AuditTrail is automatically written
        $this->assertDatabaseHas('audit_trails', [
            'model_type' => get_class($capa),
            'model_id' => $capa->id,
            'field_changed' => 'treatment_status',
            'old_value' => 'open',
            'new_value' => 'in_progress',
        ]);
    }

    public function test_admin_can_export_capa_as_csv(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();
        $this->setupCapaData($user, '5.1', 'Policies Export Test');

        $response = $this->actingAs($admin)
            ->get(route('admin.capa.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('User Name', $content);
        $this->assertStringContainsString('ISO Code', $content);
        $this->assertStringContainsString('CAPA Status', $content);
        $this->assertStringContainsString('Policies Export Test', $content);
    }

    public function test_non_admin_cannot_export_capa_csv(): void
    {
        $user = $this->regularUser();

        $this->actingAs($user)
            ->get(route('admin.capa.export'))
            ->assertRedirect();
    }
}
