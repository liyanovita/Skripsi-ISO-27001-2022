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

    public function test_updating_result_with_trigger_ai_validates_data_changes(): void
    {
        [$user, $session, $result] = $this->createAssessmentFixture();

        // 1. Initial trigger (no AI recommendation yet) should succeed
        $response = $this
            ->actingAs($user)
            ->postJson(route('results.update', $result->id), [
                'maturity_rating' => 3,
                'is_applicable' => true,
                'answers' => ['q0' => 3],
                'notes' => 'Test notes',
                'trigger_ai' => '1',
            ]);

        $response->assertOk();

        // Mock completed AI recommendation in DB
        $result->refresh();
        $result->update([
            'ai_recommendation' => 'This is a mock recommendation',
        ]);

        // 2. Triggering AI again with the exact same data should fail with 409 Conflict (no changes)
        $response = $this
            ->actingAs($user)
            ->postJson(route('results.update', $result->id), [
                'maturity_rating' => 3,
                'is_applicable' => true,
                'answers' => ['q0' => 3],
                'notes' => 'Test notes',
                'trigger_ai' => '1',
            ]);

        $response->assertStatus(409);
        $response->assertJsonPath('no_change', true);
        $response->assertJsonPath('message', __('No data has changed'));

        // 3. Changing the notes should allow AI regeneration to be triggered successfully
        $response = $this
            ->actingAs($user)
            ->postJson(route('results.update', $result->id), [
                'maturity_rating' => 3,
                'is_applicable' => true,
                'answers' => ['q0' => 3],
                'notes' => 'Test notes have changed',
                'trigger_ai' => '1',
            ]);

        $response->assertOk();
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
