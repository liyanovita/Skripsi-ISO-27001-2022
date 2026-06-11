<?php

namespace Tests\Feature\Compliance;

use App\Exports\SoaExport;
use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use App\Models\IsoStandard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_compliance_center_renders_for_authenticated_user(): void
    {
        [$user, $session, $result] = $this->createWorkspaceFixture();

        $response = $this
            ->actingAs($user)
            ->get(route('workspace.index', ['session_id' => $session->id, 'tab' => 'gap-report']));

        $response
            ->assertOk()
            ->assertSee('Compliance Center')
            ->assertSee($session->name)
            ->assertSee($result->standard->code);
    }

    public function test_workspace_entry_can_be_updated_by_owner(): void
    {
        [$user, , $result] = $this->createWorkspaceFixture();

        $response = $this
            ->actingAs($user)
            ->patchJson(route('workspace.entry.update', $result), [
                'is_applicable' => false,
                'soa_justification' => 'Excluded because the process is outsourced.',
                'treatment_due_date' => '2026-06-15',
                'treatment_pic' => 'Security Lead',
                'treatment_status' => 'in_progress',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_applicable', false)
            ->assertJsonPath('data.treatment_due_date', '2026-06-15');

        $result->refresh();

        $this->assertFalse($result->is_applicable);
        $this->assertSame('Excluded because the process is outsourced.', $result->soa_justification);
        $this->assertSame('Security Lead', $result->treatment_pic);
        $this->assertSame('in_progress', $result->treatment_status);
    }

    public function test_workspace_entry_cannot_be_updated_by_another_user(): void
    {
        [, , $result] = $this->createWorkspaceFixture();
        $otherUser = User::factory()->create();

        $response = $this
            ->actingAs($otherUser)
            ->patchJson(route('workspace.entry.update', $result), [
                'treatment_status' => 'closed',
            ]);

        $response->assertNotFound();
        $this->assertSame('open', $result->refresh()->treatment_status);
    }

    public function test_not_applicable_controls_are_excluded_from_active_gap_counts(): void
    {
        [$user, $session] = $this->createWorkspaceFixture();

        $excludedStandard = IsoStandard::create([
            'type' => 'control',
            'level' => 'requirement',
            'code' => 'A.5.2',
            'title' => 'Excluded control',
            'questions' => ['Is this control applicable?'],
        ]);

        AssessmentResult::create([
            'session_id' => $session->id,
            'iso_standard_id' => $excludedStandard->id,
            'answers' => ['no'],
            'maturity_rating' => 1,
            'status' => 'completed',
            'is_applicable' => false,
            'treatment_status' => 'closed',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('workspace.index', ['session_id' => $session->id, 'tab' => 'gap-report']));

        $response->assertOk();

        $this->assertSame(1, $response->viewData('stats')['gaps']);
        $this->assertSame(0, $response->viewData('stats')['closed']);
        $this->assertSame(1, $response->viewData('tacticalStats')['total_gaps']);
        $this->assertSame(0, $response->viewData('tacticalStats')['critical']);
        $this->assertCount(1, $response->viewData('findings'));
    }

    public function test_soa_excel_export_is_limited_to_owned_sessions(): void
    {
        [$user, $session] = $this->createWorkspaceFixture();
        $otherUser = User::factory()->create();

        Excel::fake();

        $this
            ->actingAs($user)
            ->get(route('workspace.export-soa', $session))
            ->assertOk();

        Excel::assertDownloaded('SoA_ISO27001_Internal_Audit_2026.xlsx', fn(SoaExport $export) => true);

        $this
            ->actingAs($otherUser)
            ->get(route('workspace.export-soa', $session))
            ->assertNotFound();
    }

    public function test_soa_pdf_export_uses_safe_filename_and_owned_sessions(): void
    {
        [$user, $session] = $this->createWorkspaceFixture();
        $otherUser = User::factory()->create();

        $session->update(['name' => 'Audit: Q2 / Main Site']);

        $this
            ->actingAs($user)
            ->get(route('workspace.export-soa-pdf', $session))
            ->assertOk()
            ->assertDownload('SoA_ISO27001_Audit-_Q2_-_Main_Site.pdf');

        $this
            ->actingAs($otherUser)
            ->get(route('workspace.export-soa-pdf', $session))
            ->assertNotFound();
    }

    private function createWorkspaceFixture(): array
    {
        $user = User::factory()->create();

        $session = AssessmentSession::create([
            'user_id' => $user->id,
            'name' => 'Internal Audit 2026',
            'status' => 'in_progress',
            'overall_maturity_score' => 2.00,
        ]);

        $parent = IsoStandard::create([
            'type' => 'control',
            'level' => 'domain',
            'code' => 'A.5',
            'title' => 'Organizational controls',
            'questions' => [],
        ]);

        $standard = IsoStandard::create([
            'parent_id' => $parent->id,
            'type' => 'control',
            'level' => 'requirement',
            'code' => 'A.5.1',
            'title' => 'Policies for information security',
            'description' => 'Policies must be defined and reviewed.',
            'questions' => ['Are information security policies reviewed?'],
        ]);

        $result = AssessmentResult::create([
            'session_id' => $session->id,
            'iso_standard_id' => $standard->id,
            'answers' => ['yes'],
            'maturity_rating' => 2,
            'status' => 'completed',
            'is_applicable' => true,
            'treatment_status' => 'open',
        ]);

        return [$user, $session->refresh(), $result->refresh()];
    }
}
