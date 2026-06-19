<?php

namespace Tests\Feature\Community;

use App\Models\CommunityTemplate;
use App\Models\IsoStandard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_hub_renders_templates_list(): void
    {
        $user = User::factory()->create();
        $template = CommunityTemplate::create([
            'user_id' => $user->id,
            'title' => 'ISO 27001 Fintech Framework',
            'description' => 'A comprehensive compliance roadmap for high-risk fintech platforms.',
            'author_name' => 'SecOps Master',
            'tags' => ['fintech', 'security'],
            'base_score' => 3.5,
            'content_data' => [
                'session' => [
                    'overall_maturity_score' => 3.5,
                    'results' => []
                ]
            ]
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('community.index'));

        $response
            ->assertOk()
            ->assertSee('Community Hub')
            ->assertSee('ISO 27001 Fintech Framework')
            ->assertSee('SecOps Master');
    }

    public function test_template_can_be_created_via_json_upload(): void
    {
        $user = User::factory()->create();
        $payload = [
            'session' => [
                'overall_maturity_score' => 2.4,
                'results' => [
                    [
                        'iso_standard_id' => 1,
                        'maturity_rating' => 2,
                    ]
                ]
            ]
        ];

        $file = UploadedFile::fake()->createWithContent('framework.json', json_encode($payload));

        $response = $this
            ->actingAs($user)
            ->post(route('community.store'), [
                'title' => 'Fintech Startup Template',
                'description' => 'Lightweight security assessment structure.',
                'tags' => 'fintech,startup',
                'json_file' => $file,
            ]);

        $response->assertRedirect(route('community.index'));

        $this->assertDatabaseHas('community_templates', [
            'title' => 'Fintech Startup Template',
            'user_id' => $user->id,
            'base_score' => 2.4
        ]);
    }

    public function test_user_can_use_template_to_create_session(): void
    {
        $user = User::factory()->create();
        
        $standard = IsoStandard::create([
            'type' => 'control',
            'level' => 'requirement',
            'code' => 'A.5.1',
            'title' => 'Security Policies',
            'questions' => ['Are policies reviewed?']
        ]);

        $template = CommunityTemplate::create([
            'user_id' => $user->id,
            'title' => 'Standard Blueprint',
            'description' => 'Basic blueprint.',
            'author_name' => 'Expert',
            'tags' => ['basic'],
            'base_score' => 4.0,
            'content_data' => [
                'session' => [
                    'overall_maturity_score' => 4.0,
                    'results' => [
                        [
                            'iso_standard_id' => $standard->id,
                            'maturity_rating' => 4,
                            'answers' => ['yes'],
                            'ai_recommendation' => 'Well done'
                        ]
                    ]
                ]
            ]
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('community.use'), [
                'template_id' => $template->id,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('assessment_sessions', [
            'user_id' => $user->id,
            'status' => 'draft',
            'overall_maturity_score' => 4.0
        ]);

        $this->assertSame(1, $template->refresh()->downloads_count);
    }

    public function test_user_can_clone_template_to_create_session(): void
    {
        $user = User::factory()->create();
        
        $standard = IsoStandard::create([
            'type' => 'control',
            'level' => 'requirement',
            'code' => 'A.5.1',
            'title' => 'Security Policies',
            'questions' => ['Are policies reviewed?']
        ]);

        $template = CommunityTemplate::create([
            'user_id' => $user->id,
            'title' => 'Standard Blueprint',
            'description' => 'Basic blueprint.',
            'author_name' => 'Expert',
            'tags' => ['basic'],
            'base_score' => 3.0,
            'content_data' => [
                'session' => [
                    'overall_maturity_score' => 3.0,
                    'results' => [
                        [
                            'iso_standard_id' => $standard->id,
                            'maturity_rating' => 3,
                            'answers' => ['yes'],
                            'notes' => 'Some notes'
                        ]
                    ]
                ]
            ]
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('community.clone', $template->id));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('assessment_sessions', [
            'user_id' => $user->id,
            'status' => 'draft',
            'overall_maturity_score' => 3.0
        ]);

        $this->assertSame(1, $template->refresh()->downloads_count);
    }

    public function test_user_can_upvote_and_rate_template(): void
    {
        $user = User::factory()->create();
        $template = CommunityTemplate::create([
            'user_id' => $user->id,
            'title' => 'Standard Blueprint',
            'description' => 'Basic blueprint.',
            'author_name' => 'Expert',
            'tags' => ['basic'],
            'base_score' => 3.0,
            'content_data' => [
                'session' => [
                    'overall_maturity_score' => 3.0,
                    'results' => []
                ]
            ]
        ]);

        // Upvote
        $this
            ->actingAs($user)
            ->post(route('community.upvote', $template->id))
            ->assertRedirect(route('community.index'));

        $this->assertSame(1, $template->refresh()->upvotes);

        // Rate
        $this
            ->actingAs($user)
            ->post(route('community.rate', $template->id), [
                'stars' => 5,
            ])
            ->assertRedirect(route('community.index'));

        $template->refresh();
        $this->assertSame(5, $template->rating_sum);
        $this->assertSame(1, $template->rating_count);
        $this->assertSame(5.0, $template->avg_rating);
    }

    public function test_template_preview_page_renders(): void
    {
        $user = User::factory()->create();
        $standard = IsoStandard::create([
            'type' => 'control',
            'level' => 'requirement',
            'code' => 'A.5.1',
            'title' => 'Security Policies',
            'questions' => ['Are policies reviewed?']
        ]);

        $template = CommunityTemplate::create([
            'user_id' => $user->id,
            'title' => 'Fintech Standard',
            'description' => 'Comprehensive previewable template.',
            'author_name' => 'Fintech Auditor',
            'tags' => ['preview'],
            'base_score' => 4.2,
            'content_data' => [
                'session' => [
                    'overall_maturity_score' => 4.2,
                    'results' => [
                        [
                            'iso_standard_id' => $standard->id,
                            'maturity_rating' => 4,
                            'notes' => 'Fintech notes'
                        ]
                    ]
                ]
            ]
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('community.preview', $template->id));

        $response
            ->assertOk()
            ->assertSee('Template Preview')
            ->assertSee('Fintech Standard')
            ->assertSee('A.5.1')
            ->assertSee('Security Policies')
            ->assertSee('Fintech notes');
    }
}
