<?php

namespace Tests\Feature\Governance;

use App\Models\KnowledgeBase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KnowledgeBaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_knowledge_base_index_renders_real_database_resources(): void
    {
        $user = User::factory()->create();
        $resource = $this->createKnowledgeResource([
            'title' => 'Real Risk Register Template',
            'category' => 'templates',
            'description' => null,
            'format' => null,
            'user_id' => $user->id,
        ]);

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.index'))
            ->assertOk()
            ->assertSee('Knowledge Base')
            ->assertSee($resource->title)
            ->assertSee('knowledge-base\\\\\\/download\\\\\\/' . $resource->id, false);
    }

    public function test_custom_resource_can_be_created_updated_and_deleted_from_web(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.create'))
            ->assertOk()
            ->assertSee('Content Preview')
            ->assertSee('Live preview');

        $this
            ->actingAs($user)
            ->post(route('knowledge-base.store'), $this->validPayload([
                'title' => 'Password Policy Playbook',
            ]))
            ->assertRedirect(route('knowledge-base.index'));

        $resource = KnowledgeBase::where('title', 'Password Policy Playbook')->firstOrFail();
        $this->assertDatabaseHas('audit_trails', [
            'model_type' => KnowledgeBase::class,
            'model_id' => $resource->id,
            'action' => 'created',
        ]);

        $this
            ->actingAs($user)
            ->put(route('knowledge-base.update', $resource->id), $this->validPayload([
                'title' => 'Updated Password Policy Playbook',
            ]))
            ->assertRedirect(route('knowledge-base.index'));

        $this->assertDatabaseHas('knowledge_bases', [
            'id' => $resource->id,
            'title' => 'Updated Password Policy Playbook',
        ]);
        $this->assertDatabaseHas('audit_trails', [
            'model_type' => KnowledgeBase::class,
            'model_id' => $resource->id,
            'action' => 'updated',
        ]);

        $this
            ->actingAs($user)
            ->delete(route('knowledge-base.destroy', $resource->id))
            ->assertRedirect(route('knowledge-base.index'));

        $this->assertDatabaseMissing('knowledge_bases', ['id' => $resource->id]);
        $this->assertDatabaseHas('audit_trails', [
            'model_type' => KnowledgeBase::class,
            'model_id' => $resource->id,
            'action' => 'deleted',
        ]);
    }

    public function test_system_resources_cannot_be_modified_or_deleted_from_web(): void
    {
        $user = User::factory()->create();
        $resource = $this->createKnowledgeResource(['is_system' => true]);

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.edit', $resource->id))
            ->assertRedirect(route('knowledge-base.index'));

        $this
            ->actingAs($user)
            ->put(route('knowledge-base.update', $resource->id), $this->validPayload([
                'title' => 'Tampered Official Asset',
            ]))
            ->assertRedirect(route('knowledge-base.index'));

        $this
            ->actingAs($user)
            ->delete(route('knowledge-base.destroy', $resource->id))
            ->assertRedirect(route('knowledge-base.index'));

        $this->assertDatabaseHas('knowledge_bases', [
            'id' => $resource->id,
            'title' => $resource->title,
            'is_system' => true,
        ]);
    }

    public function test_custom_resource_can_store_replace_and_download_attachment(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.create'))
            ->assertOk()
            ->assertSee('Original Attachment');

        $this
            ->actingAs($user)
            ->post(route('knowledge-base.store'), array_merge($this->validPayload([
                'title' => 'Attachment Resource Guide',
                'format' => null,
                'size' => null,
            ]), [
                'attachment' => UploadedFile::fake()->create('policy-guide.pdf', 64, 'application/pdf'),
            ]))
            ->assertRedirect(route('knowledge-base.index'));

        $resource = KnowledgeBase::where('title', 'Attachment Resource Guide')->firstOrFail();

        $this->assertSame('policy-guide.pdf', $resource->attachment_name);
        $this->assertSame('PDF', $resource->format);
        $this->assertNotNull($resource->size);
        Storage::disk('local')->assertExists($resource->attachment_path);

        $oldPath = $resource->attachment_path;

        $this
            ->actingAs($user)
            ->put(route('knowledge-base.update', $resource->id), array_merge($this->validPayload([
                'title' => 'Attachment Resource Guide',
            ]), [
                'attachment' => UploadedFile::fake()->create('replacement-template.docx', 32, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            ]))
            ->assertRedirect(route('knowledge-base.index'));

        $resource->refresh();

        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($resource->attachment_path);
        $this->assertSame('replacement-template.docx', $resource->attachment_name);

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.attachment', $resource->id))
            ->assertOk()
            ->assertDownload('replacement-template.docx');

        $this->assertSame(1, $resource->refresh()->downloads_count);
    }

    public function test_knowledge_base_api_returns_real_resources_and_statistics(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->createKnowledgeResource(['is_system' => true]);
        $this->createKnowledgeResource(['is_system' => false, 'category' => 'evidence', 'user_id' => $user->id]);

        $this
            ->getJson('/api/knowledge-base')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.totalCount', 2)
            ->assertJsonPath('data.statistics.total_resources', 2)
            ->assertJsonPath('data.statistics.system_resources', 1)
            ->assertJsonPath('data.statistics.user_resources', 1)
            ->assertJsonPath('data.categories.0', 'guides');
    }

    public function test_knowledge_base_filters_resources_by_search_and_category(): void
    {
        $user = User::factory()->create();

        $this->createKnowledgeResource([
            'title' => 'Risk Register Template',
            'category' => 'templates',
            'content' => 'Risk register content with **markdown**.',
            'user_id' => $user->id,
        ]);
        $this->createKnowledgeResource([
            'title' => 'Incident Response SOP',
            'category' => 'sop',
            'content' => 'Incident response content.',
            'user_id' => $user->id,
        ]);

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.index', ['q' => 'Risk', 'category' => 'templates']))
            ->assertOk()
            ->assertViewHas('filteredCount', 1)
            ->assertViewHas('resources', fn($resources) => str_contains(
                (string) \Illuminate\Support\Str::markdown(e($resources->getCollection()->first()->content)),
                '<strong>markdown</strong>'
            ))
            ->assertSee('Risk Register Template')
            ->assertDontSee('Incident Response SOP');
    }

    public function test_knowledge_base_sorts_resources_by_title_and_download_count(): void
    {
        $user = User::factory()->create();

        $this->createKnowledgeResource([
            'title' => 'Zulu Guide',
            'downloads_count' => 10,
            'user_id' => $user->id,
        ]);
        $this->createKnowledgeResource([
            'title' => 'Alpha Guide',
            'downloads_count' => 1,
            'user_id' => $user->id,
        ]);
        $this->createKnowledgeResource([
            'title' => 'Middle Guide',
            'downloads_count' => 5,
            'user_id' => $user->id,
        ]);

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.index', ['sort' => 'title']))
            ->assertOk()
            ->assertViewHas('selectedSort', 'title')
            ->assertViewHas('resources', fn($resources) => $resources->getCollection()->pluck('title')->take(3)->all() === [
                'Alpha Guide',
                'Middle Guide',
                'Zulu Guide',
            ]);

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.index', ['sort' => 'most_downloaded']))
            ->assertOk()
            ->assertViewHas('selectedSort', 'most_downloaded')
            ->assertViewHas('resources', fn($resources) => $resources->getCollection()->pluck('title')->take(3)->all() === [
                'Zulu Guide',
                'Middle Guide',
                'Alpha Guide',
            ]);
    }

    public function test_knowledge_base_filters_resources_by_source(): void
    {
        $user = User::factory()->create();

        $official = $this->createKnowledgeResource([
            'title' => 'Official ISMS Guide',
            'is_system' => true,
        ]);
        $custom = $this->createKnowledgeResource([
            'title' => 'Custom Team Playbook',
            'is_system' => false,
            'user_id' => $user->id,
        ]);

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.index', ['source' => 'official']))
            ->assertOk()
            ->assertViewHas('selectedSource', 'official')
            ->assertSee($official->title)
            ->assertDontSee($custom->title);

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.index', ['source' => 'custom']))
            ->assertOk()
            ->assertViewHas('selectedSource', 'custom')
            ->assertSee($custom->title)
            ->assertDontSee($official->title);
    }

    public function test_knowledge_base_preview_renders_safe_markdown(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->postJson(route('knowledge-base.preview'), [
                'content' => 'Use **strong controls** <script>alert("xss")</script>',
            ])
            ->assertOk()
            ->assertJsonPath('html', fn(string $html) =>
                str_contains($html, '<strong>strong controls</strong>')
                && str_contains($html, '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;')
                && ! str_contains($html, '<script>alert("xss")</script>')
            );
    }

    public function test_knowledge_base_resources_can_be_exported_and_imported_from_json(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        // 1. Create a resource with an attachment file on disk
        $filename = 'knowledge-base/exported-test-file.xlsx';
        Storage::disk('local')->put($filename, 'fake-excel-content');

        $resource = $this->createKnowledgeResource([
            'title' => 'Exported Resource',
            'category' => 'templates',
            'attachment_path' => $filename,
            'attachment_name' => 'exported-test-file.xlsx',
            'attachment_mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'attachment_size' => strlen('fake-excel-content'),
            'user_id' => $user->id,
        ]);

        // 2. Export resources
        $exportResponse = $this
            ->actingAs($user)
            ->get(route('knowledge-base.export-json'))
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->assertSame('knowledge_base', $exportResponse->json('resource_type'));
        
        $exportedResources = collect($exportResponse->json('resources'));
        $this->assertContains($resource->title, $exportedResources->pluck('title')->all());

        // Assert that the attachment is base64 encoded in the export payload
        $exportedItem = $exportedResources->firstWhere('title', 'Exported Resource');
        $this->assertNotNull($exportedItem);
        $this->assertSame(base64_encode('fake-excel-content'), $exportedItem['attachment_base64']);
        $this->assertArrayNotHasKey('attachment_path', $exportedItem);

        // 3. Import payload containing an attachment
        $payload = [
            'resources' => [
                $this->validPayload([
                    'title' => 'Imported JSON Resource',
                    'category' => 'evidence',
                    'attachment_name' => 'imported-test-file.xlsx',
                    'attachment_mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'attachment_base64' => base64_encode('imported-excel-content'),
                ]),
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('knowledge-base.json', json_encode($payload));

        $this
            ->actingAs($user)
            ->post(route('knowledge-base.import-json'), [
                'json_file' => $file,
            ])
            ->assertRedirect(route('knowledge-base.index'));

        $imported = KnowledgeBase::where('title', 'Imported JSON Resource')->firstOrFail();

        $this->assertFalse($imported->is_system);
        $this->assertSame(0, $imported->downloads_count);
        $this->assertDatabaseHas('audit_trails', [
            'model_type' => KnowledgeBase::class,
            'model_id' => $imported->id,
            'action' => 'created',
        ]);

        // Assert that the imported attachment is saved to disk and attributes are correctly set
        $this->assertNotNull($imported->attachment_path);
        $this->assertTrue(Storage::disk('local')->exists($imported->attachment_path));
        $this->assertSame('imported-excel-content', Storage::disk('local')->get($imported->attachment_path));
        $this->assertSame('imported-test-file.xlsx', $imported->attachment_name);
        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $imported->attachment_mime);
        $this->assertSame(strlen('imported-excel-content'), (int)$imported->attachment_size);
    }

    public function test_knowledge_base_api_create_update_delete_and_system_protection(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $createResponse = $this
            ->postJson('/api/knowledge-base', $this->validPayload([
                'title' => 'API Resource Guide',
            ]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'API Resource Guide');

        $id = $createResponse->json('data.id');

        $this
            ->putJson("/api/knowledge-base/{$id}", $this->validPayload([
                'title' => 'Updated API Resource Guide',
            ]))
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated API Resource Guide');

        $this
            ->deleteJson("/api/knowledge-base/{$id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $system = $this->createKnowledgeResource(['is_system' => true]);

        $this
            ->putJson("/api/knowledge-base/{$system->id}", $this->validPayload([
                'title' => 'Tampered API Official Asset',
            ]))
            ->assertForbidden();

        $this
            ->deleteJson("/api/knowledge-base/{$system->id}")
            ->assertForbidden();

        $this
            ->postJson('/api/knowledge-base', $this->validPayload([
                'category' => 'invalid-category',
            ]))
            ->assertUnprocessable();

        $this
            ->getJson('/api/knowledge-base/999999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Resource not found');
    }

    public function test_download_metadata_and_pdf_html_are_safe(): void
    {
        $resource = $this->createKnowledgeResource([
            'title' => 'Unsafe <b>PDF</b> Resource',
            'content' => 'Safe content <script>alert("xss")</script>',
            'downloads_count' => 0,
        ]);

        $service = app(\App\Services\Governance\KnowledgeBaseService::class);
        $html = $service->generatePdfContent($resource);
        $service->recordDownload($resource);

        $this->assertStringContainsString('Unsafe &lt;b&gt;PDF&lt;/b&gt; Resource', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert("xss")</script>', $html);
        $this->assertSame('unsafe-pdf-resource', $service->safeDownloadName($resource));
        $this->assertSame(1, $resource->refresh()->downloads_count);
    }

    public function test_knowledge_base_web_download_returns_pdf_and_records_download(): void
    {
        $user = User::factory()->create();
        $resource = $this->createKnowledgeResource([
            'title' => 'Incident Response Playbook',
            'downloads_count' => 0,
            'is_system' => true,
        ]);

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.download', $resource->id))
            ->assertOk()
            ->assertDownload('incident-response-playbook.pdf');

        $this->assertSame(1, $resource->refresh()->downloads_count);
    }

    public function test_knowledge_base_web_download_fails_for_custom_resource(): void
    {
        $user = User::factory()->create();
        $resource = $this->createKnowledgeResource([
            'title' => 'Custom User Guide',
            'downloads_count' => 0,
            'is_system' => false,
            'user_id' => $user->id,
        ]);

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.download', $resource->id))
            ->assertRedirect(route('knowledge-base.index'))
            ->assertSessionHas('error', 'PDF download is only available for official system resources.');
    }

    public function test_knowledge_base_api_download_returns_pdf_and_records_download(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $resource = $this->createKnowledgeResource([
            'title' => 'API Download Playbook',
            'downloads_count' => 0,
            'is_system' => true,
        ]);

        $this
            ->getJson("/api/knowledge-base/{$resource->id}/download")
            ->assertOk()
            ->assertDownload('api-download-playbook.pdf');

        $this->assertSame(1, $resource->refresh()->downloads_count);
    }

    public function test_knowledge_base_api_download_fails_for_custom_resource(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $resource = $this->createKnowledgeResource([
            'title' => 'API Custom Playbook',
            'downloads_count' => 0,
            'is_system' => false,
            'user_id' => $user->id,
        ]);

        $this
            ->getJson("/api/knowledge-base/{$resource->id}/download")
            ->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'PDF download is only available for official system resources.');
    }

    private function createKnowledgeResource(array $overrides = []): KnowledgeBase
    {
        return KnowledgeBase::create(array_merge([
            'title' => 'ISO Knowledge Resource',
            'category' => 'guides',
            'description' => 'A real database backed knowledge resource.',
            'content' => 'This content is long enough for validation.',
            'format' => 'PDF',
            'size' => '25KB',
            'icon' => 'fa-book-open',
            'is_system' => false,
            'downloads_count' => 0,
        ], $overrides));
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Valid Knowledge Resource',
            'category' => 'guides',
            'description' => 'Useful ISO 27001 implementation material.',
            'content' => 'This resource contains actionable ISO 27001 guidance.',
            'format' => 'PDF',
            'size' => '25KB',
            'icon' => 'fa-book-open',
        ], $overrides);
    }

    public function test_optional_content_and_sentence_truncation_logic(): void
    {
        $user = User::factory()->create();

        $payload = $this->validPayload([
            'title' => 'Optional Content Playbook',
            'content' => '',
            'description' => 'First sentence. Second sentence! Third sentence? Fourth sentence.',
        ]);

        $this
            ->actingAs($user)
            ->post(route('knowledge-base.store'), $payload)
            ->assertRedirect(route('knowledge-base.index'));

        $resource = KnowledgeBase::where('title', 'Optional Content Playbook')->firstOrFail();
        $this->assertEquals('', $resource->content);

        $response = $this
            ->actingAs($user)
            ->get(route('knowledge-base.index'))
            ->assertOk();

        $response->assertSee('First sentence.');
        $response->assertDontSee('Second sentence!');

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.show', $resource->id))
            ->assertOk()
            ->assertSee('Attachment-Only Document')
            ->assertSee('This asset does not contain any online article text.');
    }

    public function test_admin_user_can_create_and_update_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create with content
        $payload = $this->validPayload([
            'title' => 'Admin Created Resource',
            'content' => 'This content is created by admin and is long enough.',
        ]);

        $this
            ->actingAs($admin)
            ->post(route('knowledge-base.store'), $payload)
            ->assertRedirect(route('knowledge-base.index'));

        $resource = KnowledgeBase::where('title', 'Admin Created Resource')->firstOrFail();
        $this->assertEquals('This content is created by admin and is long enough.', $resource->content);

        // Update content
        $updatePayload = $this->validPayload([
            'title' => 'Admin Updated Resource',
            'content' => 'This is updated content by admin and is long enough.',
        ]);

        $this
            ->actingAs($admin)
            ->put(route('knowledge-base.update', $resource->id), $updatePayload)
            ->assertRedirect(route('knowledge-base.index'));

        $this->assertEquals('This is updated content by admin and is long enough.', $resource->refresh()->content);
    }

    public function test_standard_user_cannot_create_content(): void
    {
        $user = User::factory()->create(); // standard user

        // Attempt to create with content
        $payload = $this->validPayload([
            'title' => 'Standard User Resource',
            'content' => 'Attempting to inject this content as standard user.',
        ]);

        $this
            ->actingAs($user)
            ->post(route('knowledge-base.store'), $payload)
            ->assertRedirect(route('knowledge-base.index'));

        $resource = KnowledgeBase::where('title', 'Standard User Resource')->firstOrFail();
        // Should be forced to empty string
        $this->assertEquals('', $resource->content);
    }

    public function test_standard_user_cannot_update_existing_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(); // standard user

        // Pre-create a resource with content (by admin)
        $resource = $this->createKnowledgeResource([
            'title' => 'Important Policy document',
            'content' => 'Pre-existing high value policy content.',
            'user_id' => $user->id,
        ]);

        // Standard user attempts to update the resource's title and attempts to modify content
        $payload = $this->validPayload([
            'title' => 'Updated Policy Title by User',
            'content' => 'Maliciously changed content.',
        ]);

        $this
            ->actingAs($user)
            ->put(route('knowledge-base.update', $resource->id), $payload)
            ->assertRedirect(route('knowledge-base.index'));

        $resource->refresh();
        $this->assertEquals('Updated Policy Title by User', $resource->title);
        // Pre-existing content MUST be preserved and not modified!
        $this->assertEquals('Pre-existing high value policy content.', $resource->content);
    }

    public function test_attachment_can_be_viewed_inline_or_downloaded_explicitly(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $resource = $this->createKnowledgeResource([
            'title' => 'Inline PDF Guide',
            'format' => null,
            'size' => null,
            'downloads_count' => 0,
            'user_id' => $user->id,
        ]);

        // Store PDF attachment
        $file = UploadedFile::fake()->create('guide.pdf', 64, 'application/pdf');
        $this
            ->actingAs($user)
            ->put(route('knowledge-base.update', $resource->id), array_merge($this->validPayload(), [
                'attachment' => $file,
            ]))
            ->assertRedirect(route('knowledge-base.index'));

        $resource->refresh();
        $this->assertEquals(0, $resource->downloads_count);

        // 1. By default, PDF attachments should return inline response for preview
        $inlineResponse = $this
            ->actingAs($user)
            ->get(route('knowledge-base.attachment', $resource->id))
            ->assertOk();

        $this->assertEquals(
            'inline; filename="guide.pdf"',
            $inlineResponse->headers->get('Content-Disposition')
        );

        // Preview should NOT increment download count
        $this->assertEquals(0, $resource->refresh()->downloads_count);

        // 2. If explicitly requested with ?download=1, it should return forced download response
        $this
            ->actingAs($user)
            ->get(route('knowledge-base.attachment', $resource->id) . '?download=1')
            ->assertOk()
            ->assertDownload('guide.pdf');

        // Explicit download SHOULD increment download count
        $this->assertEquals(1, $resource->refresh()->downloads_count);
    }

    public function test_standard_user_cannot_access_other_users_custom_resource(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $resource = $this->createKnowledgeResource([
            'title' => 'User One Private Guide',
            'is_system' => false,
            'user_id' => $user1->id,
        ]);

        // User 2 tries to view User 1's custom resource
        $this
            ->actingAs($user2)
            ->get(route('knowledge-base.show', $resource->id))
            ->assertRedirect(route('knowledge-base.index'))
            ->assertSessionHas('error', 'Resource not found.');

        // User 2 tries to edit User 1's custom resource
        $this
            ->actingAs($user2)
            ->get(route('knowledge-base.edit', $resource->id))
            ->assertRedirect(route('knowledge-base.index'))
            ->assertSessionHas('error', 'Resource not found.');

        // User 2 tries to delete User 1's custom resource
        $this
            ->actingAs($user2)
            ->delete(route('knowledge-base.destroy', $resource->id))
            ->assertRedirect(route('knowledge-base.index'))
            ->assertSessionHas('error', 'Resource not found.');
    }
}
