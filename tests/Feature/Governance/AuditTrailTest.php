<?php

namespace Tests\Feature\Governance;

use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use App\Models\AuditTrail;
use App\Models\IsoStandard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_trail_index_renders_correctly(): void
    {
        $user = User::factory()->create();
        
        $session = AssessmentSession::create([
            'user_id' => $user->id,
            'name' => 'Audit 2026',
            'status' => 'in_progress',
            'overall_maturity_score' => 2.0
        ]);

        $standard = IsoStandard::create([
            'type' => 'control',
            'level' => 'requirement',
            'code' => 'A.5.1',
            'title' => 'Security Policies',
            'questions' => ['Are policies reviewed?']
        ]);

        $result = AssessmentResult::create([
            'session_id' => $session->id,
            'iso_standard_id' => $standard->id,
            'maturity_rating' => 2,
            'status' => 'in_progress',
        ]);

        AuditTrail::create([
            'user_id' => $user->id,
            'model_type' => AssessmentResult::class,
            'model_id' => $result->id,
            'action' => 'updated',
            'field_changed' => 'maturity_rating',
            'old_value' => '1',
            'new_value' => '2',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('audit-trail.index'));

        $response->assertOk()
            ->assertSee('Audit Trail')
            ->assertSee('Maturity Rating')
            ->assertSee('A.5.1')
            ->assertSee('2');
    }

    public function test_audit_trail_search_filtering(): void
    {
        $user = User::factory()->create();
        
        AuditTrail::create([
            'user_id' => $user->id,
            'model_type' => AssessmentResult::class,
            'model_id' => 1,
            'action' => 'updated',
            'field_changed' => 'status',
            'old_value' => 'in_progress',
            'new_value' => 'completed_xyz',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('audit-trail.index', ['search' => 'completed_xyz']));

        $response->assertOk()
            ->assertSee('completed_xyz');

        $responseEmpty = $this
            ->actingAs($user)
            ->get(route('audit-trail.index', ['search' => 'nonexistentvalue123']));
            
        $responseEmpty->assertOk()
            ->assertDontSee('completed_xyz')
            ->assertSee('No changes found in the audit trail');
    }

    public function test_audit_trail_csv_export(): void
    {
        $user = User::factory()->create();
        
        $session = AssessmentSession::create([
            'user_id' => $user->id,
            'name' => 'Audit Export',
            'status' => 'in_progress',
            'overall_maturity_score' => 2.0
        ]);

        $standard = IsoStandard::create([
            'type' => 'control',
            'level' => 'requirement',
            'code' => 'A.5.1',
            'title' => 'Security Policies',
            'questions' => ['Are policies reviewed?']
        ]);

        $result = AssessmentResult::create([
            'session_id' => $session->id,
            'iso_standard_id' => $standard->id,
            'maturity_rating' => 2,
            'status' => 'in_progress',
        ]);

        AuditTrail::create([
            'user_id' => $user->id,
            'model_type' => AssessmentResult::class,
            'model_id' => $result->id,
            'action' => 'updated',
            'field_changed' => 'maturity_rating',
            'old_value' => '1',
            'new_value' => 'Export_Test_Value',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('audit-trail.export'));

        $response->assertOk();
        $response->assertHeader('Content-type', 'text/csv; charset=UTF-8');
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('Export_Test_Value', $content);
        $this->assertStringContainsString('A.5.1', $content);
        $this->assertStringContainsString('maturity_rating', $content);
    }
}
