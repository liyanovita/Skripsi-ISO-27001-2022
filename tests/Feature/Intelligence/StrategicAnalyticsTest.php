<?php

namespace Tests\Feature\Intelligence;

use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use App\Models\IsoStandard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StrategicAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_strategic_analytics_renders_empty_state_without_sessions(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->get(route('reports.strategic'))
            ->assertOk()
            ->assertSee('No Strategic Data Yet')
            ->assertSee('Create Session');
    }

    public function test_strategic_analytics_excludes_not_applicable_controls_from_active_metrics(): void
    {
        [$user, $session] = $this->createStrategicFixture();

        $response = $this
            ->actingAs($user)
            ->get(route('reports.strategic', ['session_id' => $session->id]));

        $response->assertOk();

        $stats = $response->viewData('stats');
        $breakdown = $response->viewData('complianceBreakdown');
        $distribution = $response->viewData('maturityDistribution');

        $this->assertSame(1, $stats['total_gaps']);
        $this->assertSame(0, $stats['critical']);
        $this->assertSame(1, $stats['compliant']);
        $this->assertSame(1, $stats['partial']);
        $this->assertSame(0, $stats['non_compliant']);
        $this->assertSame(1, $stats['needs_improvement']);
        $this->assertSame(0, $stats['unassessed']);
        $this->assertSame(1, $stats['excluded']);
        $this->assertSame(2, $stats['total_controls']);

        $this->assertSame(1, $breakdown['excluded']);
        $this->assertSame([0, 1, 0, 1, 0], $distribution);
        $this->assertSame(3.0, $response->viewData('comparison')['latest_score']);
        $this->assertSame(3.0, $response->viewData('maturityTrends')->first()['overall_maturity_score']);
    }

    public function test_ai_summary_markdown_does_not_render_raw_html(): void
    {
        [$user, $session] = $this->createStrategicFixture([
            'ai_summary' => '**Safe Summary** <script>alert("xss")</script>',
        ]);

        $this
            ->actingAs($user)
            ->get(route('reports.strategic', ['session_id' => $session->id]))
            ->assertOk()
            ->assertSee('<strong>Safe Summary</strong>', false)
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_strategic_analytics_defaults_selected_id_to_latest_session_and_renders_kpi_cards(): void
    {
        [$user, $session] = $this->createStrategicFixture();

        $this
            ->actingAs($user)
            ->get(route('reports.strategic'))
            ->assertOk()
            ->assertViewHas('selectedId', $session->id)
            ->assertSee('Strategic Analytics')
            ->assertSee('Compliant')
            ->assertSee('Partial')
            ->assertSee('Non-Compliant')
            ->assertSee('Unassessed')
            ->assertSee(route('reports.export-pdf', $session->id), false)
            ->assertSee(route('reports.export-excel', $session->id), false);
    }

    public function test_strategic_domain_scores_include_governance_clauses_and_match_annex_prefix_exactly(): void
    {
        $user = User::factory()->create();

        $session = AssessmentSession::create([
            'user_id' => $user->id,
            'name' => 'Domain Boundary Audit',
            'status' => 'in_progress',
            'overall_maturity_score' => 0.00,
        ]);

        $clause = IsoStandard::create([
            'type' => 'clause',
            'level' => 'sub_clause',
            'code' => '4.1',
            'title' => 'Context clause',
            'questions' => ['Is context defined?'],
        ]);

        $clausa = IsoStandard::create([
            'type' => 'clausa',
            'level' => 'requirement',
            'code' => '5.1',
            'title' => 'Leadership clausa',
            'questions' => ['Is leadership demonstrated?'],
        ]);

        $a50 = IsoStandard::create([
            'type' => 'control',
            'level' => 'requirement',
            'code' => 'A.50.1',
            'title' => 'Boundary control',
            'questions' => ['Should not be treated as A.5?'],
        ]);

        AssessmentResult::create([
            'session_id' => $session->id,
            'iso_standard_id' => $clause->id,
            'maturity_rating' => 4,
            'status' => 'completed',
            'is_applicable' => true,
        ]);

        AssessmentResult::create([
            'session_id' => $session->id,
            'iso_standard_id' => $clausa->id,
            'maturity_rating' => 2,
            'status' => 'completed',
            'is_applicable' => true,
        ]);

        AssessmentResult::create([
            'session_id' => $session->id,
            'iso_standard_id' => $a50->id,
            'maturity_rating' => 1,
            'status' => 'completed',
            'is_applicable' => true,
        ]);

        $domains = $this
            ->actingAs($user)
            ->get(route('reports.strategic', ['session_id' => $session->id]))
            ->assertOk()
            ->viewData('comparison')['domains'];

        $this->assertSame(0.0, collect($domains)->firstWhere('label', 'Policies')['latest']);
        $this->assertSame(3.0, collect($domains)->firstWhere('label', 'Governance')['latest']);
    }

    public function test_strategic_analytics_api_returns_aligned_metrics(): void
    {
        [$user, $session] = $this->createStrategicFixture();
        Sanctum::actingAs($user);

        $this
            ->getJson('/api/intelligence/analytics/strategic?session_id=' . $session->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.selectedId', $session->id)
            ->assertJsonPath('data.stats.total_gaps', 1)
            ->assertJsonPath('data.stats.non_compliant', 0)
            ->assertJsonPath('data.stats.needs_improvement', 1)
            ->assertJsonPath('data.stats.excluded', 1)
            ->assertJsonPath('data.complianceBreakdown.excluded', 1);
    }

    public function test_tactical_analytics_uses_consistent_non_compliant_semantics(): void
    {
        [$user, $session] = $this->createStrategicFixture();

        $stats = app(\App\Services\Intelligence\AnalyticsService::class)
            ->getTacticalData($user->id, $session->id)['stats'];

        $this->assertSame(1, $stats['total_gaps']);
        $this->assertSame(1, $stats['needs_improvement']);
        $this->assertSame(1, $stats['partial']);
        $this->assertSame(0, $stats['non_compliant']);
        $this->assertSame(1, $stats['excluded']);
    }

    private function createStrategicFixture(array $sessionOverrides = []): array
    {
        $user = User::factory()->create();

        $session = AssessmentSession::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Strategic Audit 2026',
            'status' => 'in_progress',
            'overall_maturity_score' => 0.00,
        ], $sessionOverrides));

        $applicableGap = IsoStandard::create([
            'type' => 'control',
            'level' => 'requirement',
            'code' => 'A.5.1',
            'title' => 'Applicable gap',
            'questions' => ['Is the control implemented?'],
        ]);

        $applicableCompliant = IsoStandard::create([
            'type' => 'control',
            'level' => 'requirement',
            'code' => 'A.5.2',
            'title' => 'Applicable compliant control',
            'questions' => ['Is the control optimized?'],
        ]);

        $excludedCritical = IsoStandard::create([
            'type' => 'control',
            'level' => 'requirement',
            'code' => 'A.5.3',
            'title' => 'Excluded critical control',
            'questions' => ['Is this control applicable?'],
        ]);

        AssessmentResult::create([
            'session_id' => $session->id,
            'iso_standard_id' => $applicableGap->id,
            'maturity_rating' => 2,
            'status' => 'completed',
            'is_applicable' => true,
        ]);

        AssessmentResult::create([
            'session_id' => $session->id,
            'iso_standard_id' => $applicableCompliant->id,
            'maturity_rating' => 4,
            'status' => 'completed',
            'is_applicable' => true,
        ]);

        AssessmentResult::create([
            'session_id' => $session->id,
            'iso_standard_id' => $excludedCritical->id,
            'maturity_rating' => 1,
            'status' => 'completed',
            'is_applicable' => false,
        ]);

        return [$user, $session->refresh()];
    }
}
