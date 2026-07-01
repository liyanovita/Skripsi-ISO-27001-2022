<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\CreateTemplateRequest;
use App\Http\Requests\Community\RateTemplateRequest;
use App\Http\Traits\ResponseFormatter;
use App\Services\Community\TemplateService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TemplateController extends Controller
{
    use ResponseFormatter;

    public function __construct(
        protected TemplateService $templateService
    ) {}

    public function index(Request $request): View
    {
        $data = $this->templateService->getTemplatesData($request->get('search'));
        $data['search'] = $request->get('search');
        return view('pages.community.index', $data);
    }

    public function create(): View
    {
        return view('pages.community.form');
    }

    public function store(CreateTemplateRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $data['attachment_path'] = $file->store('community', 'local');
                $data['attachment_name'] = $file->getClientOriginalName();
                $data['attachment_mime'] = $file->getClientMimeType();
                $data['attachment_size'] = $file->getSize();
                $data['format'] = strtoupper($file->getClientOriginalExtension());
                $data['size'] = number_format($file->getSize() / 1048576, 2) . ' MB';
            }

            if ($request->hasFile('json_file')) {
                $jsonFile = $request->file('json_file');
                $fileContent = json_decode(file_get_contents($jsonFile->getRealPath()), true);
                if ($fileContent) {
                    $data['content_data'] = $fileContent;
                    $data['base_score'] = $fileContent['session']['overall_maturity_score'] ?? ($fileContent['overall_maturity_score'] ?? 0);
                }
            }

            // Convert tags from comma separated to array if needed
            if (!empty($data['tags'])) {
                $data['tags'] = array_map('trim', explode(',', $data['tags']));
            } else {
                $data['tags'] = [];
            }

            $this->templateService->create($data, auth()->id());

            return $this->successRedirect('community.index', 'Asset successfully shared with the community!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to submit asset: ' . $e->getMessage());
        }
    }

    public function edit($id): View|RedirectResponse
    {
        try {
            $template = \App\Models\CommunityTemplate::findOrFail($id);
            if ($template->user_id !== auth()->id() && !auth()->user()->is_admin) {
                return $this->errorRedirect('You do not have permission to edit this asset.');
            }
            return view('pages.community.form', compact('template'));
        } catch (\Exception $e) {
            return $this->errorRedirect('Asset not found.');
        }
    }

    public function update(CreateTemplateRequest $request, $id): RedirectResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $data['attachment_path'] = $file->store('community', 'local');
                $data['attachment_name'] = $file->getClientOriginalName();
                $data['attachment_mime'] = $file->getClientMimeType();
                $data['attachment_size'] = $file->getSize();
                $data['format'] = strtoupper($file->getClientOriginalExtension());
                $data['size'] = number_format($file->getSize() / 1048576, 2) . ' MB';
            }

            if (!empty($data['tags']) && is_string($data['tags'])) {
                $data['tags'] = array_map('trim', explode(',', $data['tags']));
            } elseif (empty($data['tags'])) {
                $data['tags'] = [];
            }

            $this->templateService->update($id, $data, auth()->id());

            return $this->successRedirect('community.index', 'Asset updated successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update asset: ' . $e->getMessage());
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $this->templateService->delete($id, auth()->id());
            return $this->successRedirect('community.index', 'Asset deleted successfully!');
        } catch (\Exception $e) {
            return $this->errorRedirect('Failed to delete asset: ' . $e->getMessage());
        }
    }

    public function useTemplate(Request $request): RedirectResponse
    {
        try {
            $session = $this->templateService->useTemplate(
                $request->template_id,
                auth()->id()
            );

            return $this->successRedirect('sessions.show', 'Community template successfully activated!', ['id' => $session->id]);
        } catch (\Exception $e) {
            return $this->errorRedirect('Failed to use template: ' . $e->getMessage());
        }
    }

    public function clone(Request $request, $id): RedirectResponse
    {
        try {
            $session = $this->templateService->cloneTemplate($id, auth()->id());

            return $this->successRedirect('sessions.show', 'Template successfully cloned! You can now adapt it.', ['id' => $session->id]);
        } catch (\Exception $e) {
            return $this->errorRedirect('Failed to clone template: ' . $e->getMessage());
        }
    }

    public function upvote($id): RedirectResponse
    {
        try {
            if (session()->has("upvoted_template_{$id}")) {
                return $this->errorRedirect('You have already upvoted this template.');
            }

            $this->templateService->upvote($id);
            session()->put("upvoted_template_{$id}", true);
            
            return $this->successRedirect('community.index', 'Template upvoted successfully!');
        } catch (\Exception $e) {
            return $this->errorRedirect('Failed to upvote template: ' . $e->getMessage());
        }
    }

    public function rate(RateTemplateRequest $request, $id): RedirectResponse
    {
        try {
            if (session()->has("rated_template_{$id}")) {
                return $this->errorRedirect('You have already rated this template.');
            }

            $this->templateService->rate($id, $request->stars);
            session()->put("rated_template_{$id}", true);

            return $this->successRedirect('community.index', 'Rating submitted! Thank you.');
        } catch (\Exception $e) {
            return $this->errorRedirect('Failed to submit rating: ' . $e->getMessage());
        }
    }

    public function downloadAttachment(Request $request, $id)
    {
        try {
            $template = \App\Models\CommunityTemplate::findOrFail($id);
            
            if (!$this->templateService->hasAttachment($template)) {
                return $this->errorRedirect('Attachment file not found.');
            }

            $mime = $template->attachment_mime;
            $inlineTypes = [
                'application/pdf', 'image/jpeg', 'image/png', 'image/gif', 
                'image/webp', 'image/svg+xml', 'text/plain', 'text/markdown',
            ];
            
            $isInline = in_array($mime, $inlineTypes) && !$request->has('download');

            if ($isInline) {
                return response()->file(
                    \Illuminate\Support\Facades\Storage::disk('local')->path($template->attachment_path),
                    [
                        'Content-Type' => $mime,
                        'Content-Disposition' => 'inline; filename="' . $this->templateService->attachmentDownloadName($template) . '"'
                    ]
                );
            }

            $this->templateService->recordDownload($template);

            return response()->download(
                \Illuminate\Support\Facades\Storage::disk('local')->path($template->attachment_path),
                $this->templateService->attachmentDownloadName($template)
            );
        } catch (\Exception $e) {
            return $this->errorRedirect('Failed to download attachment: ' . $e->getMessage());
        }
    }

    public function show($id): View|RedirectResponse
    {
        try {
            $data = $this->templateService->getTemplateWithResults($id);
            $resource = $data['template'];
            $results = $data['results'];
            $contentHtml = (string) \Illuminate\Support\Str::markdown(e($resource->content ?? $resource->description));
            return view('pages.community.show', compact('resource', 'contentHtml', 'results'));
        } catch (\Exception $e) {
            return $this->errorRedirect('Asset not found.');
        }
    }
}
