<?php

namespace App\Services\Governance;

use App\Models\AuditTrail;
use App\Models\KnowledgeBase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class KnowledgeBaseService
{
    public const CATEGORIES = ['guides', 'templates', 'sop', 'evidence'];
    public const SORT_OPTIONS = ['latest', 'title', 'most_downloaded'];
    public const SOURCE_OPTIONS = ['all', 'official', 'custom'];

    public function getAll(array $filters = [], int $perPage = 12): array
    {
        $categories = self::CATEGORIES;
        $categoryTotals = KnowledgeBase::query()
            ->selectRaw('category, count(*) as aggregate')
            ->whereIn('category', $categories)
            ->groupBy('category')
            ->pluck('aggregate', 'category');

        $categoryCounts = collect($categories)
            ->mapWithKeys(fn(string $category) => [
                $category => (int) ($categoryTotals[$category] ?? 0),
            ]);

        $totalCount = KnowledgeBase::count();
        $statistics     = [
            'total_resources' => $totalCount,
            'system_resources' => KnowledgeBase::system()->count(),
            'user_resources' => KnowledgeBase::custom()->count(),
        ];

        $query = KnowledgeBase::query()->latest();
        $search = trim((string) ($filters['q'] ?? ''));
        $selectedCategory = (string) ($filters['category'] ?? 'all');
        $selectedSort = (string) ($filters['sort'] ?? 'latest');
        $selectedSource = (string) ($filters['source'] ?? 'all');

        if ($selectedCategory !== 'all' && ! in_array($selectedCategory, $categories, true)) {
            $selectedCategory = 'all';
        }

        if (! in_array($selectedSort, self::SORT_OPTIONS, true)) {
            $selectedSort = 'latest';
        }

        if (! in_array($selectedSource, self::SOURCE_OPTIONS, true)) {
            $selectedSource = 'all';
        }

        if ($selectedCategory !== 'all') {
            $query->where('category', $selectedCategory);
        }

        if ($selectedSource === 'official') {
            $query->where('is_system', true);
        } elseif ($selectedSource === 'custom') {
            $query->where('is_system', false);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        match ($selectedSort) {
            'title' => $query->reorder('title'),
            'most_downloaded' => $query->reorderDesc('downloads_count')->orderByDesc('updated_at'),
            default => $query,
        };

        $resources = $query->paginate($perPage)->withQueryString();
        $filteredCount = $resources->total();

        return compact('resources', 'categoryCounts', 'totalCount', 'categories', 'statistics', 'filteredCount', 'search', 'selectedCategory', 'selectedSort', 'selectedSource');
    }

    public function create(array $data): KnowledgeBase
    {
        $attachment = $data['attachment'] ?? null;
        unset($data['attachment']);

        // Validate required fields
        if (empty($data['title']) || empty($data['category']) || empty($data['content'])) {
            throw new InvalidArgumentException('Title, category, and content are required.');
        }

        if (! in_array($data['category'], self::CATEGORIES, true)) {
            throw new InvalidArgumentException('Category must be one of: guides, templates, sop, evidence.');
        }

        if (strlen($data['title']) < 5 || strlen($data['title']) > 255) {
            throw new InvalidArgumentException('Title must be between 5 and 255 characters.');
        }

        if (strlen($data['content']) < 10) {
            throw new InvalidArgumentException('Content must be at least 10 characters.');
        }

        $resource = KnowledgeBase::create($data);
        $this->storeAttachment($resource, $attachment);
        $resource->refresh();

        $this->recordAudit($resource, 'created', null, null, $resource->only($resource->getFillable()));

        return $resource;
    }

    public function update(int $id, array $data): KnowledgeBase
    {
        $resource = KnowledgeBase::findOrFail($id);
        $attachment = $data['attachment'] ?? null;
        unset($data['attachment']);

        if ($resource->is_system) {
            throw new InvalidArgumentException('Official system assets cannot be modified.');
        }

        if (isset($data['title'])) {
            if (strlen($data['title']) < 5 || strlen($data['title']) > 255) {
                throw new InvalidArgumentException('Title must be between 5 and 255 characters.');
            }
        }

        if (isset($data['category']) && ! in_array($data['category'], self::CATEGORIES, true)) {
            throw new InvalidArgumentException('Category must be one of: guides, templates, sop, evidence.');
        }

        if (isset($data['content'])) {
            if (strlen($data['content']) < 10) {
                throw new InvalidArgumentException('Content must be at least 10 characters.');
            }
        }

        $auditKeys = array_unique(array_merge(array_keys($data), [
            'attachment_path',
            'attachment_name',
            'attachment_mime',
            'attachment_size',
            'format',
            'size',
        ]));

        $before = $resource->only($auditKeys);
        $resource->update($data);
        $this->storeAttachment($resource, $attachment, deleteExisting: true);

        $after = $resource->refresh()->only($auditKeys);

        if ($before !== $after) {
            $this->recordAudit($resource, 'updated', null, $before, $after);
        }

        return $resource;
    }

    public function delete(int $id): bool
    {
        $resource = KnowledgeBase::findOrFail($id);

        if ($resource->is_system) {
            throw new InvalidArgumentException('Official system assets cannot be deleted.');
        }

        $snapshot = $resource->only($resource->getFillable());
        $deleted = $resource->delete();

        if ($deleted) {
            $this->deleteAttachment($resource);
            $this->recordAudit($resource, 'deleted', null, $snapshot, null);
        }

        return $deleted;
    }

    public function findOrFail(int $id): KnowledgeBase
    {
        return KnowledgeBase::findOrFail($id);
    }

    public function recordDownload(KnowledgeBase $item): KnowledgeBase
    {
        $item->increment('downloads_count');

        return $item->refresh();
    }

    public function generatePdfContent(KnowledgeBase $item): string
    {
        return view('pages.kb.pdf', [
            'item' => $item,
            'generatedDate' => now()->format('d M Y'),
        ])->render();
    }

    public function safeDownloadName(KnowledgeBase $item): string
    {
        return Str::slug(strip_tags($item->title)) ?: 'knowledge-base-resource';
    }

    public function hasAttachment(KnowledgeBase $item): bool
    {
        return filled($item->attachment_path) && Storage::disk('local')->exists($item->attachment_path);
    }

    public function attachmentDownloadName(KnowledgeBase $item): string
    {
        if (filled($item->attachment_name)) {
            return $item->attachment_name;
        }

        $extension = pathinfo((string) $item->attachment_path, PATHINFO_EXTENSION);
        $baseName = $this->safeDownloadName($item);

        return $extension !== '' ? "{$baseName}.{$extension}" : $baseName;
    }

    public function exportResources(): array
    {
        return [
            'exported_at' => now()->toISOString(),
            'resource_type' => 'knowledge_base',
            'version' => 1,
            'resources' => KnowledgeBase::query()
                ->orderBy('title')
                ->get([
                    'title',
                    'category',
                    'description',
                    'content',
                    'format',
                    'size',
                    'icon',
                    'is_system',
                    'attachment_name',
                    'attachment_mime',
                    'attachment_size',
                ])
                ->map(fn(KnowledgeBase $resource) => $resource->toArray())
                ->all(),
        ];
    }

    public function importResources(array $payload): array
    {
        $items = $payload['resources'] ?? $payload;

        if (! is_array($items)) {
            throw new InvalidArgumentException('Invalid knowledge base import format.');
        }

        $imported = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                $skipped++;
                continue;
            }

            $data = [
                'title' => trim((string) ($item['title'] ?? '')),
                'category' => (string) ($item['category'] ?? ''),
                'description' => $item['description'] ?? null,
                'content' => (string) ($item['content'] ?? ''),
                'format' => $item['format'] ?? 'PDF',
                'size' => $item['size'] ?? null,
                'icon' => $item['icon'] ?? 'fa-book-open',
                'is_system' => false,
                'downloads_count' => 0,
            ];

            if (KnowledgeBase::where('title', $data['title'])->where('category', $data['category'])->exists()) {
                $skipped++;
                continue;
            }

            try {
                $this->create($data);
                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
            }
        }

        return compact('imported', 'skipped');
    }

    protected function recordAudit(KnowledgeBase $resource, string $action, ?string $field, mixed $oldValue, mixed $newValue): void
    {
        if (! auth()->check()) {
            return;
        }

        AuditTrail::create([
            'user_id' => auth()->id(),
            'model_type' => KnowledgeBase::class,
            'model_id' => $resource->id,
            'action' => $action,
            'field_changed' => $field,
            'old_value' => $oldValue === null ? null : json_encode($oldValue),
            'new_value' => $newValue === null ? null : json_encode($newValue),
        ]);
    }

    protected function storeAttachment(KnowledgeBase $resource, mixed $attachment, bool $deleteExisting = false): void
    {
        if (! $attachment instanceof UploadedFile) {
            return;
        }

        if ($deleteExisting) {
            $this->deleteAttachment($resource);
        }

        $path = $attachment->store('knowledge-base', 'local');
        $extension = strtoupper($attachment->getClientOriginalExtension() ?: pathinfo($path, PATHINFO_EXTENSION));
        $size = $attachment->getSize();

        $resource->forceFill([
            'attachment_path' => $path,
            'attachment_name' => $attachment->getClientOriginalName(),
            'attachment_mime' => $attachment->getMimeType(),
            'attachment_size' => $size,
            'format' => $resource->format ?: $extension,
            'size' => $resource->size ?: $this->formatBytes($size),
        ])->save();
    }

    protected function deleteAttachment(KnowledgeBase $resource): void
    {
        if (filled($resource->attachment_path)) {
            Storage::disk('local')->delete($resource->attachment_path);
        }
    }

    protected function formatBytes(?int $bytes): ?string
    {
        if ($bytes === null) {
            return null;
        }

        if ($bytes < 1024) {
            return "{$bytes} B";
        }

        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / 1048576, 1) . ' MB';
    }
}
