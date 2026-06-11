<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\AssessmentSessionApiController;
use App\Http\Controllers\Api\AssessmentResultApiController;
use App\Http\Controllers\Api\CommunityTemplateApiController;
use App\Http\Controllers\Api\IntelligenceApiController;
use App\Http\Controllers\Api\ComplianceApiController;
use App\Http\Controllers\Api\KnowledgeBaseApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\WebhookApiController;
use App\Http\Controllers\Api\HealthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// System health check (public)
Route::get('health', [HealthController::class, 'check']);

// Public data endpoints (no auth required)
Route::get('standards', [\App\Http\Controllers\GuestAssessmentController::class, 'getStandards']);
Route::get('quick-search', [\App\Http\Controllers\Api\QuickSearchController::class, 'search'])->middleware('auth:sanctum');

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthApiController::class, 'login']);
    Route::post('register', [AuthApiController::class, 'register']);
    
    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthApiController::class, 'me']);
        Route::post('logout', [AuthApiController::class, 'logout']);
        Route::post('logout-all', [AuthApiController::class, 'logoutAll']);
    });
});

// Public webhook routes (no authentication required)
Route::prefix('webhook')->group(function () {
    Route::post('n8n/ai-response', [WebhookApiController::class, 'handleN8nResponse']);
    Route::post('n8n/session-summary', [WebhookApiController::class, 'handleSessionSummary']);
    Route::post('n8n/ai-recommendation', [AssessmentResultApiController::class, 'receiveN8nWebhook']);
    Route::post('n8n/ai-summary', [IntelligenceApiController::class, 'receiveAiSummaryWebhook']);
    Route::post('send-notification', [WebhookApiController::class, 'sendNotification']);
    Route::get('reminders', [WebhookApiController::class, 'getReminders']);
});

// Protected API routes (require authentication)
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    
    // User Profile Management
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileApiController::class, 'show']);
        Route::put('/', [ProfileApiController::class, 'update']);
        Route::put('password', [ProfileApiController::class, 'updatePassword']);
    });

    // Assessment Sessions Management
    Route::prefix('sessions')->group(function () {
        Route::get('/', [AssessmentSessionApiController::class, 'index']);
        Route::post('/', [AssessmentSessionApiController::class, 'store']);
        Route::get('{id}', [AssessmentSessionApiController::class, 'show']);
        Route::put('{id}', [AssessmentSessionApiController::class, 'update']);
        Route::delete('{id}', [AssessmentSessionApiController::class, 'destroy']);
        Route::post('{id}/clone', [AssessmentSessionApiController::class, 'clone']);
        Route::post('{id}/finalize', [AssessmentSessionApiController::class, 'finalize']);
        Route::post('{id}/restore', [AssessmentSessionApiController::class, 'restore']);
        Route::delete('{id}/force', [AssessmentSessionApiController::class, 'forceDelete']);
        Route::get('{id}/export', [AssessmentSessionApiController::class, 'exportJson']);
        Route::post('import', [AssessmentSessionApiController::class, 'importJson']);
        
        // Session Results
        Route::get('{sessionId}/results', [AssessmentResultApiController::class, 'index']);
    });

    // Assessment Results Management
    Route::prefix('results')->group(function () {
        Route::put('{id}', [AssessmentResultApiController::class, 'update']);
        Route::post('{id}/ai-insight', [AssessmentResultApiController::class, 'generateAiInsight']);
        Route::get('{id}/ai-status', [AssessmentResultApiController::class, 'checkAiStatus']);
    });

    // Community Templates
    Route::prefix('community/templates')->group(function () {
        Route::get('/', [CommunityTemplateApiController::class, 'index']);
        Route::post('/', [CommunityTemplateApiController::class, 'store']);
        Route::get('{id}', [CommunityTemplateApiController::class, 'show']);
        Route::post('{id}/use', [CommunityTemplateApiController::class, 'useTemplate']);
        Route::post('{id}/clone', [CommunityTemplateApiController::class, 'clone']);
        Route::post('{id}/upvote', [CommunityTemplateApiController::class, 'upvote']);
        Route::post('{id}/rate', [CommunityTemplateApiController::class, 'rate']);
    });

    // Intelligence & Analytics
    Route::prefix('intelligence')->group(function () {
        Route::get('dashboard', [IntelligenceApiController::class, 'dashboard']);
        Route::get('analytics/tactical', [IntelligenceApiController::class, 'tactical']);
        Route::get('analytics/strategic', [IntelligenceApiController::class, 'strategic']);
        Route::post('ai-summary/{sessionId}', [IntelligenceApiController::class, 'generateAiSummary']);
    });

    // Reports Export
    Route::prefix('reports')->group(function () {
        Route::get('{sessionId}/pdf', [IntelligenceApiController::class, 'exportPdf']);
        Route::get('{sessionId}/excel', [IntelligenceApiController::class, 'exportExcel']);
    });

    // Compliance Workspace (Statement of Applicability)
    Route::prefix('compliance')->group(function () {
        Route::get('workspace', [ComplianceApiController::class, 'index']);
        Route::put('workspace/{resultId}', [ComplianceApiController::class, 'updateEntry']);
        
        // SoA Export
        Route::prefix('soa')->group(function () {
            Route::get('{sessionId}/excel', [ComplianceApiController::class, 'exportSoaExcel']);
            Route::get('{sessionId}/pdf', [ComplianceApiController::class, 'exportSoaPdf']);
        });
    });

    // Knowledge Base Management
    Route::prefix('knowledge-base')->group(function () {
        Route::get('/', [KnowledgeBaseApiController::class, 'index']);
        Route::post('/', [KnowledgeBaseApiController::class, 'store']);
        Route::get('{id}', [KnowledgeBaseApiController::class, 'show']);
        Route::put('{id}', [KnowledgeBaseApiController::class, 'update']);
        Route::delete('{id}', [KnowledgeBaseApiController::class, 'destroy']);
        Route::get('{id}/download', [KnowledgeBaseApiController::class, 'download']);
        Route::get('{id}/attachment', [KnowledgeBaseApiController::class, 'downloadAttachment']);
    });
});

// Get authenticated user info
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});