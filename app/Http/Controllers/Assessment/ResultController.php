<?php

namespace App\Http\Controllers\Assessment;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assessment\UpdateResultRequest;
use App\Http\Responses\ApiResponse;
use App\Models\AssessmentResult;
use App\Models\AssessmentSession;
use App\Services\Assessment\ResultService;
use App\Services\Assessment\SessionService;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Assessment Result Controller
 */
class ResultController extends Controller
{
    public function __construct(
        protected ResultService $resultService,
        protected SessionService $sessionService
    ) {}

    public function edit(int $sessionId): View
    {
        $user = auth()->user();
        $session = AssessmentSession::with(['results.standard'])
            ->where(function ($query) use ($user) {
                if ($user && $user->isAdmin()) {
                    return;
                }
                $query->where('user_id', $user->id)
                      ->orWhereHas('invitedUsers', fn($q) => $q->where('user_id', $user->id));
            })
            ->findOrFail($sessionId);

        $missing = $this->sessionService->getMissingScores($session);
        $isLocked = $session->isLockedForUser($user);
        $lockReason = $session->getLockReason($user);

        return view('results.edit', [
            'session'      => $session,
            'missingCodes' => $missing['codes'],
            'missingCount' => $missing['count'],
            'isLocked'     => $isLocked,
            'lockReason'   => $lockReason
        ]);
    }

    public function update(UpdateResultRequest $request, int $id): JsonResponse|RedirectResponse
    {
        try {
            $existing = AssessmentResult::with('session')->findOrFail($id);
            if ($existing->session->status === 'completed' || $existing->session->status === 'closed') {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('This assessment session is completed and read-only. Assessment scores cannot be modified.'),
                    ], 403);
                }
                return redirect()->back()->with('error', __('This assessment session is completed and read-only. Assessment scores cannot be modified.'));
            }

            $result = $this->resultService->updateResult(
                $id,
                $request->all(),
                $request->file('evidence_file')
            );

            if ($request->ajax() || $request->wantsJson()) {
                return ApiResponse::success([
                    'id'                => $result->id,
                    'maturity_rating'   => $result->maturity_rating,
                    'compliance_status' => $result->compliance_status,
                    'risk_level'        => $result->risk_level,
                    'status'            => $result->status,
                    'is_applicable'     => (bool) $result->is_applicable,
                    'soa_justification' => $result->soa_justification,
                    'evidence_file'     => is_array($result->evidence_file) ? $result->evidence_file : (empty($result->evidence_file) ? [] : [$result->evidence_file]),
                ], __('Assessment for :code successfully saved.', ['code' => $result->standard->code]));
            }

            return redirect()->back()->with([
                'success'         => __('Assessment for :code successfully saved.', ['code' => $result->standard->code]),
                'last_updated_id' => $result->id
            ]);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'PROCESSING') {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success'   => false,
                        'is_processing' => true,
                        'message'   => __('AI analysis is currently processing.'),
                    ], 429);
                }
                return redirect()->back()->with('warning', __('AI analysis is currently processing.'));
            }

            if ($e->getMessage() === 'NO_DATA_CHANGE') {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success'   => false,
                        'no_change' => true,
                        'message'   => __('No data has changed'),
                    ], 409);
                }
                return redirect()->back()->with('warning', __('No data has changed'));
            }

            if ($request->ajax() || $request->wantsJson()) {
                throw ApiException::internalError($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function generateAiInsight(int $id): JsonResponse
    {
        try {
            $existing = AssessmentResult::with('session')->findOrFail($id);
            if ($existing->session->status === 'completed' || $existing->session->status === 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => __('This assessment session is completed and read-only.'),
                ], 403);
            }

            $this->resultService->generateAiInsight($id);

            return ApiResponse::success(
                ['ai_recommendation' => null],
                __('AI insight generation triggered successfully.')
            );
        } catch (\Exception $e) {
            if ($e->getMessage() === 'PROCESSING') {
                return response()->json([
                    'success'   => false,
                    'is_processing' => true,
                    'message'   => __('AI analysis is currently processing.'),
                ], 429);
            }
            if ($e->getMessage() === 'NO_DATA_CHANGE') {
                return response()->json([
                    'success'   => false,
                    'no_change' => true,
                    'message'   => __('No data has changed'),
                ], 409);
            }
            throw ApiException::internalError($e->getMessage());
        }
    }

    /**
     * Render the expandable detail block (notes, evidence, AI synthesis) for a single
     * control row on the Assessment Result page. Fetched on demand instead of being
     * rendered inline for every control up front, to keep the initial page payload light.
     */
    public function rowDetail(int $id)
    {
        $result = $this->resultService->getResultById($id);

        return response(
            view('pages.intelligence._result_detail_row', ['result' => $result])->render()
        )->header('Content-Type', 'text/html');
    }

    /**
     * Render the interactive body (rating form, notes, evidence, AI status) for a single
     * control card on the session assessment page. Fetched on demand the first time a
     * card is expanded, instead of being rendered inline for every control up front.
     */
    public function cardBody(int $id)
    {
        $result = $this->resultService->getResultById($id);

        return response(
            view('sessions._result_card_body', ['result' => $result])->render()
        )->header('Content-Type', 'text/html');
    }

    public function checkAiStatus(int $id): JsonResponse
    {
        try {
            $result = $this->resultService->getResultById($id);

            $localized = $result->getAiContentForLocale(app()->getLocale());

            return ApiResponse::success([
                'id'                          => $result->id,
                'has_ai'                      => !empty($result->ai_recommendation),
                'ai_recommendation'           => $localized['ai_recommendation'],
                'corrective_action_plan'      => $localized['corrective_action_plan'],
                'control_insight'             => $localized['control_insight'],
                'risk_priority'               => $result->risk_priority,
                'evidence_validation'         => $result->evidence_validation,
                'impact_interpretation'       => $localized['impact_interpretation'],
                'available_in_current_locale' => $localized['available'],
            ]);
        } catch (\Exception $e) {
            throw ApiException::notFound(__('Assessment result not found'));
        }
    }

    public function extractEvidence(\Illuminate\Http\Request $request, int $id): JsonResponse
    {
        try {
            $existing = AssessmentResult::with('session')->findOrFail($id);
            if ($existing->session->status === 'completed' || $existing->session->status === 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => __('This assessment session is completed and read-only.'),
                ], 403);
            }

            $filePath = $request->input('file_path');
            if (!$filePath) {
                return response()->json([
                    'success' => false,
                    'message' => __('No evidence file specified.'),
                ], 422);
            }

            $this->resultService->triggerEvidenceExtraction($id, $filePath);

            return ApiResponse::success(null, __('Evidence extraction triggered successfully.'));
        } catch (\Exception $e) {
            if ($e->getMessage() === 'PROCESSING') {
                return response()->json([
                    'success'       => false,
                    'is_processing' => true,
                    'message'       => __('Evidence extraction is currently processing.'),
                ], 429);
            }
            throw ApiException::internalError($e->getMessage());
        }
    }

    public function checkExtractionStatus(int $id): JsonResponse
    {
        try {
            $result = $this->resultService->getResultById($id);
            $locale = app()->getLocale();

            $extractions = is_array($result->evidence_extractions) ? $result->evidence_extractions : [];
            $resolved = [];
            foreach (array_keys($extractions) as $filePath) {
                $resolved[$filePath] = $this->resultService->getExtractionForLocale($result, $filePath, $locale);
            }

            return ApiResponse::success([
                'id'                   => $result->id,
                'evidence_extractions' => $resolved,
            ]);
        } catch (\Exception $e) {
            throw ApiException::notFound(__('Assessment result not found'));
        }
    }

    public function viewEvidence(\Illuminate\Http\Request $request, int $id)
    {
        try {
            $result = $this->resultService->getResultById($id);

            $files = is_array($result->evidence_file) ? $result->evidence_file : (empty($result->evidence_file) ? [] : [$result->evidence_file]);

            if (empty($files)) {
                abort(404, __('Evidence file not specified.'));
            }

            $requestedFile = $request->query('file');
            if (empty($requestedFile) || !in_array($requestedFile, $files)) {
                $requestedFile = $files[0];
            }

            $disk = \Illuminate\Support\Facades\Storage::disk('public')->exists($requestedFile) ? 'public' : 'local';

            if (!\Illuminate\Support\Facades\Storage::disk($disk)->exists($requestedFile)) {
                abort(404, __('Evidence file not found on disk.'));
            }

            return \Illuminate\Support\Facades\Storage::disk($disk)->response($requestedFile);
        } catch (\Exception $e) {
            abort(403, __('Unauthorized access to evidence.'));
        }
    }

    public function deleteEvidence(\Illuminate\Http\Request $request, int $id): JsonResponse|RedirectResponse
    {
        try {
            $result = $this->resultService->getResultById($id);
            if ($result->session->status === 'completed' || $result->session->status === 'closed') {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('This assessment session is completed and read-only.'),
                    ], 403);
                }
                return redirect()->back()->with('error', __('This assessment session is completed and read-only.'));
            }

            $filePath = $request->input('file_path');

            $files = is_array($result->evidence_file) ? $result->evidence_file : (empty($result->evidence_file) ? [] : [$result->evidence_file]);

            if (($key = array_search($filePath, $files)) !== false) {
                unset($files[$key]);
                
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($filePath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($filePath);
                }
                if (\Illuminate\Support\Facades\Storage::disk('local')->exists($filePath)) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($filePath);
                }
            }

            $result->update([
                'evidence_file' => array_values($files)
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return ApiResponse::success(['evidence_file' => $result->evidence_file], __('Evidence file deleted successfully.'));
            }

            return redirect()->back()->with('success', __('Evidence file deleted successfully.'));
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                throw ApiException::internalError($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
