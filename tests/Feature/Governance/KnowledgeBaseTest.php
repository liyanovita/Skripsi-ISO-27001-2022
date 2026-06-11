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
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.create'))
            ->assertOk()
            ->assertSee('Content Preview')
            ->assertSee('Markdown-safe preview');

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
        $this->createKnowledgeResource(['is_system' => false, 'category' => 'evidence']);

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
        ]);
        $this->createKnowledgeResource([
            'title' => 'Incident Response SOP',
            'category' => 'sop',
            'content' => 'Incident response content.',
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
        ]);
        $this->createKnowledgeResource([
            'title' => 'Alpha Guide',
            'downloads_count' => 1,
        ]);
        $this->createKnowledgeResource([
            'title' => 'Middle Guide',
            'downloads_count' => 5,
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
        $user = User::factory()->create();
        $resource = $this->createKnowledgeResource([
            'title' => 'Exported Resource',
            'category' => 'templates',
        ]);

        $exportResponse = $this
            ->actingAs($user)
            ->get(route('knowledge-base.export-json'))
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->assertSame('knowledge_base', $exportResponse->json('resource_type'));
        $this->assertContains($resource->title, collect($exportResponse->json('resources'))->pluck('title')->all());

        $payload = [
            'resources' => [
                $this->validPayload([
                    'title' => 'Imported JSON Resource',
                    'category' => 'evidence',
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
        ]);

        $this
            ->actingAs($user)
            ->get(route('knowledge-base.download', $resource->id))
            ->assertOk()
            ->assertDownload('incident-response-playbook.pdf');

        $this->assertSame(1, $resource->refresh()->downloads_count);
    }

    public function test_knowledge_base_api_download_returns_pdf_and_records_download(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $resource = $this->createKnowledgeResource([
            'title' => 'API Download Playbook',
            'downloads_count' => 0,
        ]);

        $this
            ->getJson("/api/knowledge-base/{$resource->id}/download")
            ->assertOk()
            ->assertDownload('api-download-playbook.pdf');

        $this->assertSame(1, $resource->refresh()->downloads_count);
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
}
