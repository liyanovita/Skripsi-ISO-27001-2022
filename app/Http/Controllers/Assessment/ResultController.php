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
            'session' => $session,
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
                    'id' => $result->id,
                    'maturity_rating' => $result->maturity_rating,
                    'compliance_status' => $result->compliance_status,
                    'risk_level' => $result->risk_level,
                    'status' => $result->status,
                ], 'Assessment for ' . $result->standard->code . ' successfully saved.');
            }

            return redirect()->back()->with([
                'success' => 'Assessment for ' . $result->standard->code . ' successfully saved.',
                'last_updated_id' => $result->id
            ]);
        } catch (\Exception $e) {
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
                'AI insight generation triggered successfully.'
            );
        } catch (\Exception $e) {
            throw ApiException::internalError($e->getMessage());
        }
    }

    public function checkAiStatus(int $id): JsonResponse
    {
        try {
            $result = $this->resultService->getResultById($id);

            return ApiResponse::success([
                'id' => $result->id,
                'has_ai' => !empty($result->ai_recommendation),
                'ai_recommendation' => $result->ai_recommendation,
                'corrective_action_plan' => $result->corrective_action_plan,
                'control_insight' => $result->control_insight,
                'risk_priority' => $result->risk_priority,
                'evidence_validation' => $result->evidence_validation
            ]);
        } catch (\Exception $e) {
            throw ApiException::notFound('Assessment result not found');
        }
    }
}
