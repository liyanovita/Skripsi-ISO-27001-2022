<?php

namespace App\Http\Controllers\Governance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Governance\CreateKnowledgeBaseRequest;
use App\Http\Requests\Governance\UpdateKnowledgeBaseRequest;
use App\Services\Governance\KnowledgeBaseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

class KnowledgeBaseController extends Controller
{
    public function __construct(
        protected KnowledgeBaseService $knowledgeBaseService
    ) {}

    public function index(Request $request): View
    {
        $data = $this->knowledgeBaseService->getAll($request->only(['q', 'category', 'sort', 'source']));
        return view('pages.kb.index', $data);
    }

    public function preview(Request $request): JsonResponse
    {
        $content = (string) $request->input('content', '');

        return response()->json([
            'html' => (string) Str::markdown(e($content !== '' ? $content : __('Start typing to preview this resource...'))),
        ]);
    }

    public function exportJson(): JsonResponse
    {
        $payload = $this->knowledgeBaseService->exportResources();
        $filename = 'knowledge-base-export-' . now()->format('Ymd-His') . '.json';

        return response()->json($payload, Response::HTTP_OK, [
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function importJson(Request $request): RedirectResponse
    {
        $request->validate([
            'json_file' => ['required', 'file', 'mimes:json,txt', 'max:2048'],
        ]);

        try {
            $payload = json_decode(file_get_contents($request->file('json_file')->getRealPath()), true);

            if (! is_array($payload)) {
                throw new InvalidArgumentException('Invalid JSON file format.');
            }

            $summary = $this->knowledgeBaseService->importResources($payload);

            return redirect()->route('knowledge-base.index')
                ->with('success', "Import completed: {$summary['imported']} imported, {$summary['skipped']} skipped.");
        } catch (\Throwable $e) {
            return redirect()->route('knowledge-base.index')
                ->with('error', 'Failed to import knowledge base resources: ' . $e->getMessage());
        }
    }

    public function create(): View
    {
        return view('pages.kb.form');
    }

    public function store(CreateKnowledgeBaseRequest $request): RedirectResponse
    {
        try {
            $this->knowledgeBaseService->create($request->validated());

            return redirect()->route('knowledge-base.index')
                ->with('success', 'Resource added successfully.');
        } catch (InvalidArgumentException $e) {
            return redirect()->route('knowledge-base.create')
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->route('knowledge-base.create')
                ->withInput()
                ->with('error', 'Failed to create resource.');
        }
    }

    public function edit($id): View|RedirectResponse
    {
        try {
            $resource = $this->knowledgeBaseService->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('knowledge-base.index')
                ->with('error', 'Resource not found.');
        }

        if ($resource->is_system) {
            return redirect()->route('knowledge-base.index')
                ->with('error', 'Official system assets cannot be modified.');
        }

        return view('pages.kb.form', compact('resource'));
    }

    public function update(UpdateKnowledgeBaseRequest $request, $id): RedirectResponse
    {
        try {
            $resource = $this->knowledgeBaseService->findOrFail($id);

            if ($resource->is_system) {
                return redirect()->route('knowledge-base.index')
                    ->with('error', 'Official system assets cannot be modified.');
            }

            $this->knowledgeBaseService->update($id, $request->validated());
            return redirect()->route('knowledge-base.index')
                ->with('success', 'Resource updated successfully.');
        } catch (InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (ModelNotFoundException $e) {
            return redirect()->route('knowledge-base.index')
                ->with('error', 'Resource not found.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update resource.');
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $this->knowledgeBaseService->delete($id);
            return redirect()->route('knowledge-base.index')
                ->with('success', 'Resource deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('knowledge-base.index')
                ->with('error', 'Resource not found.');
        } catch (InvalidArgumentException $e) {
            return redirect()->route('knowledge-base.index')
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->route('knowledge-base.index')
                ->with('error', 'Failed to delete resource.');
        }
    }

    public function download($id)
    {
        try {
            $item = $this->knowledgeBaseService->findOrFail($id);
            $item = $this->knowledgeBaseService->recordDownload($item);
            $html = $this->knowledgeBaseService->generatePdfContent($item);
            $pdf = Pdf::loadHTML($html);
            $safeTitle = $this->knowledgeBaseService->safeDownloadName($item);

            return $pdf->download($safeTitle . '.pdf');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('knowledge-base.index')
                ->with('error', 'Resource not found.');
        } catch (\Throwable $e) {
            return redirect()->route('knowledge-base.index')
                ->with('error', 'Failed to download resource.');
        }
    }

    public function downloadAttachment($id)
    {
        try {
            $item = $this->knowledgeBaseService->findOrFail($id);

            if (! $this->knowledgeBaseService->hasAttachment($item)) {
                return redirect()->route('knowledge-base.index')
                    ->with('error', 'Attachment file not found.');
            }

            $this->knowledgeBaseService->recordDownload($item);

            return response()->download(
                Storage::disk('local')->path($item->attachment_path),
                $this->knowledgeBaseService->attachmentDownloadName($item)
            );
        } catch (ModelNotFoundException $e) {
            return redirect()->route('knowledge-base.index')
                ->with('error', 'Resource not found.');
        } catch (\Throwable $e) {
            return redirect()->route('knowledge-base.index')
                ->with('error', 'Failed to download attachment.');
        }
    }
}
