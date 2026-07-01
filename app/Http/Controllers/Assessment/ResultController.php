<?php

namespace App\Http\Controllers\Assessment;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assessment\UpdateResultRequest;
use App\Http\Responses\ApiResponse;
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
        $session = AssessmentSession::with(['results.standard'])
            ->where('user_id', auth()->id())
            ->findOrFail($sessionId);
        $missing = $this->sessionService->getMissingScores($session);

        return view('results.edit', [
            'session'      => $session,
            'missingCodes' => $missing['codes'],
            'missingCount' => $missing['count']
        ]);
    }

    public function update(UpdateResultRequest $request, int $id): JsonResponse|RedirectResponse
    {
        try {
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
            $this->resultService->generateAiInsight($id);

            return ApiResponse::success(
                ['ai_recommendation' => null],
                __('AI insight generation triggered successfully.')
            );
        } catch (\Exception $e) {
            if ($e->getMessage() === 'NO_DATA_CHANGE') {
                return response()->json([
                    'success'   => false,
                    'no_change' => true,
                    'message'   => __('No data change detected.'),
                ], 409);
            }
            throw ApiException::internalError($e->getMessage());
        }
    }

    public function checkAiStatus(int $id): JsonResponse
    {
        try {
            $result = $this->resultService->getResultById($id);

            return ApiResponse::success([
                'id'                    => $result->id,
                'has_ai'                => !empty($result->ai_recommendation),
                'ai_recommendation'     => $result->ai_recommendation,
                'corrective_action_plan'=> $result->corrective_action_plan,
                'control_insight'       => $result->control_insight,
                'risk_priority'         => $result->risk_priority,
                'evidence_validation'   => $result->evidence_validation,
                'impact_interpretation' => $result->impact_interpretation
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
