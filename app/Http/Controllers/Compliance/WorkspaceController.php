<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compliance\UpdateWorkspaceEntryRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Compliance\WorkspaceService;
use App\Services\Intelligence\AnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class WorkspaceController extends Controller
{
    public function __construct(
        protected WorkspaceService $workspaceService,
        protected AnalyticsService $analyticsService
    ) {}

    public function index(Request $request): View
    {
        $sessionId = $request->get('session_id') ? (int) $request->get('session_id') : null;
        $activeTab = in_array($request->get('tab'), ['workspace', 'gap-report'], true)
            ? $request->get('tab')
            : 'workspace';

        $workspaceData = $this->workspaceService->getWorkspaceData(auth()->id(), $sessionId);
        $tacticalData  = $this->analyticsService->getTacticalData(auth()->id(), $sessionId);

        // Resolve the canonical selectedId from workspace (already resolved to first session if null)
        $resolvedId = $workspaceData['selectedSession']?->id ?? $sessionId;

        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);

        $selectedSession = $workspaceData['selectedSession'];
        $isSessionLead = false;
        if ($selectedSession) {
            $isSessionLead = auth()->user()->isAdmin() || 
                ($selectedSession->user_id === auth()->id()) || 
                $selectedSession->invitedUsers()
                    ->where('assessment_session_users.user_id', auth()->id())
                    ->where('assessment_session_users.role', 'lead')
                    ->exists();
        }

        return view('pages.workspace.index', array_merge($workspaceData, [
            'comparison'    => $tacticalData['comparison'],
            'findings'      => $tacticalData['findings'],
            'tacticalStats' => $tacticalData['stats'],
            'activeTab'     => $activeTab,
            'selectedId'    => $resolvedId,
            'users'         => $users,
            'isSessionLead' => $isSessionLead,
        ]));
    }

    public function remediate($resultId): View
    {
        $id = $resultId instanceof \App\Models\AssessmentResult ? $resultId->id : (int) $resultId;
        $result = \App\Models\AssessmentResult::with(['standard', 'session'])->findOrFail($id);
        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);

        return view('pages.workspace.remediate', compact('result', 'users'));
    }

    public function updateSingle(UpdateWorkspaceEntryRequest $request, $resultId)
    {
        $id = $resultId instanceof \App\Models\AssessmentResult ? $resultId->id : (int) $resultId;

        $result = $this->workspaceService->updateEntry(
            $id,
            auth()->id(),
            $request->validated()
        );

        $evidenceFiles = is_array($result->evidence_file) ? $result->evidence_file : (empty($result->evidence_file) ? [] : [$result->evidence_file]);
        $mappedFiles = [];
        foreach ($evidenceFiles as $file) {
            $mappedFiles[] = [
                'name' => basename($file),
                'url'  => route('results.evidence', [$result->id, 'file' => $file])
            ];
        }

        // If it's an AJAX/JSON request, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            return ApiResponse::success([
                'is_applicable'      => $result->is_applicable,
                'soa_justification'  => $result->soa_justification,
                'treatment_due_date' => optional($result->treatment_due_date)->toDateString(),
                'treatment_pic'      => $result->treatment_pic,
                'treatment_status'   => $result->treatment_status,
                'notes'              => $result->notes,
                'evidence_files'     => $mappedFiles,
            ], 'Record updated successfully.');
        }

        // For HTML form submissions, redirect back with success message
        return redirect()
            ->route('workspace.remediate', $result->id)
            ->with('success', __('Remediation plan updated successfully.'));
    }

    public function exportSoa($sessionId)
    {
        $session = $this->workspaceService->getSoaData($sessionId, auth()->id());

        if ($session->status !== 'completed') {
            return redirect()->back()->with('error', __('This report can only be downloaded after the assessment session status is marked as completed.'));
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SoaExport($sessionId),
            $this->exportFilename($session->name, 'xlsx')
        );
    }

    public function exportSoaPdf($sessionId)
    {
        $session = $this->workspaceService->getSoaData($sessionId, auth()->id());

        if ($session->status !== 'completed') {
            return redirect()->back()->with('error', __('This report can only be downloaded after the assessment session status is marked as completed.'));
        }

        $orderKey = fn($result) => sprintf(
            '%s|%s',
            $result->standard->parent?->code ?? '',
            $result->standard->code ?? ''
        );

        $clausaResults = $session->results
            ->filter(fn($r) => $this->isExportableResult($r) && $r->standard->type === 'clausa')
            ->sortBy($orderKey, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $annexResults = $session->results
            ->filter(fn($r) => $this->isExportableResult($r) && $r->standard->type === 'control')
            ->sortBy($orderKey, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        if ($clausaResults->isEmpty() && $annexResults->isEmpty()) {
            $annexResults = $session->results
                ->filter(fn($r) => $this->isExportableResult($r))
                ->sortBy($orderKey, SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        }

        $pdf = Pdf::loadView('pages.workspace.soa_pdf', [
            'session'       => $session,
            'clausaResults' => $clausaResults,
            'annexResults'  => $annexResults,
            'date'          => now()->format('d F Y'),
        ]);

        return $pdf->download($this->exportFilename($session->name, 'pdf'));
    }

    protected function isExportableResult($result): bool
    {
        return $result->standard
            && is_array($result->standard->questions)
            && count($result->standard->questions) > 0;
    }

    protected function exportFilename(string $sessionName, string $extension): string
    {
        $safeName = (string) Str::of($sessionName)
            ->replaceMatches('/[\\\\\/:*?"<>|]+/', '-')
            ->replaceMatches('/\s+/', '_')
            ->trim('._-')
            ->limit(80, '');

        return 'SoA_ISO27001_' . ($safeName !== '' ? $safeName : 'session') . ".{$extension}";
    }
}
