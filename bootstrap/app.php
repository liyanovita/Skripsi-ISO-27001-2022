<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        
        $middleware->api(append: [
            \App\Http\Middleware\ApiVersion::class,
            \App\Http\Middleware\LogApiRequests::class,
        ]);
        
        // Mengecualikan route webhook dari verifikasi CSRF (karena diakses oleh n8n eksternal)
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
            'api/*',
        ]);
        
        $middleware->alias([
            'throttle.api' => \App\Http\Middleware\ThrottleRequests::class,
            'throttle.uploads' => \App\Http\Middleware\ThrottleFileUploads::class,
            'brute.force' => \App\Http\Middleware\PreventBruteForce::class,
            'webhook.auth' => \App\Http\Middleware\VerifyWebhookToken::class,
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'redirect.admin' => \App\Http\Middleware\RedirectIfAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ensure ApiException always renders as a structured JSON response,
        // even when thrown outside of a try/catch block.
        $exceptions->render(function (\App\Exceptions\ApiException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->getErrors(),
            ], $e->getStatusCode());
        });
    })->create();