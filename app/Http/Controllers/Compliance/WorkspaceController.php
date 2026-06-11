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

        return view('pages.workspace.index', array_merge($workspaceData, [
            'comparison'    => $tacticalData['comparison'],
            'findings'      => $tacticalData['findings'],
            'tacticalStats' => $tacticalData['stats'],
            'activeTab'     => $activeTab,
            'selectedId'    => $resolvedId,
        ]));
    }

    public function updateSingle(UpdateWorkspaceEntryRequest $request, $resultId): JsonResponse
    {
        $result = $this->workspaceService->updateEntry(
            $resultId,
            auth()->id(),
            $request->validated()
        );

        return ApiResponse::success([
            'is_applicable'      => $result->is_applicable,
            'soa_justification'  => $result->soa_justification,
            'treatment_due_date' => optional($result->treatment_due_date)->toDateString(),
            'treatment_pic'      => $result->treatment_pic,
            'treatment_status'   => $result->treatment_status,
        ], 'Record updated successfully.');
    }

    public function exportSoa($sessionId)
    {
        $session = $this->workspaceService->getSoaData($sessionId, auth()->id());

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SoaExport($sessionId),
            $this->exportFilename($session->name, 'xlsx')
        );
    }

    public function exportSoaPdf($sessionId)
    {
        $session = $this->workspaceService->getSoaData($sessionId, auth()->id());

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
