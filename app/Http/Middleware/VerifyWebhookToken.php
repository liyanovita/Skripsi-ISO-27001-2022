<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('services.webhook.secret') ?: config('app.webhook_secret');

        // Allow bypassing if WEBHOOK_SECRET is explicitly empty in local dev (though not recommended)
        if (empty($expectedToken) && app()->isLocal()) {
            return $next($request);
        }

        $providedToken = $request->bearerToken() ?? $request->header('X-Webhook-Secret');

        if (!$providedToken || !hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid webhook token.'
            ], 401);
        }

        return $next($request);
    }
}
