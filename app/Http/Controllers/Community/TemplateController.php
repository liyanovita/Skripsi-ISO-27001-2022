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

    public function store(CreateTemplateRequest $request): RedirectResponse
    {
        try {
            $fileContent = json_decode(
                file_get_contents($request->file('json_file')->getRealPath()),
                true
            );

            if (!$fileContent) {
                return $this->errorRedirect('Invalid JSON file format.');
            }

            $this->templateService->createTemplate([
                'title'        => $request->title,
                'description'  => $request->description,
                'author_name'  => auth()->user()->name ?? 'Anonymous Auditor',
                'tags'         => $request->tags ? explode(',', $request->tags) : [],
                'base_score'   => $fileContent['session']['overall_maturity_score'] ?? 0,
                'content_data' => $fileContent,
            ], auth()->id());

            return $this->successRedirect('community.index', 'Your best practice has been successfully shared with the community!');
        } catch (\Exception $e) {
            return $this->errorRedirect('Failed to create template: ' . $e->getMessage());
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

    public function show($id): View
    {
        $data = $this->templateService->getTemplateWithResults($id);
        return view('pages.community.preview', $data);
    }
}
