<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

// Assessment Domain
use App\Http\Controllers\Assessment\SessionController;
use App\Http\Controllers\Assessment\ResultController;

// Intelligence Domain
use App\Http\Controllers\Intelligence\DashboardController;
use App\Http\Controllers\Intelligence\AnalyticsController;
use App\Http\Controllers\Intelligence\ReportController;
use App\Http\Controllers\Intelligence\AiSummaryController;

// Compliance Domain
use App\Http\Controllers\Compliance\WorkspaceController;

// Community Domain
use App\Http\Controllers\Community\TemplateController as CommunityController;

// Integration Domain
use App\Http\Controllers\Integration\WebhookController;

// Governance Domain
use App\Http\Controllers\Governance\AuditTrailController;
use App\Http\Controllers\Governance\KnowledgeBaseController;
use App\Http\Controllers\Governance\ProfileController;

// Other Controllers
use App\Http\Controllers\GuestAssessmentController;
use App\Http\Controllers\LanguageController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('pages.landing'))->name('landing');
Route::get('/guest-audit', [GuestAssessmentController::class, 'index'])->name('guest.audit');
Route::get('/lang/{lang}', [LanguageController::class, 'switchLang'])->name('lang.switch');

// Authentication
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});

// Password Reset
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.send');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

Route::controller(RegisteredUserController::class)->group(function () {
    Route::get('/register', 'create')->name('register');
    Route::post('/register', 'store');
});

// OAuth Routes
Route::controller(SocialAuthController::class)->prefix('auth')->name('auth.')->group(function () {
    Route::get('/{provider}', 'redirect')->name('redirect');
    Route::get('/{provider}/callback', 'callback')->name('callback');
});

/*
|--------------------------------------------------------------------------
| Webhook Routes (Public, no auth, no csrf)
|--------------------------------------------------------------------------
*/
Route::prefix('webhook')->middleware('webhook.auth')->group(function () {
    Route::post('/n8n', [WebhookController::class, 'handleN8nResponse'])->name('webhook.n8n');
    Route::post('/n8n-summary', [WebhookController::class, 'handleSessionSummary'])->name('webhook.n8n.summary');
    Route::get('/n8n/reminders', [WebhookController::class, 'getReminders'])->name('webhook.n8n.reminders');
    Route::post('/n8n/send-notification', [WebhookController::class, 'sendNotification'])->name('webhook.n8n.send-notification');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::view('/verify-email', 'auth.verify-email')->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('dashboard', ['verified' => 1]);
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::get('/confirm-password', fn() => view('auth.confirm-password'))->name('password.confirm');
    Route::post('/confirm-password', function (Request $request) {
        if (!Hash::check($request->password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => __('The provided password is incorrect.'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended();
    });

    // Dashboard — redirect admin ke admin panel jika mengakses route ini
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('redirect.admin');

    // Assessment Domain - Sessions
    Route::controller(SessionController::class)->prefix('sessions')->name('sessions.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/store', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::get('/{id}', 'show')->name('show');
        Route::post('/{id}/clone', 'clone')->name('clone');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::post('/{id}/restore', 'restore')->name('restore');
        Route::delete('/{id}/force-delete', 'forceDelete')->name('force-delete');
        Route::get('/{id}/export-json', 'exportJson')->name('export-json');
        Route::post('/import-json', 'importJson')->name('import-json');
        Route::post('/{id}/finalize', 'finalize')->name('finalize');
    });

    // Assessment Domain - Results
    Route::controller(ResultController::class)->prefix('results')->name('results.')->group(function () {
        Route::get('/session/{session_id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::post('/{id}/generate-ai', 'generateAiInsight')->name('generate-ai');
        Route::get('/{id}/ai-status', 'checkAiStatus')->name('ai-status');
    });

    // Intelligence Domain - Analytics
    Route::get('/intelligence/strategic', [AnalyticsController::class, 'strategic'])->name('reports.strategic');
    // Tactical report merged into workspace — redirect for backward compatibility
    Route::get('/intelligence/tactical', fn(\Illuminate\Http\Request $r) =>
        redirect()->route('workspace.index', array_filter([
            'session_id' => $r->get('session_id'),
            'tab'        => 'gap-report',
        ]))
    )->name('reports.tactical');
    
    // Intelligence Domain - Reports
    Route::get('/reports/pdf/{session}', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::get('/reports/excel/{session}', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
    
    // Intelligence Domain - AI Summary
    Route::get('/reports/ai-summary/{session}', [AiSummaryController::class, 'generate'])->name('reports.ai-summary');
    Route::get('/reports/ai-summary/{session}/status', [AiSummaryController::class, 'checkStatus'])->name('reports.ai-summary.status');

    // Compliance Domain - Workspace
    Route::get('/workspace', [WorkspaceController::class, 'index'])->name('workspace.index');
    Route::patch('/workspace/entry/{result}', [WorkspaceController::class, 'updateSingle'])->name('workspace.entry.update');
    Route::get('/workspace/{session_id}/export-soa', [WorkspaceController::class, 'exportSoa'])->name('workspace.export-soa');
    Route::get('/workspace/{session_id}/export-soa-pdf', [WorkspaceController::class, 'exportSoaPdf'])->name('workspace.export-soa-pdf');

    // Governance Domain - Audit Trail
    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail.index');
    Route::get('/audit-trail/export', [AuditTrailController::class, 'export'])->name('audit-trail.export');

    // Community Domain
    Route::controller(CommunityController::class)->prefix('community')->name('community.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/store', 'store')->name('store');
        Route::post('/use', 'useTemplate')->name('use');
        Route::get('/{id}/preview', 'show')->name('preview');
        Route::post('/{id}/upvote', 'upvote')->name('upvote');
        Route::post('/{id}/rate', 'rate')->name('rate');
        Route::post('/{id}/clone', 'clone')->name('clone');
    });

    // Governance Domain - Knowledge Base
    Route::controller(KnowledgeBaseController::class)->prefix('knowledge-base')->name('knowledge-base.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/preview', 'preview')->name('preview');
        Route::get('/export-json', 'exportJson')->name('export-json');
        Route::post('/import-json', 'importJson')->name('import-json');
        Route::post('/store', 'store')->name('store');
        Route::get('/attachment/{id}', 'downloadAttachment')->name('attachment');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/download/{id}', 'download')->name('download');
    });

    // Governance Domain - Profile
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::match(['patch', 'put'], '/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
        Route::put('/password', 'updatePassword')->name('password.update');
    });

    // Admin Domain
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        
        // ISO Standards Management
        Route::get('standards-export', [\App\Http\Controllers\Admin\IsoStandardController::class, 'export'])->name('standards.export');
        Route::post('standards-import', [\App\Http\Controllers\Admin\IsoStandardController::class, 'import'])->name('standards.import');
        Route::resource('standards', \App\Http\Controllers\Admin\IsoStandardController::class);

        // Knowledge Base Management
        Route::resource('knowledge', \App\Http\Controllers\Admin\KnowledgeBaseController::class);

        // CAPA Plan Management
        Route::resource('capa', \App\Http\Controllers\Admin\CapaController::class)->only(['index', 'edit', 'update']);

        // System Logs / Audit Trail
        Route::get('logs', [\App\Http\Controllers\Admin\AuditTrailController::class, 'index'])->name('logs.index');

        // Compliance Reports
        Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'exportCsv'])->name('reports.export_csv');

        // User Management
        Route::controller(\App\Http\Controllers\Admin\UserController::class)->prefix('users')->name('users.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{user}', 'show')->name('show');
            Route::get('/{user}/edit', 'edit')->name('edit');
            Route::put('/{user}', 'update')->name('update');
            Route::patch('/{user}/toggle-status', 'toggleStatus')->name('toggle-status');
            Route::post('/{user}/reset-password', 'resetPassword')->name('reset-password');
            Route::delete('/{user}', 'destroy')->name('destroy');
        });

        // Audit Sessions (Cross-User)
        Route::controller(\App\Http\Controllers\Admin\SessionController::class)->prefix('sessions')->name('sessions.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{session}', 'show')->name('show');
            Route::delete('/{session}', 'destroy')->name('destroy');
        });

        // Community Moderation
        Route::controller(\App\Http\Controllers\Admin\CommunityController::class)->prefix('community')->name('community.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::delete('/{template}', 'destroy')->name('destroy');
        });

        // Admin Profile & Settings
        Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password');
    });
});
