<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assessment\CreateSessionRequest;
use App\Http\Requests\Assessment\UpdateSessionRequest;
use App\Http\Requests\Assessment\ImportSessionRequest;
use App\Http\Traits\ResponseFormatter;
use App\Services\Assessment\SessionService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

/**
 * Assessment Session Controller
 * 
 * Handles HTTP requests for assessment sessions
 */
class SessionController extends Controller
{
    use ResponseFormatter;

    public function __construct(
        protected SessionService $sessionService
    ) {}
    public function index(): View
    {
        $sessions = $this->sessionService->getUserSessions(auth()->id());
        return view('sessions.index', compact('sessions'));
    }

    public function store(CreateSessionRequest $request): RedirectResponse
    {
        try {
            $this->sessionService->createSession([
                'user_id' => auth()->id(),
                'name' => $request->name,
            ]);

            return $this->successRedirect('sessions.index', __('New audit session initialized successfully.'));
        } catch (\Exception $e) {
            return $this->errorRedirect(__('Failed: ') . $e->getMessage());
        }
    }

    public function show(int $id): View
    {
        $session = $this->sessionService->getSession($id, auth()->id());
        $missing = $this->sessionService->getMissingScores($session);

        return view('sessions.show', [
            'session' => $session,
            'missingCodes' => $missing['codes'],
            'missingCount' => $missing['count']
        ]);
    }

    public function update(UpdateSessionRequest $request, int $id): RedirectResponse
    {
        try {
            $this->sessionService->updateSession($id, auth()->id(), [
                'name' => $request->name
            ]);

            return $this->successRedirect('sessions.index', __('Audit session name successfully updated.'));
        } catch (\Exception $e) {
            return $this->errorRedirect(__('Failed to update session: ') . $e->getMessage());
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->sessionService->deleteSession($id, auth()->id());

            return $this->successRedirect('sessions.index', __('Audit session successfully archived.'));
        } catch (\Exception $e) {
            return $this->errorRedirect(__('Failed to delete session: ') . $e->getMessage());
        }
    }

    public function restore(int $id): RedirectResponse
    {
        try {
            $this->sessionService->restoreSession($id, auth()->id());

            return $this->successRedirect('sessions.index', __('Audit session successfully restored.'));
        } catch (\Exception $e) {
            return $this->errorRedirect(__('Failed to restore session: ') . $e->getMessage());
        }
    }

    public function forceDelete(int $id): RedirectResponse
    {
        try {
            $this->sessionService->forceDeleteSession($id, auth()->id());

            return $this->successRedirect('sessions.index', __('Audit session permanently deleted.'));
        } catch (\Exception $e) {
            return $this->errorRedirect(__('Failed to permanently delete session: ') . $e->getMessage());
        }
    }

    public function clone(int $id): RedirectResponse
    {
        try {
            $newSession = $this->sessionService->cloneSession($id, auth()->id());

            return $this->successRedirect('sessions.index', __('Session successfully cloned!'));
        } catch (\Exception $e) {
            return $this->errorRedirect(__('Failed to clone session: ') . $e->getMessage());
        }
    }

    public function finalize(int $id): RedirectResponse
    {
        try {
            $this->sessionService->finalizeSession($id, auth()->id());

            return $this->successRedirect('sessions.show', __('Audit session has been finalized and marked as Completed!'), ['id' => $id]);
        } catch (\Exception $e) {
            return $this->errorRedirect($e->getMessage());
        }
    }

    public function exportJson(int $id): JsonResponse
    {
        try {
            $exportData = $this->sessionService->exportSessionToJson($id);
            
            $fileName = 'AuditGuard_Export_' . str_replace(' ', '_', $exportData['session']['name']) . '_' . date('Ymd_His') . '.json';
            
            return response()->json($exportData, 200, [
                'Content-Disposition' => "attachment; filename=\"{$fileName}\""
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to export session: ') . $e->getMessage()
            ], 500);
        }
    }

    public function importJson(ImportSessionRequest $request): RedirectResponse
    {
        try {
            $data = json_decode(file_get_contents($request->file('json_file')->getRealPath()), true);

            if (!$data) {
                return $this->errorRedirect(__('Invalid JSON file format.'));
            }

            $this->sessionService->importSessionFromJson(
                $data,
                auth()->id(),
                $request->new_name
            );

            return $this->successRedirect('sessions.index', __('Audit session successfully imported from external template.'));
        } catch (\Exception $e) {
            return $this->errorRedirect(__('Failed to import session: ') . $e->getMessage());
        }
    }
}
