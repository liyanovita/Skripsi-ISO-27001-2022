<?php

namespace Tests\Feature\Governance;

use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use App\Models\IsoStandard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    protected function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    protected function regularUser(): User
    {
        return User::factory()->create(['role' => 'user', 'status' => 'active', 'business_sector' => 'Technology']);
    }

    protected function setupReportData(User $user): AssessmentSession
    {
        $session = AssessmentSession::create([
            'user_id' => $user->id,
            'name' => 'Technology Audit',
            'status' => 'completed',
            'overall_maturity_score' => 3.50,
        ]);

        $clause = IsoStandard::create([
            'type' => 'clause',
            'level' => '1',
            'code' => '4',
            'title' => 'Context of the Organization',
        ]);

        $subClause = IsoStandard::create([
            'parent_id' => $clause->id,
            'type' => 'clause',
            'level' => '2',
            'code' => '4.1',
            'title' => 'Understanding the organization',
        ]);

        $subClause->results()->create([
            'session_id' => $session->id,
            'status' => 'completed',
            'is_applicable' => true,
            'maturity_rating' => 3,
            'risk_priority' => 'High',
            'treatment_status' => 'open',
            'treatment_due_date' => now()->addDays(5)->toDateString(),
            'treatment_pic' => 'John Doe',
            'corrective_action_plan' => ['action' => 'Implement policy'],
        ]);

        return $session;
    }

    public function test_admin_can_view_reports_dashboard(): void
    {
        $admin = $this->adminUser();
        $user = $this->regularUser();
        $this->setupReportData($user);

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Compliance Reports')
            ->assertSee('Technology') // Sector performance
            ->assertSee('Understanding the organization') // Failing controls
            ->assertSee('clausesChart') // Chart canvas element
            ->assertSee('sectorsChart'); // Chart canvas element
    }

    public function test_non_admin_cannot_access_reports_dashboard(): void
    {
        $user = $this->regularUser();

        $this->actingAs($user)
            ->get(route('admin.reports.index'))
            ->assertRedirect();
    }

    public function test_admin_can_export_reports_csv(): void
    {
        $admin = $this->adminUser();
        $user = $this->regularUser();
        $this->setupReportData($user);

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.export_csv'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=iso27001_compliance_report_' . date('Y-m-d') . '.csv');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Session Name', $content);
        $this->assertStringContainsString('Technology Audit', $content);
        $this->assertStringContainsString('Understanding the organization', $content);
    }

    public function test_admin_can_export_reports_pdf(): void
    {
        $admin = $this->adminUser();
        $user = $this->regularUser();
        $this->setupReportData($user);

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.export_pdf'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotEmpty($response->getContent());
    }
}
