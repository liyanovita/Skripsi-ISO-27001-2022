<?php

namespace Tests\Feature\Governance;

use App\Models\KnowledgeBase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminKnowledgeBaseTest extends TestCase
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

    protected function createKb(array $attrs = []): KnowledgeBase
    {
        return KnowledgeBase::create(array_merge([
            'title'       => 'ISO 27001 Audit Guide',
            'category'    => 'Audit Guides',
            'description' => 'A comprehensive guide.',
            'content'     => '# Introduction\nThis is the guide.',
            'format'      => 'article',
            'size'        => '0 MB',
            'is_system'   => true,
            'downloads_count' => 0,
        ], $attrs));
    }

    // ─── Index ────────────────────────────────────────────────────────────────

    public function test_admin_can_view_knowledge_base_index(): void
    {
        $admin = $this->adminUser();
        $kb    = $this->createKb(['title' => 'Security Policy Template', 'is_system' => true]);

        $this->actingAs($admin)
            ->get(route('admin.knowledge.index'))
            ->assertOk()
            ->assertSee('Knowledge Base')
            ->assertSee('Security Policy Template')
            ->assertSee('System'); // is_system badge
    }

    public function test_non_admin_cannot_access_admin_knowledge_index(): void
    {
        $user = $this->regularUser();

        $this->actingAs($user)
            ->get(route('admin.knowledge.index'))
            ->assertRedirect();
    }

    public function test_admin_index_shows_stats_counts(): void
    {
        $admin = $this->adminUser();
        $this->createKb(['is_system' => true]);

        $this->actingAs($admin)
            ->get(route('admin.knowledge.index'))
            ->assertOk()
            ->assertSee('Total Articles')
            ->assertSee('Total Downloads');
    }

    public function test_admin_index_does_not_show_user_created_custom_resources(): void
    {
        $admin = $this->adminUser();
        $this->createKb(['title' => 'System Guide', 'is_system' => true]);
        $this->createKb(['title' => 'Custom Article', 'is_system' => false]);

        $response = $this->actingAs($admin)
            ->get(route('admin.knowledge.index'));

        $response->assertOk()
            ->assertSee('System Guide')
            ->assertDontSee('Custom Article');
    }

    public function test_admin_index_can_filter_by_category(): void
    {
        $admin = $this->adminUser();
        $this->createKb(['title' => 'Document Alpha', 'category' => 'Guides']);
        $this->createKb(['title' => 'Document Beta', 'category' => 'Policies']);

        $response = $this->actingAs($admin)
            ->get(route('admin.knowledge.index', ['category' => 'Policies']));

        $response->assertOk()
            ->assertSee('Document Beta')
            ->assertDontSee('Document Alpha');
    }

    public function test_admin_index_can_search(): void
    {
        $admin = $this->adminUser();
        $this->createKb(['title' => 'Risk Assessment Template', 'description' => 'Risk focused']);
        $this->createKb(['title' => 'Audit Checklist', 'description' => 'Audit focused']);

        $response = $this->actingAs($admin)
            ->get(route('admin.knowledge.index', ['search' => 'Risk']));

        $response->assertOk()
            ->assertSee('Risk Assessment Template')
            ->assertDontSee('Audit Checklist');
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function test_admin_can_view_create_form(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.knowledge.create'))
            ->assertOk()
            ->assertSee('Add New Document or Article')
            ->assertSee('Word-like editor');
    }

    public function test_admin_can_create_knowledge_base_article(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.knowledge.store'), [
                'title'       => 'New Security Policy',
                'category'    => 'Policies',
                'description' => 'Short description',
                'content'     => '## Overview\nContent here.',
            ])
            ->assertRedirect(route('admin.knowledge.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('knowledge_bases', [
            'title'     => 'New Security Policy',
            'is_system' => true, // admin-created articles are always system
        ]);
    }

    public function test_admin_can_create_knowledge_base_with_attachment(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser();

        $file = UploadedFile::fake()->create('policy.pdf', 512, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('admin.knowledge.store'), [
                'title'       => 'Policy Document',
                'category'    => 'Policies',
                'description' => 'A policy',
                'content'     => 'Policy content',
                'attachment'  => $file,
            ])
            ->assertRedirect(route('admin.knowledge.index'));

        $kb = KnowledgeBase::where('title', 'Policy Document')->firstOrFail();
        $this->assertNotNull($kb->attachment_path);
        $this->assertEquals('policy.pdf', $kb->attachment_name);
        Storage::disk('public')->assertExists($kb->attachment_path);
    }

    // ─── Edit / Update ────────────────────────────────────────────────────────

    public function test_admin_can_view_edit_form(): void
    {
        $admin = $this->adminUser();
        $kb    = $this->createKb();

        $this->actingAs($admin)
            ->get(route('admin.knowledge.edit', $kb))
            ->assertOk()
            ->assertSee('Edit Document or Article')
            ->assertSee($kb->title)
            ->assertSee('Word-like editor');
    }

    public function test_admin_can_update_knowledge_base(): void
    {
        $admin = $this->adminUser();
        $kb    = $this->createKb(['title' => 'Old Title']);

        $this->actingAs($admin)
            ->put(route('admin.knowledge.update', $kb), [
                'title'       => 'Updated Title',
                'category'    => 'Templates',
                'description' => 'Updated desc',
                'content'     => 'Updated content',
            ])
            ->assertRedirect(route('admin.knowledge.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('knowledge_bases', [
            'id'    => $kb->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_admin_can_replace_attachment_on_update(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser();

        $oldFile = UploadedFile::fake()->create('old.pdf', 100, 'application/pdf');
        $kb      = $this->createKb([
            'attachment_path' => 'knowledge-base/old.pdf',
            'attachment_name' => 'old.pdf',
            'format'          => 'pdf',
            'size'            => '0.1 MB',
        ]);

        Storage::disk('public')->put('knowledge-base/old.pdf', 'old content');

        $newFile = UploadedFile::fake()->create('new.pdf', 200, 'application/pdf');

        $this->actingAs($admin)
            ->put(route('admin.knowledge.update', $kb), [
                'title'       => $kb->title,
                'category'    => $kb->category,
                'description' => $kb->description,
                'content'     => $kb->content,
                'attachment'  => $newFile,
            ])
            ->assertRedirect(route('admin.knowledge.index'));

        Storage::disk('public')->assertMissing('knowledge-base/old.pdf');
        $kb->refresh();
        $this->assertEquals('new.pdf', $kb->attachment_name);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function test_admin_can_delete_knowledge_base_article(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser();
        $kb    = $this->createKb();

        $this->actingAs($admin)
            ->delete(route('admin.knowledge.destroy', $kb))
            ->assertRedirect(route('admin.knowledge.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('knowledge_bases', ['id' => $kb->id]);
    }

    public function test_admin_delete_removes_attachment_file(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser();

        Storage::disk('public')->put('knowledge-base/file.pdf', 'content');
        $kb = $this->createKb([
            'attachment_path' => 'knowledge-base/file.pdf',
            'attachment_name' => 'file.pdf',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.knowledge.destroy', $kb));

        Storage::disk('public')->assertMissing('knowledge-base/file.pdf');
    }

    public function test_admin_cannot_access_or_modify_custom_user_documents(): void
    {
        $admin = $this->adminUser();
        $customKb = $this->createKb(['title' => 'User Custom Doc', 'is_system' => false]);

        // Edit form
        $this->actingAs($admin)
            ->get(route('admin.knowledge.edit', $customKb))
            ->assertNotFound();

        // Update
        $this->actingAs($admin)
            ->put(route('admin.knowledge.update', $customKb), [
                'title'       => 'Hacked Title',
                'category'    => 'Guides',
                'description' => 'Hacked',
                'content'     => 'Hacked content',
            ])
            ->assertNotFound();

        // Delete
        $this->actingAs($admin)
            ->delete(route('admin.knowledge.destroy', $customKb))
            ->assertNotFound();
    }

    public function test_admin_can_view_knowledge_base_item_detail(): void
    {
        $admin = $this->adminUser();
        $kb = $this->createKb(['title' => 'Specific Official Audit SOP', 'is_system' => true]);

        $this->actingAs($admin)
            ->get(route('admin.knowledge.show', $kb))
            ->assertOk()
            ->assertSee('Specific Official Audit SOP')
            ->assertSee('Back')
            ->assertSee('Edit Article');
    }

    public function test_admin_cannot_view_user_custom_document_detail(): void
    {
        $admin = $this->adminUser();
        $customKb = $this->createKb(['title' => 'User Custom Doc', 'is_system' => false]);

        $this->actingAs($admin)
            ->get(route('admin.knowledge.show', $customKb))
            ->assertNotFound();
    }
}
