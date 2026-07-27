<?php

namespace Tests\Feature\Compliance;

use App\Models\User;
use App\Models\Organization;
use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\IsoStandard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_dashboard_with_kpi_metrics()
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $org = Organization::create([
            'name' => 'KPK Corp',
            'code' => 'KPK',
        ]);
        $user->update(['organization_id' => $org->id]);

        $session = AssessmentSession::create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'name' => 'Initial Audit ISO 27001:2022',
            'status' => 'completed',
            'overall_maturity_score' => 2.4,
            'deadline' => now()->addMonth()->toDateString(),
        ]);

        $standard = IsoStandard::create([
            'type' => 'control',
            'level' => 'requirement',
            'code' => 'A.5.1',
            'title' => 'Policies for information security',
            'description' => 'Policies for information security shall be defined...',
            'questions' => ['Is there a policy?'],
        ]);

        AssessmentResult::create([
            'session_id' => $session->id,
            'iso_standard_id' => $standard->id,
            'maturity_rating' => 2,
            'status' => 'completed',
            'answers' => json_encode(['verified' => true]),
            'notes' => 'Some notes',
            'risk_priority' => 'High',
            'is_applicable' => true,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('complianceScore');
        $response->assertViewHas('averageMaturity');
        $response->assertViewHas('riskPriority');
        $response->assertViewHas('aiRecStatus');

        $response->assertSee('Compliance Score');
        $response->assertSee('Overall Maturity Score');
        $response->assertSee('Risk Priority');
    }
}
