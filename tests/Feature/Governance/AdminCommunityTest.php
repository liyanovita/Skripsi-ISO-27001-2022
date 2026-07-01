<?php

namespace Tests\Feature\Governance;

use App\Models\CommunityTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommunityTest extends TestCase
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

    protected function makeTemplate(User $user, array $overrides = []): CommunityTemplate
    {
        return CommunityTemplate::create(array_merge([
            'user_id'         => $user->id,
            'title'           => 'Sample Audit Template',
            'description'     => 'A test template for ISO 27001 compliance.',
            'author_name'     => $user->name,
            'base_score'      => 3.5,
            'downloads_count' => 5,
            'upvotes'         => 2,
            'rating_count'    => 1,
            'avg_rating'      => 4.0,
            'tags'            => ['security', 'iso27001'],
            'content_data'    => ['session' => [], 'results' => []],
        ], $overrides));
    }

    public function test_admin_can_view_community_index_with_stats(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();
        $this->makeTemplate($user, ['title' => 'My Security Template', 'downloads_count' => 10, 'upvotes' => 3]);

        $this->actingAs($admin)
            ->get(route('admin.community.index'))
            ->assertOk()
            ->assertSee('Community Moderation')
            ->assertSee('Total Templates')
            ->assertSee('Total Downloads')
            ->assertSee('Total Upvotes')
            ->assertSee('My Security Template')
            ->assertSee($user->name);
    }

    public function test_non_admin_cannot_access_community_admin(): void
    {
        $user = $this->regularUser();

        $this->actingAs($user)
            ->get(route('admin.community.index'))
            ->assertRedirect();
    }

    public function test_admin_can_search_templates(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();

        $this->makeTemplate($user, ['title' => 'Alpha Template Unique']);
        $this->makeTemplate($user, ['title' => 'Beta Template Different']);

        $this->actingAs($admin)
            ->get(route('admin.community.index', ['search' => 'Alpha']))
            ->assertOk()
            ->assertSee('Alpha Template Unique')
            ->assertDontSee('Beta Template Different');
    }

    public function test_admin_can_delete_template(): void
    {
        $admin    = $this->adminUser();
        $user     = $this->regularUser();
        $template = $this->makeTemplate($user, ['title' => 'Template To Delete']);

        $this->actingAs($admin)
            ->delete(route('admin.community.destroy', $template))
            ->assertRedirect();

        $this->assertDatabaseMissing('community_templates', ['id' => $template->id]);
    }

    public function test_stats_reflect_aggregate_counts(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();

        $this->makeTemplate($user, ['downloads_count' => 20, 'upvotes' => 5]);
        $this->makeTemplate($user, ['downloads_count' => 10, 'upvotes' => 3]);

        $response = $this->actingAs($admin)
            ->get(route('admin.community.index'));

        $response->assertOk()
            ->assertSee('30')  // total downloads
            ->assertSee('8');  // total upvotes
    }

    public function test_admin_can_sort_templates_by_downloads(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();
        $this->makeTemplate($user, ['title' => 'Low Downloads', 'downloads_count' => 1]);
        $this->makeTemplate($user, ['title' => 'High Downloads', 'downloads_count' => 999]);

        $response = $this->actingAs($admin)
            ->get(route('admin.community.index', ['sort' => 'downloads']));

        $response->assertOk()->assertSee('High Downloads');
    }

    public function test_preview_link_is_present_in_listing(): void
    {
        $admin    = $this->adminUser();
        $user     = $this->regularUser();
        $template = $this->makeTemplate($user, ['title' => 'Previewable Template']);

        $this->actingAs($admin)
            ->get(route('admin.community.index'))
            ->assertOk()
            ->assertSee(route('community.show', $template->id));
    }
}
