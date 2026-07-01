<?php

namespace Tests\Feature\Api;

use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use App\Models\IsoStandard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setupResult(): AssessmentResult
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        
        $session = AssessmentSession::create([
            'user_id' => $user->id,
            'name' => 'IT Department Audit',
            'status' => 'in_progress',
        ]);

        $standard = IsoStandard::create([
            'type' => 'clause',
            'level' => '1',
            'code' => '8.1',
            'title' => 'Operational planning and control',
        ]);

        return $standard->results()->create([
            'session_id' => $session->id,
            'status' => 'in_progress',
            'is_applicable' => true,
            'maturity_rating' => 2,
            'notes' => null,
            'risk_priority' => null,
            'impact_interpretation' => null,
        ]);
    }

    /**
     * Test webhook successfully updates all fields using the new structured keys
     */
    public function test_webhook_handles_new_n8n_keys(): void
    {
        $result = $this->setupResult();

        $payload = [
            'result_id' => $result->id,
            'strategic_recommendation' => 'Establish robust documented procedures for operational control.',
            'action_plan' => [
                'action' => "1. Document key processes.\n2. Assign responsibilities."
            ],
            'prioritization_level' => 'High',
            'impact_interpretation' => 'Reduces operational compliance gaps.',
            'evidence_validation' => 'No files uploaded yet.',
        ];

        $response = $this->postJson('/api/webhook/n8n/ai-response', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'AI recommendation updated successfully',
                 ]);

        $result->refresh();
        $this->assertEquals('Establish robust documented procedures for operational control.', $result->ai_recommendation);
        $this->assertEquals("1. Document key processes.\n2. Assign responsibilities.", $result->corrective_action_plan['action']);
        $this->assertEquals('High', $result->risk_priority);
        $this->assertEquals('Reduces operational compliance gaps.', $result->impact_interpretation);
        $this->assertEquals('No files uploaded yet.', $result->evidence_validation);
    }

    /**
     * Test webhook successfully updates all fields using alias keys (such as priority, impact, recommendation)
     */
    public function test_webhook_handles_alias_n8n_keys(): void
    {
        $result = $this->setupResult();

        $payload = [
            'id' => $result->id,
            'recommendation' => 'Alias recommendation content.',
            'action' => "Alias action steps.",
            'priority' => 'Medium',
            'impact' => 'Alias impact interpretation.',
            'insight' => 'Alias insight.',
        ];

        $response = $this->postJson('/api/webhook/n8n/ai-response', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'AI recommendation updated successfully',
                 ]);

        $result->refresh();
        $this->assertEquals('Alias recommendation content.', $result->ai_recommendation);
        $this->assertEquals('Alias action steps.', $result->corrective_action_plan['action']);
        $this->assertEquals('Medium', $result->risk_priority);
        $this->assertEquals('Alias impact interpretation.', $result->impact_interpretation);
        $this->assertEquals('Alias insight.', $result->evidence_validation);
    }

    /**
     * Test webhook validation fails when result_id or recommendation is missing
     */
    public function test_webhook_fails_with_missing_data(): void
    {
        $result = $this->setupResult();

        $payload = [
            'result_id' => $result->id,
            // recommendation is missing
        ];

        $response = $this->postJson('/api/webhook/n8n/ai-response', $payload);

        $response->assertStatus(500)
                 ->assertJson([
                     'success' => false,
                 ]);
    }
}
