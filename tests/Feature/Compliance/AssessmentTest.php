<?php

namespace Tests\Feature\Compliance;

use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use App\Models\IsoStandard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_page_renders_successfully(): void
    {
        [$user, $session, $result] = $this->createAssessmentFixture();

        $response = $this
            ->actingAs($user)
            ->get(route('results.edit', $session->id));

        $response->assertOk();
        $response->assertSee($session->name);
        $response->assertSee($result->standard->title);
    }

    public function test_can_toggle_is_applicable_and_save_justification_directly_on_assessment_result(): void
    {
        [$user, $session, $result] = $this->createAssessmentFixture();

        $response = $this
            ->actingAs($user)
            ->postJson(route('results.update', $result->id), [
                'is_applicable' => false,
                'soa_justification' => 'Not applicable due to business context',
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $result->refresh();
        $this->assertFalse($result->is_applicable);
        $this->assertSame('Not applicable due to business context', $result->soa_justification);
        $this->assertSame('completed', $result->status);
        $this->assertNull($result->maturity_rating);
    }

    public function test_finalize_session_only_requires_applicable_controls_to_be_scored(): void
    {
        [$user, $session, $result] = $this->createAssessmentFixture();

        // Let's mark the only control as not applicable
        $result->update([
            'is_applicable' => false,
            'soa_justification' => 'Excluded',
            'maturity_rating' => null,
            'status' => 'completed',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('sessions.finalize', $session->id));

        $response->assertRedirect();
        $this->assertSame('completed', $session->refresh()->status);
    }

    private function createAssessmentFixture(): array
    {
        $user = User::factory()->create();

        $session = AssessmentSession::create([
            'user_id' => $user->id,
            'name' => 'Internal Audit 2026',
            'status' => 'in_progress',
            'overall_maturity_score' => 0,
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
            'answers' => [],
            'maturity_rating' => null,
            'status' => 'pending',
            'is_applicable' => true,
            'treatment_status' => 'open',
        ]);

        return [$user, $session->refresh(), $result->refresh()];
    }
}
